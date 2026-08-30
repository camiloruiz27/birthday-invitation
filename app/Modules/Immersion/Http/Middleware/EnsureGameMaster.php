<?php

namespace App\Modules\Immersion\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGameMaster
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->get('immersion_gm_ok')) {
            return redirect()->route('immersion.gm.login');
        }

        return $next($request);
    }
}
