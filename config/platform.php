<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Simulated checkout
    |--------------------------------------------------------------------------
    |
    | There is no payment provider yet. While this is on, a signed-in user can
    | put a case in their library from its catalog page without paying, so the
    | whole funnel is walkable end to end.
    |
    | Off in production by default: a deployed site must not give cases away.
    | It takes no payment details and writes an ordinary entitlement marked
    | `grant`, so simulated access is always distinguishable from a purchase.
    |
    */

    'simulated_checkout' => (bool) env(
        'PLATFORM_SIMULATED_CHECKOUT',
        env('APP_ENV', 'production') !== 'production'
    ),

    /*
    |--------------------------------------------------------------------------
    | AI credit packages
    |--------------------------------------------------------------------------
    |
    | What a Game Master can buy when they run out. Selling credits is commerce
    | and lives here; what a credit BUYS is a property of the engine and lives
    | in config/immersion.php.
    |
    | While simulated_checkout is on these are handed over without payment, the
    | same way cases are, so the whole top-up flow is walkable before there is a
    | payment provider.
    |
    | PLACEHOLDER PRICING: amounts are in whole pesos (COP has no practical
    | cents) and are reference figures, to be set before launch alongside the
    | case price.
    |
    */

    'credit_packages' => [
        [
            'id' => 'starter',
            'name' => 'Recarga corta',
            'credits' => 60,
            'price_amount' => 39000,
            'currency' => 'COP',
            'summary' => 'Una partida completa: interrogatorio a fondo y un final avanzado.',
        ],
        [
            'id' => 'standard',
            'name' => 'Recarga estandar',
            'credits' => 150,
            'price_amount' => 89000,
            'currency' => 'COP',
            'summary' => 'Dos o tres mesas, segun cuanto interroguen.',
            'highlight' => true,
        ],
        [
            'id' => 'club',
            'name' => 'Recarga de club',
            'credits' => 400,
            'price_amount' => 199000,
            'currency' => 'COP',
            'summary' => 'Para quien juega seguido o dirige varias mesas al mes.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Real payments (Bold)
    |--------------------------------------------------------------------------
    |
    | Independent of simulated_checkout on purpose, the same way an AI
    | capability is switched off without touching the rest of the game
    | (see immersion.ai.*_enabled). Both can be true in a dev environment that
    | happens to have sandbox keys; only this flag decides whether a Game
    | Master is offered a real card payment.
    |
    | Bold's Payment Link API is used rather than an embedded card form: the
    | buyer is redirected to a page Bold hosts, so card data never reaches
    | this server and PCI scope stays with Bold.
    |
    */

    'payments' => [
        'enabled' => (bool) env('PLATFORM_PAYMENTS_ENABLED', false),
        'base_url' => env('BOLD_API_BASE_URL', 'https://integrations.api.bold.co'),
        'identity_key' => env('BOLD_IDENTITY_KEY', ''),
        'secret_key' => env('BOLD_SECRET_KEY', ''),
        'timeout' => (int) env('BOLD_TIMEOUT', 15),

        // An order left "pending" (checkout started, never finished) longer
        // than this is data to clean up, not a customer to chase — it never
        // granted anything, since only an approved webhook does that.
        'stale_order_hours' => (int) env('PLATFORM_STALE_ORDER_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Search engine indexing
    |--------------------------------------------------------------------------
    |
    | The public pages ship with noindex until the site is actually launched,
    | so an unfinished catalog with placeholder prices does not get indexed.
    |
    */

    'indexable' => (bool) env('PLATFORM_INDEXABLE', false),

    /*
    |--------------------------------------------------------------------------
    | Rate limits
    |--------------------------------------------------------------------------
    |
    | "attempts,minutes" pairs for Laravel's throttle middleware. Every one of
    | these endpoints is either a credential-guessing surface or a way to make
    | the app send mail to an address of the attacker's choosing.
    |
    */

    'rate_limits' => [
        'login' => env('PLATFORM_THROTTLE_LOGIN', '5,1'),
        'register' => env('PLATFORM_THROTTLE_REGISTER', '5,10'),
        'password_email' => env('PLATFORM_THROTTLE_PASSWORD_EMAIL', '3,10'),
        'password_reset' => env('PLATFORM_THROTTLE_PASSWORD_RESET', '5,10'),

        // Confirming an address, and asking for the mail again. The second is
        // the one that matters: without it the resend button is a way to make
        // our SMTP server send mail on demand.
        'verify_email' => env('PLATFORM_THROTTLE_VERIFY_EMAIL', '6,1'),
        'verify_email_resend' => env('PLATFORM_THROTTLE_VERIFY_RESEND', '3,10'),

        // Guessing promo codes. Consumed only by requests that actually carry
        // a code (see PlatformServiceProvider::registerPromoRateLimiter), so
        // an ordinary trip through the checkout review costs nothing.
        //
        // Per account first, then per IP so that making new accounts does not
        // hand out a fresh allowance each time.
        'promo' => env('PLATFORM_THROTTLE_PROMO', '10,10'),
        'promo_ip' => env('PLATFORM_THROTTLE_PROMO_IP', '30,10'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Captcha (Cloudflare Turnstile)
    |--------------------------------------------------------------------------
    |
    | Guards the endpoints an automated script can point at the platform from
    | outside: creating accounts, guessing passwords, making us send mail, and
    | guessing promo codes. Rate limits already cap how fast ONE origin can
    | try; this is what stops a botnet spreading the same attack across
    | thousands of addresses, where a per-IP limit never trips.
    |
    | Off unless BOTH keys are present, which is what keeps local development
    | and the test suite running with no Cloudflare account: the validation
    | rule passes and the widget renders nothing. That means a production
    | deploy without these keys silently has no captcha — deliberate, because
    | the alternative is a missing key locking every user out of registering.
    | The rate limits stand on their own either way.
    |
    | Keys: https://dash.cloudflare.com → Turnstile → add the site. The site
    | key is public (it ships in the page); the secret key never leaves here.
    |
    */

    'captcha' => [
        'site_key' => env('TURNSTILE_SITE_KEY', ''),
        'secret_key' => env('TURNSTILE_SECRET_KEY', ''),
        'verify_url' => env(
            'TURNSTILE_VERIFY_URL',
            'https://challenges.cloudflare.com/turnstile/v0/siteverify'
        ),
        'timeout' => (int) env('TURNSTILE_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security headers
    |--------------------------------------------------------------------------
    |
    | See App\Http\Middleware\SecurityHeaders. Everything except the Content
    | Security Policy is unconditional — those headers cannot break a page.
    |
    | The CSP can, so it ships in report-only mode: browsers report what it
    | WOULD have blocked (visible in the console) without blocking anything.
    | Turn PLATFORM_CSP_ENFORCE on once a walk through checkout, the console
    | and a player inbox reports nothing.
    |
    */

    'csp' => [
        'enforce' => (bool) env('PLATFORM_CSP_ENFORCE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cron diagnostic email
    |--------------------------------------------------------------------------
    |
    | A manual switch, off by default on purpose. When on, `SendCronDiagnosticMail`
    | is scheduled every 7 minutes (see PlatformServiceProvider) and mails a
    | branded round-trip test to `email` below — proof the scheduler, the
    | queue and SMTP all work, and a look at the platform's own email design.
    |
    | Turn it off the moment it has done its job: PLATFORM_CRON_DIAGNOSTIC_ENABLED=false
    | in the server's .env. Left on, it is one email every 7 minutes, forever.
    |
    */

    'cron_diagnostic' => [
        'enabled' => (bool) env('PLATFORM_CRON_DIAGNOSTIC_ENABLED', false),
        'email' => env('PLATFORM_CRON_DIAGNOSTIC_EMAIL', 'camiruiza27@gmail.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP cron trigger (fallback)
    |--------------------------------------------------------------------------
    |
    | Lets an external ping service run `schedule:run` over HTTP, for a host
    | whose own cron daemon silently never fires the job its panel shows
    | configured (see app/Modules/Immersion/README.md, "Cron en produccion").
    |
    | An empty secret makes the endpoint always refuse: set
    | PLATFORM_CRON_HTTP_SECRET only on the server that actually needs this
    | fallback, never in a shared or example .env. 
    |
    */

    'cron_http_trigger' => [
        'secret' => env('PLATFORM_CRON_HTTP_SECRET'),
    ],

];
