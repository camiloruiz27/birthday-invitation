<?php

namespace App\Modules\Platform\Support\Geo;

/**
 * Which country an IP address is in, as an ISO 3166-1 alpha-2 code ("CO",
 * "US"), or null when it cannot be told. Null is a normal answer — a private
 * address, an unlisted range, a missing database — and callers treat it as
 * "not known", never as "foreign".
 */
interface CountryLookup
{
    public function countryFor(?string $ip): ?string;
}
