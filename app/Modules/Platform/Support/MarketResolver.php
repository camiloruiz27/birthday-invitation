<?php

namespace App\Modules\Platform\Support;

use App\Modules\Platform\Support\Geo\CountryLookup;
use Illuminate\Http\Request;

/**
 * Decides which market a request belongs to.
 *
 * In order: the visitor's own choice (the `mc_market` cookie, set by the
 * footer selector), the market already pinned to this session, then the
 * country of their IP. Anything unknown is Colombia.
 *
 * The result is pinned to the session so the price on the checkout review is
 * the price the purchase is made at, even if the IP lookup were to answer
 * differently a minute later (a mobile network changing exit node).
 */
class MarketResolver
{
    public const COOKIE = 'mc_market';

    public const SESSION_KEY = 'market.pinned';

    public const HOME_COUNTRY = 'CO';

    public function __construct(private CountryLookup $countries)
    {
    }

    public function resolve(Request $request): string
    {
        // Shipped dark: until the flag is on, everyone is in the Colombian market.
        if (! config('platform.market.international_enabled')) {
            return Market::CO;
        }

        // Testing aids. Never honoured in production, or a query string would
        // be a way to pick the cheaper price list.
        if (! app()->environment('production')) {
            $forced = Market::normalize(config('platform.market.force'))
                ?? Market::normalize($request->query('market'));

            if ($forced) {
                return $forced;
            }
        }

        $chosen = Market::normalize($request->cookie(self::COOKIE));

        if ($chosen) {
            $this->pin($request, $chosen);

            return $chosen;
        }

        $pinned = $request->hasSession()
            ? Market::normalize($request->session()->get(self::SESSION_KEY))
            : null;

        if ($pinned) {
            return $pinned;
        }

        $country = $this->countries->countryFor($request->ip());

        $market = $country === null || $country === self::HOME_COUNTRY ? Market::CO : Market::INTL;

        // An unknown country is not worth remembering: a later request may
        // resolve it properly.
        if ($country !== null) {
            $this->pin($request, $market);
        }

        return $market;
    }

    public function pin(Request $request, string $market): void
    {
        if ($request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, $market);
        }
    }
}
