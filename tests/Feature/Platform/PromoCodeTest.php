<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Actions\SettleOrder;
use App\Modules\Platform\Console\Commands\CreatePromoCode;
use App\Modules\Platform\Console\Commands\ExpireStaleOrders;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\PromoCodeRedemption;
use App\Modules\Platform\Payments\PaymentWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Gift and discount codes, redeemed through RedeemPromoCode — the single
 * writer for both. Discount tests reuse RealCheckoutTest's Bold-mocking
 * shape (Http::fake against the Payment Link endpoints) since they exercise
 * the exact same StartCheckout path a real purchase does.
 */
class PromoCodeTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'platform.payments.enabled' => true,
            'platform.payments.identity_key' => 'test-identity',
            'platform.payments.secret_key' => 'test-secret',
            'platform.simulated_checkout' => false,
        ]);
    }

    private function fakeBold(): void
    {
        Http::fake([
            '*/online/link/v1' => Http::response([
                'payload' => ['payment_link' => 'LNK_PROMO', 'url' => 'https://checkout.bold.co/LNK_PROMO'],
                'errors' => [],
            ], 200),
        ]);
    }

    public function test_a_case_gift_code_grants_the_case_marked_as_promo(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $promo = PromoCode::create(['code' => 'CASOGRATIS', 'grants_case_slug' => 'steve-jacobs']);

        app(RedeemPromoCode::class)->redeemGift($user, 'casogratis'); // lowercase on purpose

        $this->assertTrue($user->fresh()->ownsCase($case));
        $this->assertSame(
            Entitlement::SOURCE_PROMO,
            Entitlement::where('user_id', $user->id)->where('mystery_case_id', $case->id)->first()->source
        );
        $this->assertSame(1, $promo->fresh()->redemptions_count);
    }

    public function test_a_credits_gift_code_grants_the_right_amount_with_the_promo_reason(): void
    {
        $user = $this->gameMaster();
        PromoCode::create(['code' => 'CREDITOS50', 'grants_credits' => 50]);
        $before = app(AiCredits::class)->walletFor($user)->available();

        app(RedeemPromoCode::class)->redeemGift($user, 'CREDITOS50');

        $this->assertSame($before + 50, app(AiCredits::class)->walletFor($user->fresh())->available());
        $this->assertSame(
            1,
            CreditLedgerEntry::where('user_id', $user->id)->where('reason', CreditLedgerEntry::REASON_PROMO)->count()
        );
    }

    public function test_a_bundle_code_grants_both_the_case_and_the_credits_in_one_go(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'LANZAMIENTO', 'grants_case_slug' => 'steve-jacobs', 'grants_credits' => 85]);

        app(RedeemPromoCode::class)->redeemGift($user, 'LANZAMIENTO');

        $this->assertTrue($user->fresh()->ownsCase($case));

        // GrantCaseAccess itself mints the case's included credits (85 for
        // steve-jacobs) on first grant — the promo's own 85 stacks on top of
        // that, it does not replace it.
        $this->assertSame(170, app(AiCredits::class)->walletFor($user->fresh())->available());
    }

    public function test_a_code_with_its_total_cap_reached_is_rejected_for_a_new_user(): void
    {
        $promo = PromoCode::create(['code' => 'LIMITADO', 'grants_credits' => 10, 'max_redemptions' => 1]);
        $first = $this->gameMaster();
        $second = $this->userWithoutAccess();

        app(RedeemPromoCode::class)->redeemGift($first, 'LIMITADO');

        $caught = null;

        try {
            app(RedeemPromoCode::class)->redeemGift($second, 'LIMITADO');
        } catch (PromoCodeException $exception) {
            $caught = $exception;
        }

        $this->assertNotNull($caught);
        // The same words as "does not exist": telling an exhausted code apart
        // from an unknown one confirms to a guesser that they hit a real code.
        $this->assertSame(RedeemPromoCode::UNUSABLE, $caught->getMessage());
        $this->assertSame(1, $promo->fresh()->redemptions_count);
        $this->assertSame(0, app(AiCredits::class)->walletFor($second)->available());
    }

    public function test_a_code_limited_to_one_use_per_person_rejects_a_repeat_but_allows_someone_else(): void
    {
        PromoCode::create(['code' => 'UNAVEZ', 'grants_credits' => 10, 'max_redemptions_per_user' => 1]);
        $user = $this->gameMaster();
        $other = $this->userWithoutAccess();

        app(RedeemPromoCode::class)->redeemGift($user, 'UNAVEZ');

        $repeated = null;

        try {
            app(RedeemPromoCode::class)->redeemGift($user, 'UNAVEZ');
        } catch (PromoCodeException $exception) {
            $repeated = $exception;
        }

        $this->assertNotNull($repeated);

        // The second user is untouched by the first's exhausted per-user cap.
        app(RedeemPromoCode::class)->redeemGift($other, 'UNAVEZ');
        $this->assertSame(10, app(AiCredits::class)->walletFor($other->fresh())->available());
    }

    public function test_a_percent_discount_reduces_the_amount_bold_is_asked_to_charge(): void
    {
        $this->fakeBold();
        PromoCode::create(['code' => 'DESCUENTO20', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 20]);
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs'); // price_amount 89000 per the manifest

        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'DESCUENTO20'])
            ->assertRedirect('https://checkout.bold.co/LNK_PROMO');

        $order = Order::sole();
        $this->assertSame((int) floor($case->price_amount * 0.8), $order->amount);

        Http::assertSent(function ($request) use ($order) {
            return str_ends_with($request->url(), '/online/link/v1')
                && $request['amount']['total_amount'] === $order->amount;
        });

        $this->assertSame(1, PromoCodeRedemption::where('order_id', $order->id)->count());
    }

    public function test_a_full_discount_skips_bold_entirely_and_delivers_the_case_directly(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'GRATIS100', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 100]);

        Http::fake(); // nothing should be called at all

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'GRATIS100']);

        Http::assertNothingSent();
        $this->assertTrue($user->fresh()->ownsCase($case));

        $order = Order::sole();
        $this->assertSame(0, $order->amount);
        $this->assertSame(Order::STATUS_APPROVED, $order->status);
    }

    public function test_an_any_case_gift_typed_at_checkout_delivers_the_case_without_bold(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $promo = PromoCode::create(['code' => 'REGALOLIBRE', 'grants_any_case' => true, 'max_redemptions' => 1]);

        Http::fake(); // nothing should be called at all

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'regalolibre']);

        Http::assertNothingSent();
        $this->assertTrue($user->fresh()->ownsCase($case));
        $this->assertSame(
            Entitlement::SOURCE_PROMO,
            Entitlement::where('user_id', $user->id)->where('mystery_case_id', $case->id)->first()->source
        );

        $order = Order::sole();
        $this->assertSame(0, $order->amount);
        $this->assertSame(Order::STATUS_APPROVED, $order->status);
        $this->assertSame('promo', $order->provider);

        $this->assertSame(1, $promo->fresh()->redemptions_count);
        $this->assertSame(1, PromoCodeRedemption::where('order_id', $order->id)->count());
    }

    public function test_a_gift_fixed_to_this_case_delivers_it_at_checkout(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'FIJOAQUI', 'grants_case_slug' => 'steve-jacobs']);

        Http::fake();

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'FIJOAQUI']);

        Http::assertNothingSent();
        $this->assertTrue($user->fresh()->ownsCase($case));
    }

    public function test_a_gift_bundle_typed_at_checkout_also_delivers_its_credits(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'COMBOCOMPRA', 'grants_any_case' => true, 'grants_credits' => 40]);

        Http::fake();

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'COMBOCOMPRA']);

        $this->assertTrue($user->fresh()->ownsCase($case));

        // 85 minted with steve-jacobs itself, plus the bundle's own 40.
        $this->assertSame(125, app(AiCredits::class)->walletFor($user->fresh())->available());
        $this->assertSame(
            1,
            CreditLedgerEntry::where('user_id', $user->id)
                ->where('reason', CreditLedgerEntry::REASON_PROMO)
                ->where('delta', 40)
                ->count()
        );
    }

    public function test_a_code_created_with_max_per_user_zero_can_be_used_repeatedly_by_the_same_person(): void
    {
        $this->artisan('platform:create-promo-code', [
            'code' => 'SINTOPE',
            '--discount-percent' => 25,
            '--max-per-user' => 0,
        ])->assertSuccessful();

        $promo = PromoCode::where('code', 'SINTOPE')->sole();
        $this->assertNull($promo->max_redemptions_per_user);

        $user = $this->userWithoutAccess();
        $redeem = app(RedeemPromoCode::class);

        $redeem->applyDiscount($user, 'SINTOPE', 89000);
        $redeem->applyDiscount($user, 'SINTOPE', 89000);
        $redeem->applyDiscount($user, 'SINTOPE', 89000);

        $this->assertSame(3, $promo->fresh()->redemptions_count);
    }

    public function test_a_gift_for_another_case_is_turned_away_at_checkout_without_spending_a_use(): void
    {
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');
        $this->catalogCase('el-brindis-22-14');
        $promo = PromoCode::create(['code' => 'OTROCASO2', 'grants_case_slug' => 'el-brindis-22-14', 'max_redemptions' => 1]);

        Http::fake();

        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'OTROCASO2'])
            ->assertSessionHasErrors('promo_code');

        Http::assertNothingSent();
        $this->assertSame(0, Order::count());
        $this->assertSame(0, $promo->fresh()->redemptions_count);
        $this->assertSame(0, Entitlement::where('user_id', $user->id)->count());
    }

    public function test_a_case_gift_is_still_turned_away_when_buying_a_credit_package(): void
    {
        $user = $this->gameMaster();
        $package = collect((array) config('platform.credit_packages'))->first();
        $promo = PromoCode::create(['code' => 'CASOPARACREDITOS', 'grants_any_case' => true, 'max_redemptions' => 1]);

        Http::fake();

        $this->actingAs($user)
            ->post(route('credits.purchase'), ['package' => $package['id'], 'promo_code' => 'CASOPARACREDITOS'])
            ->assertSessionHasErrors('promo_code');

        $this->assertSame(0, $promo->fresh()->redemptions_count);
        $this->assertSame(0, Order::count());
    }

    public function test_a_failure_while_delivering_a_free_order_leaves_it_pending_and_unapproved(): void
    {
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'FALLAENTREGA', 'grants_any_case' => true, 'grants_credits' => 40]);

        // The case is granted, then the credits step blows up: the whole
        // delivery must roll back rather than leave an approved order with
        // half of what it promised.
        $this->mock(AiCredits::class, function ($mock) {
            $mock->shouldReceive('grant')->andThrow(new \RuntimeException('boom'));
        });

        $this->withoutExceptionHandling();

        try {
            $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'FALLAENTREGA']);
            $this->fail('The delivery failure should have propagated.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('boom', $exception->getMessage());
        }

        $this->assertSame(Order::STATUS_PENDING, Order::sole()->status);
        $this->assertSame(0, Entitlement::where('user_id', $user->id)->count());
    }

    public function test_releasing_a_discount_after_bold_fails_gives_the_use_back(): void
    {
        Http::fake(['*/online/link/v1' => Http::response(['errors' => ['boom']], 500)]);
        PromoCode::create(['code' => 'FALLA', 'discount_type' => PromoCode::DISCOUNT_FIXED, 'discount_value' => 1000, 'max_redemptions' => 5]);
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        // The checkout link creation throws (Bold returned 500) and the
        // controller has no reason to catch that specific failure, so
        // Laravel's normal handler turns it into a 500 response. What
        // matters here is only what happened to the code's claim before
        // that exception propagated.
        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'FALLA'])
            ->assertServerError();

        $promo = PromoCode::where('code', 'FALLA')->sole();
        $this->assertSame(0, $promo->redemptions_count);
        $this->assertSame(0, PromoCodeRedemption::count());

        // The Order row itself is left behind pending (StartCheckout creates
        // it before ever asking Bold for a link) — platform:expire-stale-orders
        // is what eventually cleans that up, not this release path.
        $order = Order::sole();
        $this->assertSame(Order::STATUS_PENDING, $order->status);
    }

    /**
     * A discount claimed through a real checkout, left pending the way it is
     * once Bold has produced a link. Returns the order the redemption is
     * attached to.
     */
    private function pendingDiscountedOrder(PromoCode $promo, $user): Order
    {
        $this->fakeBold();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => $promo->code])
            ->assertRedirect('https://checkout.bold.co/LNK_PROMO');

        return Order::sole();
    }

    private function settle(Order $order, string $status): void
    {
        app(SettleOrder::class)->apply($order, new PaymentWebhookEvent($order->reference, $status, 'BOLD-PAY-1', []));
    }

    public function test_a_rejected_order_gives_the_discount_use_back(): void
    {
        $promo = PromoCode::create([
            'code' => 'RECHAZO',
            'discount_type' => PromoCode::DISCOUNT_PERCENT,
            'discount_value' => 20,
            'max_redemptions' => 1,
        ]);
        $user = $this->userWithoutAccess();
        $order = $this->pendingDiscountedOrder($promo, $user);

        $this->assertSame(1, $promo->fresh()->redemptions_count);

        $this->settle($order, Order::STATUS_REJECTED);

        $this->assertSame(Order::STATUS_REJECTED, $order->fresh()->status);
        $this->assertSame(0, $promo->fresh()->redemptions_count);
        $this->assertSame(0, PromoCodeRedemption::count());

        // The buyer's per-user cap no longer counts the failed attempt, so
        // the same code works again on a retry.
        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'RECHAZO'])
            ->assertRedirect('https://checkout.bold.co/LNK_PROMO');
    }

    public function test_settling_the_same_rejection_twice_releases_the_use_only_once(): void
    {
        $promo = PromoCode::create([
            'code' => 'DOBLE',
            'discount_type' => PromoCode::DISCOUNT_FIXED,
            'discount_value' => 1000,
            'max_redemptions' => 5,
        ]);
        $first = $this->userWithoutAccess();
        $order = $this->pendingDiscountedOrder($promo, $first);

        // A second, unrelated buyer holds a use of the same code.
        app(RedeemPromoCode::class)->applyDiscount($this->gameMaster(), 'DOBLE', 1000);
        $this->assertSame(2, $promo->fresh()->redemptions_count);

        $this->settle($order, Order::STATUS_REJECTED);
        $this->settle($order, Order::STATUS_REJECTED); // webhook + reconcile racing

        $this->assertSame(1, $promo->fresh()->redemptions_count);
    }

    public function test_an_approved_order_keeps_its_discount_use(): void
    {
        $promo = PromoCode::create([
            'code' => 'PAGADO',
            'discount_type' => PromoCode::DISCOUNT_PERCENT,
            'discount_value' => 20,
            'max_redemptions' => 3,
        ]);
        $user = $this->userWithoutAccess();
        $order = $this->pendingDiscountedOrder($promo, $user);

        $this->settle($order, Order::STATUS_APPROVED);

        $this->assertSame(1, $promo->fresh()->redemptions_count);
        $this->assertSame(1, PromoCodeRedemption::where('order_id', $order->id)->count());

        // A refund afterwards is for a person to review: the code WAS used.
        $this->settle($order, Order::STATUS_VOIDED);

        $this->assertSame(1, $promo->fresh()->redemptions_count);
    }

    public function test_expiring_a_stale_order_gives_the_discount_use_back(): void
    {
        $promo = PromoCode::create([
            'code' => 'ABANDONO',
            'discount_type' => PromoCode::DISCOUNT_PERCENT,
            'discount_value' => 20,
            'max_redemptions' => 1,
        ]);
        $order = $this->pendingDiscountedOrder($promo, $this->userWithoutAccess());
        $order->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->artisan(ExpireStaleOrders::class)->assertSuccessful();

        $this->assertSame(Order::STATUS_EXPIRED, $order->fresh()->status);
        $this->assertSame(0, $promo->fresh()->redemptions_count);
        $this->assertSame(0, PromoCodeRedemption::count());
    }

    public function test_expiring_an_order_whose_use_was_already_released_does_not_release_twice(): void
    {
        // The Bold-failed path: StartCheckout releases, the order stays
        // pending, and the expiry job later closes it.
        Http::fake(['*/online/link/v1' => Http::response(['errors' => ['boom']], 500)]);
        $promo = PromoCode::create([
            'code' => 'YALIBERADO',
            'discount_type' => PromoCode::DISCOUNT_FIXED,
            'discount_value' => 1000,
            'max_redemptions' => 5,
        ]);
        $this->catalogCase('steve-jacobs');
        $this->actingAs($this->userWithoutAccess())
            ->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'YALIBERADO'])
            ->assertServerError();

        // Someone else's genuine use must not be eaten by the expiry.
        app(RedeemPromoCode::class)->applyDiscount($this->gameMaster(), 'YALIBERADO', 1000);

        Order::sole()->forceFill(['created_at' => now()->subDays(2)])->save();
        $this->artisan(ExpireStaleOrders::class)->assertSuccessful();

        $this->assertSame(1, $promo->fresh()->redemptions_count);
    }

    public function test_an_inactive_or_unknown_code_is_rejected_without_creating_anything(): void
    {
        PromoCode::create(['code' => 'APAGADO', 'grants_credits' => 10, 'active' => false]);
        $user = $this->gameMaster();

        $this->expectException(PromoCodeException::class);
        app(RedeemPromoCode::class)->redeemGift($user, 'APAGADO');
    }

    public function test_the_redeem_screen_rejects_a_discount_code_with_a_clear_message(): void
    {
        PromoCode::create(['code' => 'SOLODESC', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 10]);
        $user = $this->gameMaster();

        $this->actingAs($user)
            ->post(route('promo.redeem.store'), ['code' => 'SOLODESC'])
            ->assertSessionHasErrors('code');

        $this->assertSame(0, PromoCodeRedemption::count());
    }

    public function test_create_promo_code_command_rejects_mixing_gift_and_discount(): void
    {
        $this->artisan(CreatePromoCode::class, [
            'code' => 'MEZCLADO',
            '--case' => 'steve-jacobs',
            '--discount-percent' => '10',
        ])->assertFailed();

        $this->assertSame(0, PromoCode::count());
    }

    public function test_create_promo_code_command_creates_a_gift_bundle(): void
    {
        $this->catalogCase('steve-jacobs');

        $this->artisan(CreatePromoCode::class, [
            'code' => 'bundle2026',
            '--case' => 'steve-jacobs',
            '--credits' => '85',
            '--max-redemptions' => '50',
        ])->assertSuccessful();

        $promo = PromoCode::where('code', 'BUNDLE2026')->sole();
        $this->assertSame('steve-jacobs', $promo->grants_case_slug);
        $this->assertSame(85, $promo->grants_credits);
        $this->assertSame(50, $promo->max_redemptions);
    }
}
