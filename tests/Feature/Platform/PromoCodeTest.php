<?php

namespace Tests\Feature\Platform;

use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Console\Commands\CreatePromoCode;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Models\PromoCodeRedemption;
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
        $this->assertStringContainsString('límite de usos', $caught->getMessage());
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
