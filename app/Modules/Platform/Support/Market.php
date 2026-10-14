<?php

namespace App\Modules\Platform\Support;

use Illuminate\Http\Request;

/**
 * Which price list a visitor is shown: Colombia (COP) or international (USD).
 *
 * The value is decided once per request by the ResolveMarket middleware and
 * parked on the request; everything else only reads it. Anything that cannot
 * tell — a unit test, a console command, the middleware not having run — is
 * Colombia, the market the platform has always sold in.
 */
class Market
{
    public const CO = 'CO';

    public const INTL = 'INTL';

    public const ATTRIBUTE = 'market';

    public static function currency(string $market): string
    {
        return $market === self::INTL ? 'USD' : 'COP';
    }

    public static function normalize(?string $value): ?string
    {
        $value = strtoupper(trim((string) $value));

        return in_array($value, [self::CO, self::INTL], true) ? $value : null;
    }

    public static function fromRequest(Request $request): string
    {
        $market = self::normalize($request->attributes->get(self::ATTRIBUTE));

        // With the flag off nobody is international, whatever was resolved.
        if ($market === self::INTL && ! config('platform.market.international_enabled')) {
            return self::CO;
        }

        return $market ?? self::CO;
    }
}
