<?php

namespace App\Http\Middleware;

use App\Modules\Platform\Support\Attribution;
use Closure;
use Illuminate\Http\Request;

/**
 * Remembers which campaign a visitor came from.
 *
 * Runs on every web request but only acts on a GET carrying a tracked
 * parameter or a promo code, so almost every request passes straight through.
 * Must sit after StartSession: everything it keeps lives in the session. See
 * Attribution for why it exists at all.
 */
class CaptureAttribution
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('GET') && $request->query->count() > 0) {
            Attribution::capture($request);
        }

        return $next($request);
    }
}
