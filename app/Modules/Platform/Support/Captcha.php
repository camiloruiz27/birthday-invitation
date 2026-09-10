<?php

namespace App\Modules\Platform\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cloudflare Turnstile: the single place a captcha token is checked.
 *
 * What it is FOR is worth being precise about, because a captcha and a rate
 * limit look like the same defence and are not. A rate limit caps how fast
 * one origin can try something; it does nothing against a botnet spreading
 * the same 100.000 attempts across 100.000 addresses, where no single IP ever
 * trips a limit. That is the attack this stops — mass account creation,
 * distributed password guessing, and using the sign-up and password-reset
 * forms to make our SMTP server send mail on demand.
 *
 * The two work together and neither replaces the other, so every endpoint
 * that gets a captcha keeps its throttle.
 */
class Captcha
{
    /**
     * With no keys configured there is nothing to check, so validation
     * passes and the widget renders nothing.
     *
     * This is what lets the test suite and a local machine run with no
     * Cloudflare account. The cost is that a production deploy missing its
     * keys quietly has no captcha — chosen deliberately over the alternative,
     * where one absent variable locks every real user out of registering. The
     * rate limits are unaffected either way, so the endpoints are never bare.
     */
    public function enabled(): bool
    {
        return $this->siteKey() !== '' && $this->secretKey() !== '';
    }

    /**
     * Public by design: it is rendered into the page for the widget to use.
     */
    public function siteKey(): string
    {
        return (string) config('platform.captcha.site_key');
    }

    /**
     * Verifies one token against Cloudflare, spending it.
     *
     * A token is single-use and short-lived, so this must be called exactly
     * once per submission — which is why it lives behind a validation rule
     * and nowhere else.
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (! is_string($token) || $token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout((int) config('platform.captcha.timeout', 10))
                ->post((string) config('platform.captcha.verify_url'), array_filter([
                    'secret' => $this->secretKey(),
                    'response' => $token,
                    // Lets Cloudflare score the challenge against the address
                    // that actually solved it. Optional, and omitted rather
                    // than sent empty when we cannot determine it.
                    'remoteip' => $ip,
                ]));

            if (! $response->successful()) {
                return $this->failOpen('captcha_verify_http_error', ['status' => $response->status()]);
            }

            if ($response->json('success') === true) {
                return true;
            }

            // A genuine rejection: the token was forged, replayed, expired,
            // or simply absent. Logged at warning because a burst of these is
            // what an attack looks like from here.
            Log::warning('captcha_rejected', [
                'error_codes' => $response->json('error-codes'),
            ]);

            return false;
        } catch (\Throwable $exception) {
            return $this->failOpen('captcha_verify_exception', ['message' => $exception->getMessage()]);
        }
    }

    /**
     * Cloudflare being unreachable is OUR outage, not the visitor's.
     *
     * Failing closed here would mean a Turnstile incident takes registration,
     * sign-in and password recovery down with it — trading a bot problem for
     * a total lockout of real customers. The throttles are still in force
     * during such a window, so the endpoints keep a defence; it is logged at
     * error level precisely because it is a hole that should be noticed and
     * should not last.
     */
    private function failOpen(string $event, array $context): bool
    {
        Log::error($event, $context);

        return true;
    }

    private function secretKey(): string
    {
        return (string) config('platform.captcha.secret_key');
    }
}
