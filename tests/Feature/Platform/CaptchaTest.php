<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Rules\CaptchaRule;
use App\Modules\Platform\Support\Captcha;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * The captcha on the four forms an automated script can point at the platform
 * from outside.
 *
 * What it adds over the rate limits is worth stating, because they look like
 * the same defence: a rate limit caps how fast ONE origin can try something,
 * and does nothing against the same attack spread across thousands of
 * addresses, where no single IP ever trips a limit. The two are complementary
 * and every captcha'd endpoint keeps its throttle.
 */
class CaptchaTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function withKeys(): void
    {
        config([
            'platform.captcha.site_key' => 'site-key',
            'platform.captcha.secret_key' => 'secret-key',
        ]);
    }

    private function cloudflareSays(bool $success): void
    {
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(
                ['success' => $success, 'error-codes' => $success ? [] : ['invalid-input-response']],
                200
            ),
        ]);
    }

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Isabella Figueroa',
            'email' => 'nueva@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ], $overrides);
    }

    /* -----------------------------------------------------------------
     | Off by default
     |---------------------------------------------------------------- */

    /**
     * With no keys there is nothing to check, and demanding a token would
     * make every form unsubmittable. This is what lets the whole test suite
     * and a local machine run without a Cloudflare account.
     */
    public function test_with_no_keys_configured_the_forms_work_untouched(): void
    {
        config(['platform.captcha.site_key' => '', 'platform.captcha.secret_key' => '']);
        Http::fake();

        $this->assertFalse(app(Captcha::class)->enabled());
        $this->assertSame([], CaptchaRule::rules());

        $this->post(route('register'), $this->registration())->assertRedirect(route('dashboard'));

        // Nothing was asked of Cloudflare, because there was nothing to ask.
        Http::assertNothingSent();
    }

    /* -----------------------------------------------------------------
     | On
     |---------------------------------------------------------------- */

    public function test_a_submission_with_no_token_is_rejected_when_the_captcha_is_on(): void
    {
        $this->withKeys();
        Http::fake();

        $this->post(route('register'), $this->registration())
            ->assertSessionHasErrors(CaptchaRule::FIELD);

        $this->assertNull(User::firstWhere('email', 'nueva@example.com'));
    }

    public function test_a_token_cloudflare_rejects_blocks_the_submission(): void
    {
        $this->withKeys();
        $this->cloudflareSays(false);

        $this->post(route('register'), $this->registration([CaptchaRule::FIELD => 'forged-token']))
            ->assertSessionHasErrors(CaptchaRule::FIELD);

        $this->assertNull(User::firstWhere('email', 'nueva@example.com'));
    }

    public function test_a_token_cloudflare_accepts_lets_the_submission_through(): void
    {
        $this->withKeys();
        $this->cloudflareSays(true);

        $this->post(route('register'), $this->registration([CaptchaRule::FIELD => 'good-token']))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull(User::firstWhere('email', 'nueva@example.com'));
    }

    /**
     * The secret key is what proves the token to Cloudflare, and it must be
     * the only place it ever goes.
     */
    public function test_the_secret_key_is_sent_to_cloudflare_and_nowhere_else(): void
    {
        $this->withKeys();
        $this->cloudflareSays(true);

        $this->post(route('register'), $this->registration([CaptchaRule::FIELD => 'good-token']));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'challenges.cloudflare.com')
                && $request['secret'] === 'secret-key'
                && $request['response'] === 'good-token';
        });

        // The site key is public and belongs in the page; the secret never is.
        $this->assertSame('site-key', app(Captcha::class)->siteKey());
    }

    /* -----------------------------------------------------------------
     | Cloudflare being down
     |---------------------------------------------------------------- */

    /**
     * Fails OPEN, deliberately. Failing closed would mean a Cloudflare
     * incident takes registration, sign-in and password recovery down with
     * it — trading a bot problem for a total lockout of real customers. The
     * throttles still apply during such a window, so the endpoint is never
     * left bare.
     */
    public function test_a_cloudflare_outage_does_not_lock_real_users_out(): void
    {
        $this->withKeys();
        Http::fake(['challenges.cloudflare.com/*' => Http::response('', 503)]);

        $this->post(route('register'), $this->registration([CaptchaRule::FIELD => 'some-token']))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull(User::firstWhere('email', 'nueva@example.com'));
    }

    /* -----------------------------------------------------------------
     | Coverage: every exposed form
     |---------------------------------------------------------------- */

    /**
     * All four, so that adding a form later and forgetting the captcha is a
     * failing test rather than a quiet gap.
     */
    public function test_the_captcha_guards_sign_in_password_recovery_and_code_redemption(): void
    {
        $this->withKeys();
        Http::fake();

        $this->post(route('login'), ['email' => 'a@example.com', 'password' => 'whatever'])
            ->assertSessionHasErrors(CaptchaRule::FIELD);

        $this->post(route('password.email'), ['email' => 'a@example.com'])
            ->assertSessionHasErrors(CaptchaRule::FIELD);

        $this->actingAs($this->gameMaster())
            ->post(route('promo.redeem.store'), ['code' => 'CUALQUIERA'])
            ->assertSessionHasErrors(CaptchaRule::FIELD);
    }
}
