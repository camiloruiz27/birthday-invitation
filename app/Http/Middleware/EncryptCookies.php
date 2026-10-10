<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Written by the browser, not by us (the consent banner and the ad
        // pixels), so they arrive in plain text and must be read as such —
        // an encrypted-cookie middleware silently drops anything it cannot
        // decrypt. Read server-side only to attach a conversion to the ad
        // click that caused it, and only with marketing consent.
        'mc_consent',
        '_fbp',
        '_fbc',
        '_ttp',
    ];
}
