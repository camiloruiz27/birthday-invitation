<?php

namespace App\Http\Middleware;

use App\Modules\Platform\Support\ConnectionReuseProbe;
use Closure;
use Illuminate\Http\Request;

/**
 * Samples, after the response has gone out, whether the request reused a
 * persistent database connection (see ConnectionReuseProbe). Does nothing unless
 * DB_PERSISTENT is on, and then only on a small share of requests.
 */
class SampleConnectionReuse
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        ConnectionReuseProbe::sample();
    }
}
