<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Actions\RedeemPromoCode;
use App\Modules\Platform\Models\PromoCode;
use App\Modules\Platform\Payments\BoldPaymentProvider;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The three fixes from the security audit that are not features: the webhook
 * signature's failure mode, the promo code enumeration surface, and the
 * response headers.
 *
 * Each of these is the kind of thing that works perfectly right up until a
 * configuration changes, which is exactly why it is pinned by a test rather
 * than left to a code comment.
 */
class SecurityHardeningTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    /* -----------------------------------------------------------------
     | 1. An empty BOLD_SECRET_KEY must not verify anything
     |---------------------------------------------------------------- */

    /**
     * The bug this guards: hash_hmac() with an empty key returns a valid
     * signature that anyone can compute. Without the guard, a deploy that
     * lost the key would accept a forged SALE_APPROVED and hand out a case
     * for free — while recording it as a genuine sale.
     */
    public function test_a_signature_is_refused_in_production_when_no_secret_key_is_configured(): void
    {
        $this->app['env'] = 'production';
        config(['platform.payments.secret_key' => '']);

        $body = '{"type":"SALE_APPROVED"}';
        // The signature an attacker would compute, knowing only the algorithm.
        $forged = hash_hmac('sha256', base64_encode($body), '');

        $this->assertFalse(
            app(BoldPaymentProvider::class)->verifyWebhookSignature($body, $forged)
        );
    }

    /**
     * Outside production it stays honoured, because Bold's own sandbox really
     * does sign test webhooks with an empty key. Losing this would make the
     * sandbox untestable, which is how the guard ends up being removed later.
     */
    public function test_an_empty_secret_key_still_verifies_outside_production(): void
    {
        $this->app['env'] = 'testing';
        config(['platform.payments.secret_key' => '']);

        $body = '{"type":"SALE_APPROVED"}';

        $this->assertTrue(
            app(BoldPaymentProvider::class)->verifyWebhookSignature(
                $body,
                hash_hmac('sha256', base64_encode($body), '')
            )
        );
    }

    public function test_the_webhook_route_rejects_a_forged_signature_in_production(): void
    {
        $this->app['env'] = 'production';
        config(['platform.payments.secret_key' => '']);

        $body = json_encode(['type' => 'SALE_APPROVED', 'data' => ['metadata' => ['reference' => 'X']]]);

        $this->call(
            'POST',
            route('payments.webhook.bold'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_X_BOLD_SIGNATURE' => hash_hmac('sha256', base64_encode($body), '')],
            $body
        )->assertStatus(401);
    }

    /* -----------------------------------------------------------------
     | 2. Promo codes: one message, and a throttle
     |---------------------------------------------------------------- */

    /**
     * Unknown, switched off and used up must be indistinguishable. Any
     * difference between them tells a guesser they found a real code, which
     * is the single signal a brute-force run needs.
     */
    public function test_every_reason_a_code_cannot_be_used_answers_with_the_same_words(): void
    {
        $user = $this->gameMaster();

        PromoCode::create(['code' => 'APAGADO', 'grants_credits' => 10, 'active' => false]);
        PromoCode::create([
            'code' => 'AGOTADO',
            'grants_credits' => 10,
            'max_redemptions' => 1,
            'redemptions_count' => 1,
        ]);

        $messages = [];

        foreach (['NOEXISTE', 'APAGADO', 'AGOTADO'] as $code) {
            try {
                app(RedeemPromoCode::class)->redeemGift($user, $code);
                $this->fail("El codigo {$code} deberia haber sido rechazado.");
            } catch (\App\Modules\Platform\Exceptions\PromoCodeException $exception) {
                $messages[] = $exception->getMessage();
            }
        }

        $this->assertSame(
            [RedeemPromoCode::UNUSABLE, RedeemPromoCode::UNUSABLE, RedeemPromoCode::UNUSABLE],
            $messages
        );
    }

    public function test_redeeming_is_throttled_after_repeated_wrong_codes(): void
    {
        $user = $this->gameMaster();

        // One over the per-account allowance of 10 in 10 minutes.
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $this->actingAs($user)
                ->post(route('promo.redeem.store'), ['code' => "GUESS{$attempt}"])
                ->assertSessionHasErrors('code');
        }

        $this->actingAs($user)
            ->post(route('promo.redeem.store'), ['code' => 'GUESS-ONE-TOO-MANY'])
            ->assertStatus(429);
    }

    /**
     * The reason this is a named limiter and not a plain `throttle:10,10`:
     * the checkout review is the ordinary path to paying, walked by every
     * buyer with no code at all. Charging them an attempt would rate-limit
     * people for trying to give us money.
     */
    public function test_walking_the_checkout_review_without_a_code_never_spends_an_attempt(): void
    {
        config(['platform.payments.enabled' => true]);

        $user = $this->userWithoutAccess();
        $this->catalogCase('steve-jacobs');

        for ($visit = 0; $visit < 25; $visit++) {
            $this->actingAs($user)
                ->get(route('cases.checkout.review', 'steve-jacobs'))
                ->assertOk();
        }
    }

    /* -----------------------------------------------------------------
     | 3. Response headers
     |---------------------------------------------------------------- */

    public function test_every_response_carries_the_baseline_security_headers(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));

        // The one that matters most here: a player's access token lives in
        // the URL, so a permissive referrer policy would leak that token —
        // the player's entire credential — to any external site linked from
        // an inbox page.
        $this->assertSame('same-origin', $response->headers->get('Referrer-Policy'));
    }

    public function test_the_content_security_policy_starts_in_report_only_mode(): void
    {
        config(['platform.csp.enforce' => false]);

        $response = $this->get(route('home'));

        // Report-only cannot break a page, which is what makes it safe to
        // ship to a live site before anyone has walked it in a browser.
        $this->assertNotNull($response->headers->get('Content-Security-Policy-Report-Only'));
        $this->assertNull($response->headers->get('Content-Security-Policy'));
    }

    public function test_the_content_security_policy_can_be_switched_to_enforcing(): void
    {
        config(['platform.csp.enforce' => true]);

        $policy = $this->get(route('home'))->headers->get('Content-Security-Policy');

        $this->assertNotNull($policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        // Turnstile has to be reachable or the captcha never renders.
        $this->assertStringContainsString('https://challenges.cloudflare.com', $policy);
    }

    /**
     * A nonce, not 'unsafe-inline'. The app's own bootstrap (Vite's tags and
     * Ziggy's route list) is inline, so without a nonce the only policy that
     * would not break the page is one permissive enough to be worthless.
     */
    public function test_the_script_policy_uses_a_nonce_rather_than_allowing_inline_scripts(): void
    {
        config(['platform.csp.enforce' => true]);

        $policy = $this->get(route('home'))->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression("/script-src[^;]*'nonce-[A-Za-z0-9]+'/", $policy);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $policy);
    }

    /**
     * The one that would actually take the site down if it were wrong.
     *
     * The policy's nonce is worthless unless the page's own inline scripts
     * carry it: Ziggy's route list and Vite's tags are both inline, so if
     * either is emitted without the attribute, switching CSP to enforcing
     * turns every page blank. Asserting the header alone would pass while
     * that was broken — this reads the rendered HTML instead.
     */
    public function test_the_pages_own_inline_scripts_carry_the_nonce_from_the_header(): void
    {
        config(['platform.csp.enforce' => true]);

        $response = $this->get(route('home'))->assertOk();

        preg_match("/'nonce-([A-Za-z0-9]+)'/", $response->headers->get('Content-Security-Policy'), $matches);
        $nonce = $matches[1] ?? null;

        $this->assertNotNull($nonce, 'La politica no trae nonce.');

        $html = $response->getContent();

        // Ziggy writes the route list into an inline <script>.
        $this->assertStringContainsString('Ziggy', $html);

        // Every <script> in the shell must carry this response's nonce. A
        // count is what catches a new inline script being added later
        // without one.
        $scriptTags = preg_match_all('/<script\b/', $html);
        $noncedTags = preg_match_all('/<script\b[^>]*nonce="'.preg_quote($nonce, '/').'"/', $html);

        $this->assertGreaterThan(0, $scriptTags);
        $this->assertSame(
            $scriptTags,
            $noncedTags,
            'Hay un <script> inline sin el nonce: activar el CSP dejaria la pagina en blanco.'
        );
    }

    public function test_the_payment_provider_is_the_bold_implementation(): void
    {
        // Guards the binding the first three tests reach through: if this
        // ever resolved to something else, they would be verifying a class
        // the application does not use.
        $this->assertInstanceOf(BoldPaymentProvider::class, app(PaymentProvider::class));
    }
}
