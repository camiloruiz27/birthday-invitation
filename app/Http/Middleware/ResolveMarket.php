<?php

namespace App\Http\Middleware;

use App\Modules\Platform\Support\Market;
use App\Modules\Platform\Support\MarketResolver;
use Closure;
use Illuminate\Http\Request;

/**
 * Parks the visitor's market on the request, once, so prices everywhere
 * (pages, checkout, ad events) read the same answer. Runs after the session
 * starts: the resolved market is pinned there.
 */
class ResolveMarket
{
    public function __construct(private MarketResolver $resolver)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $request->attributes->set(Market::ATTRIBUTE, $this->resolver->resolve($request));

        return $next($request);
    }
}
