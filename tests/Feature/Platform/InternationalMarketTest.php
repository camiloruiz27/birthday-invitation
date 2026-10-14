<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use App\Modules\Platform\Payments\Exceptions\PaymentProviderException;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Support\Geo\CountryLookup;
use App\Modules\Platform\Support\PromoCodeMetrics;
use App\Modules\Platform\Support\MarketResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The international market: which price list a visitor is on, and that an
 * order sold to them is created, discounted and sent to Bold in USD derived
 * from the COP base price.
 */
class InternationalMarketTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'platform.payments.enabled' => true,
            'platform.payments.identity_key' => 'test-identity',
            'platform.payments.secret_key' => 'test-secret',
            'platform.payments.usd_amount_unit' => 'dollars',
            'platform.simulated_checkout' => false,
            'platform.market.international_enabled' => true,
        ]);
    }

    private function countryIs(?string $country): void
    {
        $this->app->instance(CountryLookup::class, new class($country) implements CountryLookup {
            public function __construct(private ?string $country)
            {
            }

            public function countryFor(?string $ip): ?string
            {
                return $this->country;
            }
        });
    }

    private function fakeBold(): void
    {
        Http::fake([
            '*/online/link/v1' => Http::response([
                'payload' => ['payment_link' => 'LNK_USD', 'url' => 'https://checkout.bold.co/LNK_USD'],
                'errors' => [],
            ], 200),
        ]);
    }

    private function intl(): static
    {
        config(['platform.market.force' => 'INTL']);

        return $this;
    }

    public function test_a_visitor_outside_colombia_is_on_the_international_market(): void
    {
        $this->countryIs('US');

        $this->get(route('cases.index'))->assertInertia(fn (Assert $page) => $page
            ->where('market.code', 'INTL')
            ->where('market.currency', 'USD'));
    }

    public function test_a_visitor_in_colombia_or_with_an_unknown_country_is_on_the_colombian_market(): void
    {
        foreach (['CO', null] as $country) {
            $this->countryIs($country);

            $this->get(route('cases.index'))->assertInertia(fn (Assert $page) => $page
                ->where('market.code', 'CO')
                ->where('market.currency', 'COP'));
        }
    }

    public function test_the_visitors_own_choice_beats_the_ip(): void
    {
        $this->countryIs('US');

        // withCookie() encrypts it the way the server's own cookie arrives back.
        $this->withCookie(MarketResolver::COOKIE, 'CO')
            ->get(route('cases.index'))
            ->assertInertia(fn (Assert $page) => $page->where('market.code', 'CO'));
    }

    public function test_the_footer_selector_stores_the_choice(): void
    {
        $this->countryIs('CO');

        $this->post(route('market.update'), ['market' => 'INTL'])
            ->assertRedirect()
            ->assertCookie(MarketResolver::COOKIE);

        $this->get(route('cases.index'))->assertInertia(fn (Assert $page) => $page->where('market.code', 'INTL'));
    }

    public function test_the_selector_rejects_an_unknown_market(): void
    {
        $this->post(route('market.update'), ['market' => 'MARS'])->assertStatus(422);
    }

    public function test_with_the_flag_off_everyone_is_colombian_even_from_abroad(): void
    {
        config(['platform.market.international_enabled' => false]);
        $this->countryIs('US');

        $this->get(route('cases.index'))->assertInertia(fn (Assert $page) => $page
            ->where('market.code', 'CO')
            ->where('market.selectable', false));
    }

    public function test_a_query_string_cannot_pick_the_market_in_production(): void
    {
        $this->countryIs('US');
        $this->app['env'] = 'production';

        $this->get(route('cases.index', ['market' => 'CO']))
            ->assertInertia(fn (Assert $page) => $page->where('market.code', 'INTL'));
    }

    public function test_an_international_order_is_created_and_sent_to_bold_in_usd(): void
    {
        $this->fakeBold();
        $this->intl();
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs'); // 89.000 COP -> USD 28

        $this->actingAs($user)
            ->post(route('cases.acquire', 'steve-jacobs'))
            ->assertRedirect('https://checkout.bold.co/LNK_USD');

        $order = Order::sole();
        $this->assertSame('USD', $order->currency);
        $this->assertSame(2800, $order->amount);
        $this->assertSame(2800, $order->list_amount);
        $this->assertSame('INTL', $order->market);
        $this->assertSame($case->price_amount, $order->base_list_amount);
        $this->assertSame($case->price_amount, $order->base_amount);
        $this->assertSame(3500, $order->pricing_snapshot['fx_rate']);

        Http::assertSent(fn ($request) => $request['amount']['currency'] === 'USD'
            && $request['amount']['total_amount'] === 28);
    }

    public function test_a_percent_code_takes_exactly_its_percent_off_the_usd_price(): void
    {
        $this->fakeBold();
        $this->intl();
        PromoCode::create(['code' => 'MITAD', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 50]);
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'MITAD']);

        // A percent code comes off the USD price the buyer sees: half of
        // USD 28 is USD 14, exactly. base_amount is the COP side, for accounting.
        $order = Order::sole();
        $this->assertSame(44500, $order->base_amount);
        $this->assertSame(1400, $order->amount);
        $this->assertSame(2800, $order->list_amount);
        $this->assertSame(1400, $order->discount_amount);
    }

    public function test_a_percent_code_on_an_odd_dollar_price_rounds_down_to_whole_dollars(): void
    {
        $this->fakeBold();
        $this->intl();
        PromoCode::create(['code' => 'MITAD', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 50]);
        $user = $this->userWithoutAccess();

        // starter pack: 9.900 COP -> USD 5; half is 2,50, which becomes USD 2 —
        // the buyer never gets less than the 50% they were promised.
        $this->actingAs($user)
            ->post(route('credits.purchase'), ['package' => 'starter', 'promo_code' => 'MITAD'])
            ->assertRedirect('https://checkout.bold.co/LNK_USD');

        $order = Order::sole();
        $this->assertSame(200, $order->amount);
        $this->assertSame(300, $order->discount_amount);
        Http::assertSent(fn ($request) => $request['amount']['total_amount'] === 2);
    }

    public function test_a_percent_code_never_makes_a_cheap_purchase_free(): void
    {
        $this->fakeBold();
        $this->intl();
        PromoCode::create(['code' => 'CASITODO', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 95]);
        $user = $this->userWithoutAccess();

        // 95% off USD 5 is 0,25: it would round to 0, which is what marks a gift
        // delivered without payment. It stops at USD 1 and goes through Bold.
        $this->actingAs($user)
            ->post(route('credits.purchase'), ['package' => 'starter', 'promo_code' => 'CASITODO'])
            ->assertRedirect('https://checkout.bold.co/LNK_USD');

        $this->assertSame(100, Order::sole()->amount);
        Http::assertSent(fn ($request) => $request['amount']['total_amount'] === 1);
    }

    public function test_whole_dollars_reach_bold_in_either_unit(): void
    {
        PromoCode::create(['code' => 'VEINTE', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 20]);
        $this->intl();
        $this->catalogCase('steve-jacobs');

        // 20% off USD 28 = 22,40, which becomes USD 22: 2200 cents, 22 dollars.
        foreach (['dollars' => 22, 'cents' => 2200] as $unit => $sent) {
            Order::query()->delete();
            config(['platform.payments.usd_amount_unit' => $unit]);
            $this->fakeBold();

            $this->actingAs($this->userWithoutAccess())
                ->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'VEINTE']);

            $this->assertSame(2200, Order::sole()->amount);
            Http::assertSent(fn ($request) => $request['amount']['total_amount'] === $sent);
        }
    }

    public function test_an_amount_with_cents_is_never_sent_to_bold(): void
    {
        Http::fake();

        // Not reachable through the pricer, which only makes whole dollars; this
        // is the last line of defence if something ever produced one.
        $order = Order::make(['currency' => 'USD', 'amount' => 1050, 'reference' => 'X', 'type' => Order::TYPE_CASE]);

        $this->expectException(PaymentProviderException::class);

        try {
            app(PaymentProvider::class)->createCheckoutLink($order, 'https://example.test/back');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_a_fixed_code_is_a_cop_amount_even_for_an_international_buyer(): void
    {
        $this->fakeBold();
        $this->intl();
        PromoCode::create(['code' => 'DIEZMIL', 'discount_type' => PromoCode::DISCOUNT_FIXED, 'discount_value' => 10000]);
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'DIEZMIL']);

        // 89.000 - 10.000 = 79.000 COP -> (22,571 + 1) * 1,05 = 24,75 -> USD 25.
        $order = Order::sole();
        $this->assertSame(79000, $order->base_amount);
        $this->assertSame(2500, $order->amount);
    }

    public function test_a_full_discount_is_free_in_usd_too_and_never_reaches_bold(): void
    {
        $this->intl();
        PromoCode::create(['code' => 'GRATIS100', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 100]);
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');

        Http::fake();

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'GRATIS100']);

        Http::assertNothingSent();
        $this->assertTrue($user->fresh()->ownsCase($case));

        $order = Order::sole();
        $this->assertSame(0, $order->amount);
        $this->assertSame('USD', $order->currency);
        $this->assertSame(Order::STATUS_APPROVED, $order->status);
    }

    public function test_a_usd_order_is_refused_when_the_bold_unit_is_not_valid(): void
    {
        config(['platform.payments.usd_amount_unit' => 'pesos']);
        $this->fakeBold();
        $this->intl();
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));

        Http::assertNothingSent();
    }

    public function test_the_unit_can_be_cents(): void
    {
        config(['platform.payments.usd_amount_unit' => 'cents']);
        $this->fakeBold();
        $this->intl();
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));

        Http::assertSent(fn ($request) => $request['amount']['total_amount'] === 2800);
    }

    public function test_the_catalog_and_the_pricing_page_show_usd_to_an_international_visitor(): void
    {
        $this->intl();
        $this->catalogCase('steve-jacobs');

        $this->get(route('pricing'))->assertInertia(fn (Assert $page) => $page
            ->where('cases.0.currency', 'USD')
            ->where('cases.0.price_amount', 2800));

        $this->get(route('cases.show', 'steve-jacobs'))->assertInertia(fn (Assert $page) => $page
            ->where('case.currency', 'USD')
            ->where('case.price_amount', 2800));
    }

    public function test_the_credit_packages_are_listed_in_usd(): void
    {
        $this->intl();

        $this->actingAs($this->userWithoutAccess())
            ->get(route('credits'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('packages.0.currency', 'USD')
                ->where('packages.0.price_amount', 500)   // 9.900 COP -> USD 5
                ->where('packages.1.price_amount', 800)   // 22.900 COP -> USD 8
                ->where('packages.2.price_amount', 1700)); // 49.900 COP -> USD 17
    }

    public function test_the_case_review_shows_what_the_order_will_charge(): void
    {
        $this->intl();
        PromoCode::create(['code' => 'MITAD', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 50]);
        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->get(route('cases.checkout.review', ['slug' => 'steve-jacobs', 'promo_code' => 'MITAD']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('currency', 'USD')
                ->where('original_amount', 2800)
                ->where('discount_amount', 1400)
                ->where('final_amount', 1400));

        // ...and the order made right after charges exactly that.
        $this->fakeBold();
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'MITAD']);
        $this->assertSame(1400, Order::sole()->amount);
    }

    public function test_the_credits_review_shows_what_the_order_will_charge(): void
    {
        $this->intl();

        $this->actingAs($this->userWithoutAccess())
            ->get(route('credits.checkout.review', ['package' => 'starter']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('currency', 'USD')
                ->where('original_amount', 500)
                ->where('final_amount', 500)
                ->where('discount_amount', null));
    }

    public function test_the_ad_landing_quotes_the_offer_in_usd(): void
    {
        $this->intl();
        PromoCode::create(['code' => 'MITAD', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 50]);
        $this->catalogCase('steve-jacobs');

        $this->withSession(['pending_promo' => 'MITAD'])
            ->get(route('ads.case', 'steve-jacobs'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('case.currency', 'USD')
                ->where('offer.final_amount', 1400)
                ->where('offer.discount_amount', 1400));
    }

    /** A signed SALE_APPROVED for $order, reporting what Bold says it charged. */
    private function approvedWebhook(Order $order, string $currency, int|float $total): void
    {
        $raw = json_encode([
            'id' => 'evt-usd',
            'type' => 'SALE_APPROVED',
            'data' => [
                'payment_id' => 'BOLD-PAY-USD',
                'metadata' => ['reference' => $order->reference],
                'amount' => ['currency' => $currency, 'total' => $total],
            ],
        ]);

        $this->call('POST', route('payments.webhook.bold'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BOLD_SIGNATURE' => hash_hmac('sha256', base64_encode($raw), 'test-secret'),
        ], $raw)->assertOk();
    }

    public function test_a_usd_sale_settled_by_bold_in_cop_is_still_approved_but_flagged(): void
    {
        $this->fakeBold();
        $this->intl();
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));
        $order = Order::sole();

        Log::spy();

        // Bold converted the card charge to pesos at its own rate.
        $this->approvedWebhook($order, 'COP', 96000);

        $this->assertSame(Order::STATUS_APPROVED, $order->fresh()->status);
        $this->assertTrue($user->fresh()->ownsCase($case));
        Log::shouldHaveReceived('warning')->with('platform_payment_currency_differs', \Mockery::type('array'))->once();
    }

    public function test_a_matching_usd_webhook_in_dollars_or_cents_raises_no_warning(): void
    {
        $this->fakeBold();
        $this->intl();
        $this->catalogCase('steve-jacobs');
        $this->actingAs($this->userWithoutAccess())->post(route('cases.acquire', 'steve-jacobs'));
        $order = Order::sole();

        Log::spy();

        $this->approvedWebhook($order, 'USD', 28);

        Log::shouldNotHaveReceived('warning', ['platform_payment_amount_differs', \Mockery::any()]);
        Log::shouldNotHaveReceived('warning', ['platform_payment_currency_differs', \Mockery::any()]);
    }

    public function test_promo_metrics_total_international_orders_in_cop_not_usd_cents(): void
    {
        $this->fakeBold();
        $this->intl();
        PromoCode::create(['code' => 'MITAD', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 50]);
        $this->catalogCase('steve-jacobs');
        $this->actingAs($this->userWithoutAccess())->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'MITAD']);
        $this->approvedWebhook(Order::sole(), 'USD', 14);

        $row = collect(app(PromoCodeMetrics::class)->all()['codes'])->firstWhere('code', 'MITAD');

        // 89.000 COP list, 44.500 COP charged: 44.500 collected and taken off.
        $this->assertSame(44500, $row['collected']);
        $this->assertSame(44500, $row['discounted']);
    }

    public function test_a_tiktok_ad_click_from_abroad_keeps_its_campaign_and_buys_in_usd(): void
    {
        // Decided by IP alone — no cookie, no override — as for a real visitor.
        $this->countryIs('US');
        $this->fakeBold();
        PromoCode::create(['code' => 'MITAD', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 50]);
        $this->catalogCase('steve-jacobs');

        $tiktok = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 musical_ly_33.0.0 BytedanceWebview/d8a21c6';

        // 1. The ad lands them on the case page, in USD, with the ad's code.
        $this->withHeader('User-Agent', $tiktok)
            ->get(route('ads.case', ['slug' => 'steve-jacobs', 'utm_source' => 'tiktok', 'ttclid' => 'CLK1', 'promo_code' => 'MITAD']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('inAppBrowser', 'tiktok')
                ->where('market.code', 'INTL')
                ->where('case.currency', 'USD')
                ->where('offer.final_amount', 1400));

        // 2. Later in the same visit, signed in: the review quotes the same
        //    figure without the code being typed again...
        $user = $this->userWithoutAccess();

        $this->actingAs($user)->withHeader('User-Agent', $tiktok)
            ->get(route('cases.checkout.review', 'steve-jacobs'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('currency', 'USD')
                ->where('promo_code', 'MITAD')
                ->where('final_amount', 1400));

        // 3. ...and the order keeps the campaign and the market.
        $this->actingAs($user)->withHeader('User-Agent', $tiktok)
            ->post(route('cases.acquire', 'steve-jacobs'), ['promo_code' => 'MITAD'])
            ->assertRedirect('https://checkout.bold.co/LNK_USD');

        $order = Order::sole();
        $this->assertSame('INTL', $order->market);
        $this->assertSame(1400, $order->amount);
        $this->assertSame('CLK1', $order->attribution['last']['ttclid']);
        $this->assertSame('tiktok', $order->attribution['last']['utm_source']);
        $this->assertSame('tiktok', $order->attribution['last']['in_app']);
    }

    public function test_the_market_does_not_depend_on_the_browser_the_visitor_is_in(): void
    {
        $this->countryIs('US');
        $this->catalogCase('steve-jacobs');

        // The in-app browser and the phone's own browser have separate cookie
        // jars; the market must come out the same in both.
        $instagram = 'Mozilla/5.0 (iPhone) AppleWebKit/605.1.15 Instagram 300.0.0.0';
        $safari = 'Mozilla/5.0 (iPhone) AppleWebKit/605.1.15 Version/17.0 Mobile Safari/604.1';

        foreach ([$instagram, $safari] as $agent) {
            $this->flushSession();

            $this->withHeader('User-Agent', $agent)
                ->get(route('cases.index'))
                ->assertInertia(fn (Assert $page) => $page->where('market.code', 'INTL'));
        }
    }

    public function test_a_colombian_order_is_unchanged(): void
    {
        $this->fakeBold();
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');

        $this->actingAs($user)->post(route('cases.acquire', 'steve-jacobs'));

        $order = Order::sole();
        $this->assertSame('COP', $order->currency);
        $this->assertSame($case->price_amount, $order->amount);
        $this->assertNull($order->market);
    }
}
