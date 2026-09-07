<?php

namespace App\Modules\Immersion\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGameMaster
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->get('immersion_gm_ok')) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json([
                    'message' => 'Sesion expirada.',
                    'redirect' => route('immersion.gm.login'),
                ], 401);
            }

            return redirect()->route('immersion.gm.login');
        }

        return $next($request);
    }
}
