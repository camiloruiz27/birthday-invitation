<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Console\Commands\CreatePromoCode;
use App\Modules\Platform\Console\Commands\CreatePromoCodeBatch;
use App\Modules\Platform\Exceptions\PromoCodeException;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Campaign codes: they lapse on their own, they can be limited to buying a
 * case or to topping up AI credits, and every order keeps what its code took
 * off so a promotion's real cost can be measured.
 */
class PromoCodeExpiryAndScopeTest extends TestCase
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
        Http::fake(['*/online/link/v1' => Http::response([
            'payload' => ['payment_link' => 'LNK_1', 'url' => 'https://checkout.bold.co/LNK_1'],
            'errors' => [],
        ], 200)]);
    }

    private function discount(string $code, array $extra = []): PromoCode
    {
        return PromoCode::create([
            'code' => $code,
            'discount_type' => PromoCode::DISCOUNT_PERCENT,
            'discount_value' => 20,
        ] + $extra);
    }

    // --- Expiry ------------------------------------------------------------

    public function test_an_expired_code_is_turned_away_with_the_same_words_as_an_unknown_one(): void
    {
        $this->discount('VENCIDO', ['expires_at' => now()->subMinute()]);

        try {
            app(RedeemPromoCode::class)->preview($this->userWithoutAccess(), 'VENCIDO', 50000);
            $this->fail('An expired code was accepted.');
        } catch (PromoCodeException $exception) {
            $this->assertSame(RedeemPromoCode::UNUSABLE, $exception->getMessage());
        }
    }

    public function test_a_code_that_has_not_expired_yet_works(): void
    {
        $this->discount('VIGENTE', ['expires_at' => now()->addDay()]);

        $preview = app(RedeemPromoCode::class)->preview($this->userWithoutAccess(), 'VIGENTE', 50000);

        $this->assertSame(40000, $preview->discountedAmount);
    }

    public function test_a_code_with_no_expiry_never_lapses(): void
    {
        $this->discount('SIEMPRE');

        $this->assertFalse(PromoCode::firstWhere('code', 'SIEMPRE')->isExpired());
    }

    public function test_an_expired_code_cannot_start_a_checkout_and_is_not_consumed(): void
    {
        $this->fakeBold();
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $promo = $this->discount('VENCIDO', ['expires_at' => now()->subMinute()]);

        $this->actingAs($user)
            ->post(route('cases.acquire', $case->slug), ['promo_code' => 'VENCIDO'])
            ->assertSessionHasErrors('promo_code');

        $this->assertSame(0, $promo->fresh()->redemptions_count);
        $this->assertSame(0, Order::count());
        Http::assertNothingSent();
    }

    // --- Scope -------------------------------------------------------------

    public function test_a_case_only_code_applies_to_a_case_but_not_to_credits(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $this->discount('SOLOCASOS', ['applies_to' => PromoCode::APPLIES_CASE]);

        $this->assertSame(
            (int) floor($case->price_amount * 0.8),
            app(RedeemPromoCode::class)->preview($user, 'SOLOCASOS', $case->price_amount, $case)->discountedAmount
        );

        $this->expectException(PromoCodeException::class);
        $this->expectExceptionMessage(RedeemPromoCode::UNUSABLE);

        app(RedeemPromoCode::class)->preview($user, 'SOLOCASOS', 9900);
    }

    public function test_a_credits_only_code_applies_to_credits_but_not_to_a_case(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $this->discount('SOLOTOKENS', ['applies_to' => PromoCode::APPLIES_CREDITS]);

        $this->assertSame(
            7920,
            app(RedeemPromoCode::class)->preview($user, 'SOLOTOKENS', 9900)->discountedAmount
        );

        $this->expectException(PromoCodeException::class);
        $this->expectExceptionMessage(RedeemPromoCode::UNUSABLE);

        app(RedeemPromoCode::class)->preview($user, 'SOLOTOKENS', $case->price_amount, $case);
    }

    public function test_a_code_with_no_scope_still_works_on_both(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $this->discount('PARATODO');

        $promos = app(RedeemPromoCode::class);

        $this->assertSame(
            (int) floor($case->price_amount * 0.8),
            $promos->preview($user, 'PARATODO', $case->price_amount, $case)->discountedAmount
        );
        $this->assertSame(7920, $promos->preview($user, 'PARATODO', 9900)->discountedAmount);
    }

    public function test_a_wrong_scope_checkout_is_refused_and_does_not_spend_a_use(): void
    {
        $this->fakeBold();
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $promo = $this->discount('SOLOTOKENS', ['applies_to' => PromoCode::APPLIES_CREDITS]);

        $this->actingAs($user)
            ->post(route('cases.acquire', $case->slug), ['promo_code' => 'SOLOTOKENS'])
            ->assertSessionHasErrors('promo_code');

        $this->assertSame(0, $promo->fresh()->redemptions_count);
        $this->assertSame(0, Order::count());
    }

    public function test_the_credits_review_rejects_a_case_only_code(): void
    {
        $user = $this->userWithoutAccess();
        $package = collect((array) config('platform.credit_packages'))->first();
        $this->discount('SOLOCASOS', ['applies_to' => PromoCode::APPLIES_CASE]);

        $this->actingAs($user)
            ->get(route('credits.checkout.review', ['package' => $package['id'], 'promo_code' => 'SOLOCASOS']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('promo_code', null)
                ->where('final_amount', (int) $package['price_amount'])
                ->whereNot('promo_error', null)
            );
    }

    public function test_scope_only_limits_discounts_never_gifts(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'REGALO', 'grants_case_slug' => 'steve-jacobs', 'applies_to' => PromoCode::APPLIES_CREDITS]);

        app(RedeemPromoCode::class)->redeemGift($user, 'REGALO');

        $this->assertTrue($user->fresh()->ownsCase($case));
    }

    // --- What the order keeps ---------------------------------------------

    public function test_an_order_keeps_the_list_price_the_discount_and_the_code(): void
    {
        $this->fakeBold();
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $promo = $this->discount('DEL20');

        $this->actingAs($user)->post(route('cases.acquire', $case->slug), ['promo_code' => 'DEL20']);

        $order = Order::sole();
        $this->assertSame($case->price_amount, $order->list_amount);
        $this->assertSame((int) floor($case->price_amount * 0.8), $order->amount);
        $this->assertSame($case->price_amount - $order->amount, $order->discount_amount);
        $this->assertSame($promo->id, $order->promo_code_id);
    }

    public function test_a_credit_package_order_keeps_them_too(): void
    {
        $this->fakeBold();
        $user = $this->userWithoutAccess();
        $package = collect((array) config('platform.credit_packages'))->first();
        $this->discount('TOKENS20', ['applies_to' => PromoCode::APPLIES_CREDITS]);

        $this->actingAs($user)->post(route('credits.purchase'), [
            'package' => $package['id'],
            'promo_code' => 'TOKENS20',
        ]);

        $order = Order::sole();
        $this->assertSame((int) $package['price_amount'], $order->list_amount);
        $this->assertSame(
            (int) $package['price_amount'] - $order->amount,
            $order->discount_amount
        );
        $this->assertNotNull($order->promo_code_id);
    }

    public function test_an_order_without_a_code_has_a_list_price_and_no_discount(): void
    {
        $this->fakeBold();
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');

        $this->actingAs($user)->post(route('cases.acquire', $case->slug));

        $order = Order::sole();
        $this->assertSame($order->amount, $order->list_amount);
        $this->assertNull($order->discount_amount);
        $this->assertNull($order->promo_code_id);
    }

    // --- Console ------------------------------------------------------------

    public function test_the_command_sets_an_expiry_through_the_end_of_that_day_and_a_scope(): void
    {
        $date = now()->addDays(10)->format('Y-m-d');

        $this->artisan(CreatePromoCode::class, [
            'code' => 'camp10',
            '--discount-percent' => '10',
            '--expires' => $date,
            '--applies-to' => 'credits',
        ])->assertSuccessful();

        $promo = PromoCode::where('code', 'CAMP10')->sole();
        $this->assertSame('credits', $promo->applies_to);
        $this->assertSame("{$date} 23:59:59", $promo->expires_at->format('Y-m-d H:i:s'));
    }

    public function test_the_command_refuses_an_expiry_in_the_past_or_that_is_not_a_date(): void
    {
        $this->artisan(CreatePromoCode::class, [
            'code' => 'PASADO',
            '--discount-percent' => '10',
            '--expires' => now()->subDay()->format('Y-m-d'),
        ])->assertFailed();

        $this->artisan(CreatePromoCode::class, [
            'code' => 'RARO',
            '--discount-percent' => '10',
            '--expires' => 'cuando-se-pueda',
        ])->assertFailed();

        $this->assertSame(0, PromoCode::count());
    }

    public function test_the_command_refuses_a_scope_it_does_not_know_or_on_a_gift(): void
    {
        $this->artisan(CreatePromoCode::class, [
            'code' => 'MAL',
            '--discount-percent' => '10',
            '--applies-to' => 'todo',
        ])->assertFailed();

        $this->artisan(CreatePromoCode::class, [
            'code' => 'REGALOMAL',
            '--credits' => '50',
            '--applies-to' => 'credits',
        ])->assertFailed();

        $this->assertSame(0, PromoCode::count());
    }

    public function test_a_batch_carries_the_expiry_and_scope_on_every_code(): void
    {
        $this->artisan(CreatePromoCodeBatch::class, [
            'prefix' => 'tiktok',
            '--count' => '3',
            '--discount-percent' => '15',
            '--expires' => now()->addDays(5)->format('Y-m-d'),
            '--applies-to' => 'case',
        ])->assertSuccessful();

        $codes = PromoCode::where('code', 'like', 'TIKTOK-%')->get();

        $this->assertCount(3, $codes);
        $this->assertTrue($codes->every(fn (PromoCode $promo) => $promo->applies_to === 'case'
            && $promo->expires_at !== null
            && $promo->max_redemptions === 1));
    }
}
