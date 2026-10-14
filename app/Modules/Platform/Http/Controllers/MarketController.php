<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Support\Market;
use App\Modules\Platform\Support\MarketResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * The footer selector: lets a visitor correct the price list they were put on
 * (a VPN, a trip, a Colombian living abroad). Stored in a first-party
 * preference cookie, encrypted like the rest — the server both writes and
 * reads it.
 */
class MarketController extends Controller
{
    public function update(Request $request, MarketResolver $resolver): RedirectResponse
    {
        $market = Market::normalize($request->input('market'));

        abort_if($market === null, 422);

        Cookie::queue(MarketResolver::COOKIE, $market, 60 * 24 * 180);
        $resolver->pin($request, $market);

        return back();
    }
}
