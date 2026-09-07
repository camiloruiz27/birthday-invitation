<?php

namespace App\Modules\Platform\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Platform administrator only.
 *
 * Answers 404 to everyone else — signed in or not, and rather than 403 or a
 * login redirect. Neither an ordinary account nor a stranger has any business
 * learning that an admin area exists at this URL, and "not authenticated" is
 * just one more way of not being an administrator.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->is_admin, 404);

        return $next($request);
    }
}
