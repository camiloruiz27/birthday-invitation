<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Ads\AdEvent;
use App\Modules\Platform\Ads\AdEvents;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Reporting sales and registrations back to TikTok (Events API) and Meta
 * (Conversions API). The browser pixel loses events to blockers and in-app
 * browsers; these server-side copies are what recover them, matched to the
 * browser's by event_id so each is counted once.
 *
 * Two promises are tested harder than the payloads: nothing is sent without
 * the visitor's marketing consent, and a failing ad network can never break
 * a payment.
 */
class AdEventsTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private const SECRET = 'test-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'platform.payments.enabled' => true,
            'platform.payments.identity_key' => 'test-identity',
            'platform.payments.secret_key' => self::SECRET,
            'platform.simulated_checkout' => false,
            'platform.ads.tiktok.pixel_id' => 'TT_PIXEL',
            'platform.ads.tiktok.access_token' => 'tt-token',
            'platform.ads.meta.pixel_id' => 'META_PIXEL',
            'platform.ads.meta.access_token' => 'meta-token',
        ]);
    }

    private function consent(bool $marketing = true, int $version = 3): string
    {
        return json_encode(['v' => $version, 'analytics' => false, 'recording' => false, 'marketing' => $marketing, 'ts' => 1]);
    }

    private function fakeEverything(int $adStatus = 200): void
    {
        Http::fake([
            '*/online/link/v1' => Http::response([
                'payload' => ['payment_link' => 'LNK_AD', 'url' => 'https://checkout.bold.co/LNK_AD'],
                'errors' => [],
            ], 200),
            'https://business-api.tiktok.com/*' => Http::response(['code' => 0, 'message' => 'OK'], $adStatus),
            'https://graph.facebook.com/*' => Http::response(['events_received' => 1], $adStatus),
        ]);
    }

    private function adRequests(string $host): array
    {
        // values(): recorded() keeps the position each request had among ALL
        // requests, so without it the first ad request is not at index 0.
        return Http::recorded(fn (HttpRequest $request) => str_contains($request->url(), $host))
            ->map(fn ($pair) => $pair[0])
            ->values()
            ->all();
    }

    /**
     * Starts a case checkout as the browser would, with the consent cookie
     * (and the pixels' own cookies) when given.
     */
    private function buy(User $user, array $cookies = []): Order
    {
        $case = $this->catalogCase('steve-jacobs');

        $client = $this->actingAs($user)->withHeader('User-Agent', 'TestBrowser/1.0');

        foreach ($cookies as $name => $value) {
            $client = $client->withUnencryptedCookie($name, $value);
        }

        $client
            ->withSession(['attribution' => ['first' => ['utm_source' => 'tiktok'], 'last' => ['utm_source' => 'tiktok', 'ttclid' => 'CLK1', 'landing_path' => '/casos/steve-jacobs']]])
            ->post(route('cases.acquire', $case->slug))
            ->assertRedirect('https://checkout.bold.co/LNK_AD');

        return Order::sole();
    }

    private function settle(Order $order): void
    {
        $payload = [
            'id' => 'evt-1',
            'type' => 'SALE_APPROVED',
            'data' => [
                'payment_id' => 'BOLD-PAY-1',
                'metadata' => ['reference' => $order->reference],
                'amount' => ['currency' => 'COP', 'total' => $order->amount],
            ],
        ];
        $raw = json_encode($payload);

        $this->call('POST', route('payments.webhook.bold'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BOLD_SIGNATURE' => hash_hmac('sha256', base64_encode($raw), self::SECRET),
        ], $raw)->assertOk();
    }

    // --- The consent promise ----------------------------------------------

    public function test_an_order_remembers_the_browser_and_whether_marketing_was_accepted(): void
    {
        $this->fakeEverything();
        $user = $this->userWithoutAccess();

        $order = $this->buy($user, [
            'mc_consent' => $this->consent(),
            '_fbp' => 'fb.1.111.222',
            '_ttp' => 'ttp-abc',
        ]);

        $this->assertTrue($order->marketing_consent);
        $this->assertSame('TestBrowser/1.0', $order->client_user_agent);
        $this->assertNotNull($order->client_ip);
        $this->assertSame('fb.1.111.222', $order->attribution['cookies']['fbp']);
        $this->assertSame('ttp-abc', $order->attribution['cookies']['ttp']);
        $this->assertSame('CLK1', $order->attribution['last']['ttclid']);
    }

    public function test_without_marketing_consent_nothing_is_kept_from_the_ad_networks_and_nothing_is_sent(): void
    {
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(), [
            'mc_consent' => $this->consent(marketing: false),
            '_fbp' => 'fb.1.111.222',
            '_ttp' => 'ttp-abc',
        ]);

        $this->assertFalse($order->marketing_consent);
        $this->assertArrayNotHasKey('cookies', $order->attribution);

        $this->settle($order);

        $this->assertTrue($order->fresh()->isApproved());
        $this->assertSame([], $this->adRequests('tiktok.com'));
        $this->assertSame([], $this->adRequests('facebook.com'));
    }

    public function test_no_consent_cookie_at_all_means_no_consent(): void
    {
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess());

        $this->settle($order);

        $this->assertFalse($order->fresh()->marketing_consent);
        $this->assertSame([], $this->adRequests('tiktok.com'));
        $this->assertSame([], $this->adRequests('facebook.com'));
    }

    public function test_a_consent_given_under_an_older_version_does_not_count(): void
    {
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(), ['mc_consent' => $this->consent(true, version: 2)]);

        $this->assertFalse($order->marketing_consent);
    }

    public function test_a_garbled_consent_cookie_does_not_count(): void
    {
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(), ['mc_consent' => '{not json']);

        $this->assertFalse($order->marketing_consent);
    }

    // --- Purchases ----------------------------------------------------------

    public function test_a_paid_order_with_consent_is_reported_to_both_networks_with_the_order_id(): void
    {
        $this->fakeEverything();
        $user = $this->userWithoutAccess(['email' => 'Ana.Perez@Example.com']);
        $order = $this->buy($user, [
            'mc_consent' => $this->consent(),
            '_fbp' => 'fb.1.111.222',
            '_fbc' => 'fb.1.333.CLICK',
            '_ttp' => 'ttp-abc',
        ]);

        $this->settle($order);

        $hash = hash('sha256', 'ana.perez@example.com');

        $tiktok = $this->adRequests('business-api.tiktok.com');
        $this->assertCount(1, $tiktok);
        $this->assertSame('tt-token', $tiktok[0]->header('Access-Token')[0]);
        $this->assertSame('TT_PIXEL', $tiktok[0]['event_source_id']);
        $event = $tiktok[0]['data'][0];
        // TikTok calls a purchase CompletePayment.
        $this->assertSame('CompletePayment', $event['event']);
        $this->assertSame((string) $order->id, $event['event_id']);
        $this->assertSame($hash, $event['user']['email']);
        $this->assertSame('CLK1', $event['user']['ttclid']);
        $this->assertSame('ttp-abc', $event['user']['ttp']);
        $this->assertSame('TestBrowser/1.0', $event['user']['user_agent']);
        $this->assertSame($order->amount, $event['properties']['value']);
        $this->assertSame('COP', $event['properties']['currency']);
        $this->assertSame('steve-jacobs', $event['properties']['contents'][0]['content_id']);

        $meta = $this->adRequests('graph.facebook.com');
        $this->assertCount(1, $meta);
        $this->assertStringContainsString('/META_PIXEL/events', $meta[0]->url());
        $event = $meta[0]['data'][0];
        $this->assertSame('Purchase', $event['event_name']);
        $this->assertSame((string) $order->id, $event['event_id']);
        $this->assertSame('website', $event['action_source']);
        $this->assertSame([$hash], $event['user_data']['em']);
        $this->assertSame('fb.1.111.222', $event['user_data']['fbp']);
        $this->assertSame('fb.1.333.CLICK', $event['user_data']['fbc']);
        $this->assertSame($order->amount, $event['custom_data']['value']);
        $this->assertSame(['steve-jacobs'], $event['custom_data']['content_ids']);
    }

    public function test_the_plain_email_is_never_put_on_the_wire(): void
    {
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(['email' => 'secreto@example.com']), ['mc_consent' => $this->consent()]);

        $this->settle($order);

        foreach (['business-api.tiktok.com', 'graph.facebook.com'] as $host) {
            foreach ($this->adRequests($host) as $request) {
                $this->assertStringNotContainsString('secreto@example.com', json_encode($request->data()));
            }
        }
    }

    public function test_meta_rebuilds_the_click_cookie_from_the_click_id_when_the_cookie_never_got_set(): void
    {
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(), ['mc_consent' => $this->consent()]);
        $order->update(['attribution' => ['last' => ['fbclid' => 'FBCLICK'], 'cookies' => []]]);

        $this->settle($order->fresh());

        $event = $this->adRequests('graph.facebook.com')[0]['data'][0];
        $this->assertMatchesRegularExpression('/^fb\.1\.\d+\.FBCLICK$/', $event['user_data']['fbc']);
    }

    public function test_a_webhook_delivered_twice_reports_the_sale_once(): void
    {
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(), ['mc_consent' => $this->consent()]);

        $this->settle($order);
        $this->settle($order);

        $this->assertCount(1, $this->adRequests('business-api.tiktok.com'));
        $this->assertCount(1, $this->adRequests('graph.facebook.com'));
    }

    public function test_a_rejected_payment_reports_nothing(): void
    {
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(), ['mc_consent' => $this->consent()]);

        $raw = json_encode([
            'id' => 'evt-1', 'type' => 'SALE_REJECTED',
            'data' => ['payment_id' => 'P', 'metadata' => ['reference' => $order->reference], 'amount' => ['currency' => 'COP', 'total' => 1]],
        ]);
        $this->call('POST', route('payments.webhook.bold'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_BOLD_SIGNATURE' => hash_hmac('sha256', base64_encode($raw), self::SECRET),
        ], $raw);

        $this->assertSame([], $this->adRequests('business-api.tiktok.com'));
        $this->assertSame([], $this->adRequests('graph.facebook.com'));
    }

    public function test_a_network_without_a_token_is_skipped_and_the_other_still_gets_the_event(): void
    {
        config(['platform.ads.tiktok.access_token' => '']);
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(), ['mc_consent' => $this->consent()]);

        $this->settle($order);

        $this->assertSame([], $this->adRequests('business-api.tiktok.com'));
        $this->assertCount(1, $this->adRequests('graph.facebook.com'));
    }

    public function test_with_no_ad_configuration_at_all_nothing_leaves(): void
    {
        config([
            'platform.ads.tiktok.access_token' => '',
            'platform.ads.meta.access_token' => '',
        ]);
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(), ['mc_consent' => $this->consent()]);

        $this->settle($order);

        $this->assertSame([], $this->adRequests('business-api.tiktok.com'));
        $this->assertSame([], $this->adRequests('graph.facebook.com'));
    }

    public function test_test_event_codes_are_sent_so_events_show_up_in_the_test_tabs(): void
    {
        config([
            'platform.ads.tiktok.test_event_code' => 'TEST111',
            'platform.ads.meta.test_event_code' => 'TEST222',
        ]);
        $this->fakeEverything();
        $order = $this->buy($this->userWithoutAccess(), ['mc_consent' => $this->consent()]);

        $this->settle($order);

        $this->assertSame('TEST111', $this->adRequests('business-api.tiktok.com')[0]['test_event_code']);
        $this->assertSame('TEST222', $this->adRequests('graph.facebook.com')[0]['test_event_code']);
    }

    // --- The other promise: a payment never breaks ------------------------

    public function test_ad_networks_failing_never_undo_or_block_a_payment(): void
    {
        $this->fakeEverything(adStatus: 500);
        $user = $this->userWithoutAccess();
        $order = $this->buy($user, ['mc_consent' => $this->consent()]);

        $this->settle($order);

        $this->assertTrue($order->fresh()->isApproved());
        $this->assertTrue($user->fresh()->ownsCase('steve-jacobs'));
    }

    public function test_ad_networks_being_unreachable_never_undo_or_block_a_payment(): void
    {
        Http::fake([
            '*/online/link/v1' => Http::response([
                'payload' => ['payment_link' => 'LNK_AD', 'url' => 'https://checkout.bold.co/LNK_AD'],
                'errors' => [],
            ], 200),
            'https://business-api.tiktok.com/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'),
            'https://graph.facebook.com/*' => fn () => throw new \Illuminate\Http\Client\ConnectionException('timeout'),
        ]);
        Log::spy();

        $user = $this->userWithoutAccess();
        $order = $this->buy($user, ['mc_consent' => $this->consent()]);

        $this->settle($order);

        $this->assertTrue($order->fresh()->isApproved());
        $this->assertTrue($user->fresh()->ownsCase('steve-jacobs'));
        Log::shouldHaveReceived('warning')->with('platform_ads_event_failed', \Mockery::type('array'));
    }

    public function test_a_rejected_event_is_logged_without_the_access_token(): void
    {
        $this->fakeEverything(adStatus: 400);
        Log::spy();
        $order = $this->buy($this->userWithoutAccess(), ['mc_consent' => $this->consent()]);

        $this->settle($order);

        Log::shouldHaveReceived('warning')->with('platform_ads_meta_event_failed', \Mockery::on(
            fn (array $context) => ! str_contains(json_encode($context), 'meta-token')
        ));
    }

    // --- Free orders ----------------------------------------------------------

    public function test_a_free_order_is_reported_too_because_it_never_reaches_the_webhook(): void
    {
        $this->fakeEverything();
        PromoCode::create(['code' => 'GRATIS', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 100]);
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->withUnencryptedCookie('mc_consent', $this->consent())
            ->post(route('cases.acquire', $case->slug), ['promo_code' => 'GRATIS'])
            ->assertRedirect();

        $order = Order::sole();
        $this->assertSame(0, $order->amount);
        $this->assertTrue($user->fresh()->ownsCase($case));

        $event = $this->adRequests('graph.facebook.com')[0]['data'][0];
        $this->assertSame('Purchase', $event['event_name']);
        $this->assertSame((string) $order->id, $event['event_id']);
        $this->assertSame(0, $event['custom_data']['value']);
    }

    // --- Registrations ------------------------------------------------------

    private function registerAs(string $email, array $cookies = []): void
    {
        $client = $this->withHeader('User-Agent', 'TestBrowser/1.0');

        foreach ($cookies as $name => $value) {
            $client = $client->withUnencryptedCookie($name, $value);
        }

        $client->post(route('register'), [
            'name' => 'Ana',
            'email' => $email,
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'accept_terms' => true,
        ]);
    }

    public function test_a_registration_with_consent_is_reported(): void
    {
        $this->fakeEverything();

        $this->registerAs('nuevo@example.com', ['mc_consent' => $this->consent(), '_fbp' => 'fb.1.1.2']);

        $user = User::where('email', 'nuevo@example.com')->firstOrFail();

        $meta = $this->adRequests('graph.facebook.com');
        $this->assertCount(1, $meta);
        $event = $meta[0]['data'][0];
        $this->assertSame('CompleteRegistration', $event['event_name']);
        // The same id the browser sends from the registration form.
        $this->assertSame("reg-{$user->id}", $event['event_id']);
        $this->assertSame(hash('sha256', 'nuevo@example.com'), $event['user_data']['em'][0]);
        $this->assertArrayNotHasKey('custom_data', $event);

        $this->assertCount(1, $this->adRequests('business-api.tiktok.com'));
    }

    public function test_a_registration_without_consent_is_not_reported(): void
    {
        $this->fakeEverything();

        $this->registerAs('callado@example.com', ['mc_consent' => $this->consent(marketing: false)]);

        $this->assertDatabaseHas('users', ['email' => 'callado@example.com']);
        $this->assertSame([], $this->adRequests('graph.facebook.com'));
        $this->assertSame([], $this->adRequests('business-api.tiktok.com'));
    }

    public function test_a_failing_ad_network_never_blocks_a_registration(): void
    {
        $this->fakeEverything(adStatus: 500);

        $this->registerAs('resiste@example.com', ['mc_consent' => $this->consent()]);

        $this->assertDatabaseHas('users', ['email' => 'resiste@example.com']);
        $this->assertAuthenticated();
    }

    // --- The event itself -----------------------------------------------------

    public function test_an_event_survives_being_put_on_a_queue(): void
    {
        $event = new AdEvent(
            name: AdEvent::PURCHASE,
            eventId: '42',
            time: 1700000000,
            email: ' Ana@Example.com ',
            value: 49900,
            currency: 'COP',
            contents: [['id' => 'caso', 'quantity' => 1]],
        );

        $copy = AdEvent::fromArray($event->toArray());

        $this->assertEquals($event, $copy);
        $this->assertSame(hash('sha256', 'ana@example.com'), $copy->hashedEmail());
    }

    public function test_consent_is_read_only_from_the_current_cookie_version(): void
    {
        $request = fn (?string $value) => \Illuminate\Http\Request::create('/', 'GET', [], $value === null ? [] : ['mc_consent' => $value]);

        $this->assertTrue(AdEvents::marketingConsent($request($this->consent())));
        $this->assertFalse(AdEvents::marketingConsent($request($this->consent(false))));
        $this->assertFalse(AdEvents::marketingConsent($request($this->consent(true, version: 2))));
        $this->assertFalse(AdEvents::marketingConsent($request('')));
        $this->assertFalse(AdEvents::marketingConsent($request(null)));
    }

    public function test_the_entitlement_still_arrives_through_the_normal_path(): void
    {
        // Sanity: reporting to ad networks is additive; delivery is unchanged.
        $this->fakeEverything();
        $user = $this->userWithoutAccess();
        $order = $this->buy($user, ['mc_consent' => $this->consent()]);

        $this->settle($order);

        $this->assertSame(
            Entitlement::SOURCE_PURCHASE,
            Entitlement::where('user_id', $user->id)->sole()->source
        );
    }
}
