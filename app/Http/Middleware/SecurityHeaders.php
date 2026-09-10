<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * The response headers that tell a browser what this site is allowed to do.
 *
 * Split into two halves on purpose. The first four headers cannot break a
 * page, so they are unconditional. The Content Security Policy can break one
 * badly — a single missed source and the app renders blank — so it ships in
 * report-only mode and is switched to enforcing from config once a walk
 * through the site reports nothing. See config/platform.php ('csp').
 *
 * Runs first in the `web` group because the nonce has to exist before the
 * view renders, not after.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // One nonce per response, handed to the two places that emit an
        // inline <script>: Vite's tags and Ziggy's route list. Without it a
        // script-src worth having would block the app's own bootstrap.
        $nonce = Str::random(32);

        Vite::useCspNonce($nonce);
        View::share('cspNonce', $nonce);

        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Nothing here is ever meant to be embedded elsewhere. Belt and
        // braces with frame-ancestors below, which is the modern equivalent
        // but is not honoured by older browsers.
        $response->headers->set('X-Frame-Options', 'DENY');

        // The one that matters most for this app specifically: a player's
        // access token is IN the URL, so without this any outbound link from
        // an inbox page would hand that token to the destination site in the
        // Referer header — and that token is the player's whole credential.
        $response->headers->set('Referrer-Policy', 'same-origin');

        // No feature here needs a camera, a microphone or a location.
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );

        // Only over a connection that is already secure: sending HSTS over
        // plain http is meaningless, and on a local http:// machine it would
        // pin the browser to https for a host that does not serve it.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        $response->headers->set(
            config('platform.csp.enforce')
                ? 'Content-Security-Policy'
                : 'Content-Security-Policy-Report-Only',
            $this->contentSecurityPolicy($nonce)
        );

        return $response;
    }

    private function contentSecurityPolicy(string $nonce): string
    {
        // Cloudflare serves the Turnstile script and renders the challenge
        // inside its own iframe, so it needs both script-src and frame-src.
        $turnstile = 'https://challenges.cloudflare.com';

        $script = ["'self'", "'nonce-{$nonce}'", $turnstile];
        $connect = ["'self'"];

        // The Vite dev server hands modules over its own origin and pushes
        // hot updates over a websocket. Neither exists in a built deploy, so
        // they are only admitted where they actually run.
        if (Vite::isRunningHot()) {
            $script[] = 'http://localhost:5173';
            $connect[] = 'http://localhost:5173';
            $connect[] = 'ws://localhost:5173';
        }

        $policy = [
            "default-src 'self'",
            'script-src '.implode(' ', $script),

            // 'unsafe-inline' is unavoidable for styles: React writes inline
            // style attributes, which a nonce cannot cover. It is also far
            // less dangerous than for scripts — an injected style cannot run
            // code, and script-src stays strict.
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",

            // data: for the gallery's inline assets; blob: for audio the
            // browser builds locally.
            "img-src 'self' data: blob:",
            "media-src 'self' blob:",

            'connect-src '.implode(' ', $connect),
            "frame-src {$turnstile}",

            // Nobody may frame us — the clickjacking defence for the
            // checkout and console screens.
            "frame-ancestors 'none'",

            // Stops an injected <base> from silently repointing every
            // relative URL on the page.
            "base-uri 'self'",

            // No Flash, no applets, nothing to embed.
            "object-src 'none'",

            // Where a form on this site may post. Bold's checkout is reached
            // by navigating away, not by posting a form, but it is listed so
            // that turning this on cannot break a payment.
            "form-action 'self' https://checkout.bold.co",
        ];

        return implode('; ', $policy);
    }
}
