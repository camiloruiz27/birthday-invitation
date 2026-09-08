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
    ],

];
