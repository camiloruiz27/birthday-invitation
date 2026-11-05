<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\ReferralConversion;
use App\Modules\Platform\Support\ReferralProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class FreeCaseAcquisitionTest extends TestCase
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

    public function test_a_guest_cannot_claim_a_free_case(): void
    {
        $case = $this->freeCase();

        $this->post(route('cases.acquire', $case->slug))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('entitlements', 0);
    }

    public function test_a_signed_in_user_claims_a_free_case_without_bold_and_receives_its_access_and_credits(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->freeCase();
        Http::fake();

        $this->actingAs($user)
            ->post(route('cases.acquire', $case->slug))
            ->assertRedirect(route('library'));

        Http::assertNothingSent();
        $this->assertTrue($user->fresh()->ownsCase($case));
        $this->assertSame(Entitlement::SOURCE_FREE, Entitlement::sole()->source);

        $order = Order::sole();
        $this->assertSame(0, $order->amount);
        $this->assertSame(0, $order->list_amount);
        $this->assertSame(Order::STATUS_APPROVED, $order->status);
        $this->assertSame('free', $order->provider);
        $this->assertNull($order->promo_code_id);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(1, CreditLedgerEntry::where('user_id', $user->id)
            ->where('reason', CreditLedgerEntry::REASON_GRANT)
            ->count());
    }

    public function test_repeating_a_free_case_claim_does_not_duplicate_the_order_access_or_credits(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->freeCase();

        $this->actingAs($user)->post(route('cases.acquire', $case->slug))->assertRedirect(route('library'));
        $this->actingAs($user)->post(route('cases.acquire', $case->slug))->assertRedirect(route('cases.show', $case->slug));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('entitlements', 1);
        $this->assertSame(1, CreditLedgerEntry::where('user_id', $user->id)
            ->where('reason', CreditLedgerEntry::REASON_GRANT)
            ->count());
    }

    public function test_free_cases_do_not_open_checkout_or_accept_a_discount_code(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->freeCase();

        $this->actingAs($user)
            ->get(route('cases.checkout.review', $case->slug))
            ->assertRedirect(route('cases.show', $case->slug));

        $this->actingAs($user)
            ->post(route('cases.acquire', $case->slug), ['promo_code' => 'NINGUNO'])
            ->assertSessionHasErrors('promo_code');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_a_free_case_does_not_create_a_referral_conversion_or_consume_the_first_paid_purchase_benefit(): void
    {
        $referrer = $this->userWithoutAccess();
        $code = app(ReferralProgram::class)->referralFor($referrer);
        $buyer = $this->userWithoutAccess();
        $freeCase = $this->freeCase();

        $this->actingAs($buyer)
            ->withSession([ReferralProgram::SESSION_KEY => $code->code])
            ->post(route('cases.acquire', $freeCase->slug))
            ->assertRedirect(route('library'));

        $this->assertDatabaseCount('referral_conversions', 0);

        $discount = app(ReferralProgram::class)->preview($buyer->fresh(), $code->code, 10000);
        $this->assertSame(20, $discount->recipientDiscountPercent);
    }

    public function test_free_cases_are_marked_in_catalog_detail_library_and_administration_data(): void
    {
        $case = $this->freeCase();
        $user = $this->userWithoutAccess();
        $admin = $this->userWithoutAccess(['is_admin' => true]);

        $this->get(route('cases.index'))
            ->assertInertia(fn (Assert $page) => $page->where('cases.0.is_free', true));

        $this->get(route('cases.show', $case->slug))
            ->assertInertia(fn (Assert $page) => $page->where('case.is_free', true));

        app(\App\Modules\Platform\Actions\ClaimFreeCase::class)->claim($user, $case);

        $this->actingAs($user)
            ->get(route('library'))
            ->assertInertia(fn (Assert $page) => $page->where('cases.0.is_free', true));

        $this->actingAs($admin)
            ->get(route('admin.cases'))
            ->assertInertia(fn (Assert $page) => $page->where('cases.0.is_free', true));
    }

    public function test_registering_for_a_free_case_returns_to_its_detail_before_it_is_claimed(): void
    {
        $case = $this->freeCase();

        $this->get(route('register', ['case' => $case->slug]))->assertOk();

        $this->assertSame(route('cases.show', $case->slug), session('url.intended'));
    }

    private function freeCase(): MysteryCase
    {
        $case = $this->catalogCase('steve-jacobs');
        $case->update(['price_amount' => 0, 'published_at' => now()]);

        return $case->fresh();
    }
}
