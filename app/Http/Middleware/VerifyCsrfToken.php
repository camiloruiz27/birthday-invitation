<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Bold's servers post here, not a browser with our CSRF cookie.
        // Authenticated by BoldWebhookController's signature check instead.
        'webhooks/bold',
    ];
}
