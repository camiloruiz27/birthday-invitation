<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Models\Order;
use App\Modules\Platform\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * An ad click lands anywhere with utm_* / click ids / a promo code on the
 * URL, and everything has to survive until the visitor buys: browsing,
 * registering and confirming their email, each on a URL that carries none of
 * it. The landing page an ad points at must never matter.
 */
class AttributionTest extends TestCase
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

    private function register(string $email = 'ana@example.com'): User
    {
        $this->post(route('register'), [
            'name' => 'Ana',
            'email' => $email,
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'accept_terms' => true,
        ]);

        return User::where('email', $email)->firstOrFail();
    }

    // --- Capturing ---------------------------------------------------------

    public function test_a_campaign_link_is_remembered_whatever_page_it_lands_on(): void
    {
        $this->get('/?utm_source=tiktok&utm_campaign=lanzamiento&ttclid=ABC123&irrelevant=x')->assertOk();

        $attribution = session('attribution');

        $this->assertSame('tiktok', $attribution['first']['utm_source']);
        $this->assertSame('lanzamiento', $attribution['first']['utm_campaign']);
        $this->assertSame('ABC123', $attribution['first']['ttclid']);
        $this->assertSame('/', $attribution['first']['landing_path']);
        // Only what we track is ever stored.
        $this->assertArrayNotHasKey('irrelevant', $attribution['first']);
    }

    public function test_the_first_touch_is_kept_and_the_last_one_moves(): void
    {
        $this->get('/?utm_source=tiktok&utm_campaign=primera');
        $this->get('/casos?utm_source=meta&fbclid=ZZZ');

        $attribution = session('attribution');

        $this->assertSame('tiktok', $attribution['first']['utm_source']);
        $this->assertSame('meta', $attribution['last']['utm_source']);
        $this->assertSame('ZZZ', $attribution['last']['fbclid']);
        $this->assertSame('/casos', $attribution['last']['landing_path']);
    }

    public function test_a_visit_without_tracking_parameters_remembers_nothing(): void
    {
        $this->get('/casos?pagina=2')->assertOk();

        $this->assertNull(session('attribution'));
        $this->assertNull(session('pending_promo'));
    }

    public function test_a_promo_code_in_the_link_is_remembered_normalised(): void
    {
        $this->get('/casos?promo_code=tiktok20')->assertOk();

        $this->assertSame('TIKTOK20', session('pending_promo'));
    }

    public function test_a_malformed_promo_code_is_ignored(): void
    {
        $this->get('/casos?promo_code='.urlencode('<script>alert(1)</script>'))->assertOk();

        $this->assertNull(session('pending_promo'));
    }

    public function test_an_overlong_value_is_cut_not_stored_whole(): void
    {
        $this->get('/?utm_source='.str_repeat('a', 500))->assertOk();

        $this->assertSame(200, strlen(session('attribution')['first']['utm_source']));
    }

    // --- Registering -------------------------------------------------------

    public function test_registering_keeps_where_the_account_came_from(): void
    {
        $this->get('/casos?utm_source=tiktok&utm_campaign=lanzamiento&promo_code=TIKTOK20');

        $user = $this->register();

        $this->assertSame('tiktok', $user->attribution['first']['utm_source']);
        $this->assertSame('lanzamiento', $user->attribution['last']['utm_campaign']);
        $this->assertSame('TIKTOK20', $user->attribution['promo']);
    }

    public function test_registering_with_no_campaign_stores_no_attribution(): void
    {
        $this->assertNull($this->register()->attribution);
    }

    public function test_registering_for_a_case_sends_the_new_account_straight_to_its_checkout(): void
    {
        $case = $this->catalogCase('steve-jacobs');

        $this->get(route('register', ['case' => $case->slug]))->assertOk();
        $this->assertSame(route('cases.checkout.review', $case->slug), session('url.intended'));

        $this->post(route('register'), [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'accept_terms' => true,
        ])->assertRedirect(route('cases.checkout.review', $case->slug));

        // Used up: signing in later must not offer it again.
        $this->assertNull(session('url.intended'));

        // Saved on the account too, for the confirmation mail opened on
        // another device where this session does not exist.
        $user = User::where('email', 'ana@example.com')->firstOrFail();
        $this->assertSame(route('cases.checkout.review', $case->slug), $user->attribution['intended']);
    }

    public function test_an_unknown_or_unpublished_case_in_the_register_link_is_ignored(): void
    {
        $this->catalogCase('steve-jacobs');

        $this->get(route('register', ['case' => 'no-existe']))->assertOk();
        $this->assertNull(session('url.intended'));

        $this->get(route('register', ['case' => 'https://evil.example/']))->assertOk();
        $this->assertNull(session('url.intended'));
    }

    public function test_the_register_link_sends_to_the_case_page_when_payments_are_off(): void
    {
        config(['platform.payments.enabled' => false]);
        $case = $this->catalogCase('steve-jacobs');

        $this->get(route('register', ['case' => $case->slug]));

        $this->assertSame(route('cases.show', $case->slug), session('url.intended'));
    }

    // --- Confirming the email ---------------------------------------------

    private function verificationUrl(User $user): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
    }

    public function test_confirming_the_email_continues_to_where_the_visitor_was_going(): void
    {
        $case = $this->catalogCase('steve-jacobs');
        $user = User::factory()->unverified()->create();
        $target = route('cases.checkout.review', $case->slug);

        $this->actingAs($user)
            ->withSession(['url.intended' => $target])
            ->get($this->verificationUrl($user))
            ->assertRedirect($target);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_confirming_on_another_device_still_finds_where_they_were_going(): void
    {
        $case = $this->catalogCase('steve-jacobs');
        $target = route('cases.checkout.review', $case->slug);
        $user = User::factory()->unverified()->create(['attribution' => ['intended' => $target]]);

        // No session memory at all: only what was saved on the account.
        $this->actingAs($user)
            ->get($this->verificationUrl($user))
            ->assertRedirect($target);
    }

    public function test_a_saved_destination_outside_the_site_is_never_followed(): void
    {
        $user = User::factory()->unverified()->create(['attribution' => ['intended' => 'https://evil.example/phish']]);

        $this->actingAs($user)
            ->get($this->verificationUrl($user))
            ->assertRedirect(route('dashboard'));
    }

    public function test_confirming_with_nowhere_to_go_lands_on_the_panel_as_before(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get($this->verificationUrl($user))
            ->assertRedirect(route('dashboard'));
    }

    // --- The remembered code reaching checkout ----------------------------

    public function test_the_case_review_applies_the_code_that_came_with_the_ad(): void
    {
        $user = User::factory()->create();
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'TIKTOK20', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 20]);

        $this->actingAs($user)
            ->withSession(['pending_promo' => 'TIKTOK20'])
            ->get(route('cases.checkout.review', $case->slug))
            ->assertInertia(fn (Assert $page) => $page
                ->where('promo_code', 'TIKTOK20')
                ->where('final_amount', (int) floor($case->price_amount * 0.8))
            );
    }

    public function test_the_credits_review_applies_the_code_that_came_with_the_ad(): void
    {
        $user = User::factory()->create();
        $package = collect((array) config('platform.credit_packages'))->first();
        PromoCode::create(['code' => 'TOKENS25', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 25]);

        $this->actingAs($user)
            ->withSession(['pending_promo' => 'TOKENS25'])
            ->get(route('credits.checkout.review', ['package' => $package['id']]))
            ->assertInertia(fn (Assert $page) => $page
                ->where('promo_code', 'TOKENS25')
                ->where('final_amount', (int) floor($package['price_amount'] * 0.75))
            );
    }

    public function test_after_registering_the_code_is_found_on_the_account_not_just_the_session(): void
    {
        $user = User::factory()->create(['attribution' => ['promo' => 'TIKTOK20']]);
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'TIKTOK20', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 20]);

        $this->actingAs($user)
            ->get(route('cases.checkout.review', $case->slug))
            ->assertInertia(fn (Assert $page) => $page->where('promo_code', 'TIKTOK20'));
    }

    public function test_a_code_typed_on_the_url_beats_the_remembered_one(): void
    {
        $user = User::factory()->create();
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'VIEJO10', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 10]);
        PromoCode::create(['code' => 'NUEVO30', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 30]);

        $this->actingAs($user)
            ->withSession(['pending_promo' => 'VIEJO10'])
            ->get(route('cases.checkout.review', $case->slug).'?promo_code=NUEVO30')
            ->assertInertia(fn (Assert $page) => $page->where('promo_code', 'NUEVO30'));
    }

    public function test_a_remembered_code_that_no_longer_works_is_reported_once_and_forgotten(): void
    {
        $user = User::factory()->create();
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create([
            'code' => 'VENCIDO',
            'discount_type' => PromoCode::DISCOUNT_PERCENT,
            'discount_value' => 20,
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($user)
            ->withSession(['pending_promo' => 'VENCIDO'])
            ->get(route('cases.checkout.review', $case->slug))
            ->assertInertia(fn (Assert $page) => $page
                ->where('promo_code', null)
                ->where('final_amount', $case->price_amount)
                ->whereNot('promo_error', null)
            );

        $this->assertNull(session('pending_promo'));
    }

    public function test_buying_with_the_code_uses_it_up_so_it_stops_being_offered(): void
    {
        Http::fake(['*/online/link/v1' => Http::response([
            'payload' => ['payment_link' => 'LNK_1', 'url' => 'https://checkout.bold.co/LNK_1'],
            'errors' => [],
        ], 200)]);

        $user = User::factory()->create(['attribution' => ['promo' => 'TIKTOK20']]);
        $case = $this->catalogCase('steve-jacobs');
        PromoCode::create(['code' => 'TIKTOK20', 'discount_type' => PromoCode::DISCOUNT_PERCENT, 'discount_value' => 20]);

        $this->actingAs($user)
            ->withSession(['pending_promo' => 'TIKTOK20'])
            ->post(route('cases.acquire', $case->slug), ['promo_code' => 'TIKTOK20'])
            ->assertRedirect('https://checkout.bold.co/LNK_1');

        $this->assertNull(session('pending_promo'));
        $this->assertArrayNotHasKey('promo', (array) $user->fresh()->attribution);
    }

    // --- Landing on the order ----------------------------------------------

    public function test_an_order_records_the_campaign_that_brought_the_buyer(): void
    {
        Http::fake(['*/online/link/v1' => Http::response([
            'payload' => ['payment_link' => 'LNK_1', 'url' => 'https://checkout.bold.co/LNK_1'],
            'errors' => [],
        ], 200)]);

        $user = User::factory()->create();
        $case = $this->catalogCase('steve-jacobs');

        $this->actingAs($user)
            ->withSession(['attribution' => [
                'first' => ['utm_source' => 'tiktok'],
                'last' => ['utm_source' => 'tiktok', 'ttclid' => 'CLK1'],
            ]])
            ->post(route('cases.acquire', $case->slug));

        $order = Order::sole();
        $this->assertSame('tiktok', $order->attribution['first']['utm_source']);
        $this->assertSame('CLK1', $order->attribution['last']['ttclid']);
        $this->assertNotNull($order->client_ip);
        $this->assertFalse($order->marketing_consent);
    }
}
