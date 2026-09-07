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
