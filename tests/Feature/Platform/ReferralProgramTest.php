<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Actions\SettleOrder;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\ReferralCode;
use App\Modules\Platform\Models\ReferralConversion;
use App\Modules\Platform\Payments\PaymentWebhookEvent;
use App\Modules\Platform\Support\ReferralProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class ReferralProgramTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'platform.payments.enabled' => true,
            'platform.payments.identity_key' => 'test-identity',
            'platform.payments.secret_key' => 'test-secret',
        ]);
    }

    public function test_a_new_user_gets_an_active_referral_code_without_a_paid_case(): void
    {
        $user = $this->userWithoutAccess();
        $program = app(ReferralProgram::class);

        $code = $program->referralFor($user);

        $this->assertNotNull($code);
        $this->assertTrue($code->isUsable());
        $this->assertSame($user->id, $code->user_id);
    }

    public function test_first_referred_case_purchase_gets_twenty_percent_and_creates_thirty_percent_private_reward(): void
    {
        [$referrer, $code] = $this->referrerWithCode();
        $buyer = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');

        $order = $this->startReferralPurchase($buyer, $case, $code->code);

        $this->assertSame((int) floor($case->price_amount * .8), $order->amount);
        $conversion = ReferralConversion::sole();
        $this->assertSame(20, $conversion->recipient_discount_percent);
        $this->assertSame(30, $conversion->referrer_reward_percent);
        $this->assertSame(ReferralConversion::STATUS_PENDING, $conversion->status);

        $this->approve($order);

        $conversion->refresh();
        $reward = $conversion->rewardPromoCode;
        $this->assertSame(ReferralConversion::STATUS_APPROVED, $conversion->status);
        $this->assertNotNull($reward);
        $this->assertSame(30, $reward->discount_value);
        $this->assertSame($referrer->id, $reward->assigned_user_id);
        $this->assertTrue($reward->expires_at->isSameDay(now()->addDays(90)));
    }

    public function test_later_case_purchase_can_use_a_different_referral_code_at_fifteen_and_twenty_five_percent(): void
    {
        [, $firstCode] = $this->referrerWithCode();
        [$secondReferrer, $secondCode] = $this->referrerWithCode('el-brindis-22-14');
        $buyer = $this->userWithoutAccess();

        $first = $this->startReferralPurchase($buyer, $this->catalogCase('steve-jacobs'), $firstCode->code);
        $this->approve($first);

        $secondCase = $this->catalogCase('el-brindis-22-14');
        $second = $this->startReferralPurchase($buyer, $secondCase, $secondCode->code);
        $conversion = ReferralConversion::where('order_id', $second->id)->sole();

        $this->assertSame((int) floor($secondCase->price_amount * .85), $second->amount);
        $this->assertSame(15, $conversion->recipient_discount_percent);
        $this->assertSame(25, $conversion->referrer_reward_percent);

        $this->approve($second);
        $this->assertSame($secondReferrer->id, $conversion->fresh()->referrer_user_id);
        $this->assertSame(25, $conversion->fresh()->rewardPromoCode->discount_value);
    }

    public function test_owner_cannot_apply_their_own_referral_code(): void
    {
        [$owner, $code] = $this->referrerWithCode();
        $this->catalogCase('el-brindis-22-14');

        $this->actingAs($owner)
            ->post(route('cases.acquire', 'el-brindis-22-14'), ['promo_code' => $code->code])
            ->assertSessionHasErrors('promo_code');

        $this->assertSame(0, Order::count());
        $this->assertSame(0, ReferralConversion::count());
    }

    public function test_rejected_referral_purchase_does_not_create_a_reward(): void
    {
        [, $code] = $this->referrerWithCode();
        $order = $this->startReferralPurchase($this->userWithoutAccess(), $this->catalogCase('steve-jacobs'), $code->code);

        app(SettleOrder::class)->apply($order, new PaymentWebhookEvent($order->reference, Order::STATUS_REJECTED, 'REJECTED-1', []));

        $conversion = ReferralConversion::sole();
        $this->assertSame(ReferralConversion::STATUS_CANCELLED, $conversion->status);
        $this->assertNull($conversion->reward_promo_code_id);
    }

    public function test_repeated_approved_webhooks_create_only_one_reward(): void
    {
        [, $code] = $this->referrerWithCode();
        $order = $this->startReferralPurchase($this->userWithoutAccess(), $this->catalogCase('steve-jacobs'), $code->code);

        $this->approve($order);
        $this->approve($order);

        $conversion = ReferralConversion::sole();
        $this->assertSame(1, PromoCode::where('assigned_user_id', $conversion->referrer_user_id)->count());
    }

    public function test_referral_reward_coupon_is_private_and_single_use(): void
    {
        [$referrer, $code] = $this->referrerWithCode();
        $order = $this->startReferralPurchase($this->userWithoutAccess(), $this->catalogCase('steve-jacobs'), $code->code);
        $this->approve($order);
        $reward = ReferralConversion::sole()->rewardPromoCode;
        $case = $this->catalogCase('el-brindis-22-14');

        try {
            app(RedeemPromoCode::class)->preview(User::factory()->create(), $reward->code, $case->price_amount, $case);
            $this->fail('A private referral reward must not work for another user.');
        } catch (\App\Modules\Platform\Exceptions\PromoCodeException) {
            $this->addToAssertionCount(1);
        }

        // The owning account can still quote and later redeem the coupon.
        $preview = app(RedeemPromoCode::class)->preview($referrer, $reward->code, $case->price_amount, $case);
        $this->assertSame((int) floor($case->price_amount * .7), $preview->discountedAmount);
    }

    public function test_referral_link_remembers_a_valid_code_without_disclosing_invalid_ones(): void
    {
        [, $code] = $this->referrerWithCode();

        $this->get(route('referrals.visit', $code->code))
            ->assertRedirect(route('cases.index'))
            ->assertSessionHas(ReferralProgram::SESSION_KEY, $code->code);

        $this->get(route('referrals.visit', 'REF-NOEXISTE'))
            ->assertRedirect(route('cases.index'));
    }

    public function test_shared_ip_is_an_admin_alert_but_does_not_block_a_purchase(): void
    {
        $referrer = $this->userWithoutAccess();
        $earnedOrder = $this->approvedPaidOrder($referrer, $this->catalogCase('steve-jacobs'));
        $earnedOrder->update(['client_ip' => '203.0.113.42']);
        $code = app(ReferralProgram::class)->activateFromOrder($earnedOrder);

        $order = $this->startReferralPurchase(
            $this->userWithoutAccess(),
            $this->catalogCase('el-brindis-22-14'),
            $code->code,
            '203.0.113.42'
        );

        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertContains('shared_ip', ReferralConversion::sole()->risk_flags);
    }

    public function test_admin_can_review_metrics_and_disable_a_referral_code(): void
    {
        [, $code] = $this->referrerWithCode();
        $order = $this->startReferralPurchase($this->userWithoutAccess(), $this->catalogCase('steve-jacobs'), $code->code);
        $this->approve($order);
        $admin = $this->userWithoutAccess();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->get(route('admin.referrals'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Referrals')
                ->where('summary.approved', 1)
                ->where('summary.rewards_issued', 1)
                ->has('codes', 2)
                ->has('conversions', 1)
            );

        $this->actingAs($admin)
            ->patch(route('admin.referrals.toggle', $code))
            ->assertRedirect();

        $this->assertFalse($code->fresh()->active);
        $this->actingAs($this->userWithoutAccess())->get(route('admin.referrals'))->assertNotFound();
    }

    /** @return array{0: User, 1: ReferralCode} */
    private function referrerWithCode(string $slug = 'steve-jacobs'): array
    {
        $user = $this->userWithoutAccess();

        return [$user, app(ReferralProgram::class)->referralFor($user)];
    }

    private function approvedPaidOrder(User $user, MysteryCase $case): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'type' => Order::TYPE_CASE,
            'mystery_case_id' => $case->id,
            'amount' => $case->price_amount,
            'list_amount' => $case->price_amount,
            'currency' => $case->currency,
            'status' => Order::STATUS_APPROVED,
            'reference' => strtoupper(bin2hex(random_bytes(12))),
            'paid_at' => now(),
        ]);
    }

    private function startReferralPurchase(User $buyer, MysteryCase $case, string $code, ?string $ip = null): Order
    {
        Http::fake([
            '*/online/link/v1' => Http::response([
                'payload' => ['payment_link' => 'LNK_REF', 'url' => 'https://checkout.bold.co/LNK_REF'],
                'errors' => [],
            ], 200),
        ]);

        $request = $this->actingAs($buyer);

        if ($ip) {
            $request->withServerVariables(['REMOTE_ADDR' => $ip]);
        }

        $request
            ->post(route('cases.acquire', $case->slug), ['promo_code' => $code])
            ->assertRedirect('https://checkout.bold.co/LNK_REF');

        return Order::query()->latest('id')->firstOrFail();
    }

    private function approve(Order $order): void
    {
        app(SettleOrder::class)->apply($order, new PaymentWebhookEvent($order->reference, Order::STATUS_APPROVED, 'PAID-'.$order->id, []));
    }
}
