<?php

namespace App\Modules\Platform\Support\Geo;

use MaxMind\Db\Reader;
use Throwable;

/**
 * Country lookup against a local MaxMind GeoLite2-Country database file.
 *
 * Local on purpose: no request leaves the server and the visitor's IP is not
 * sent to a third party. Any failure — file missing (it is not in git; see
 * platform:geoip-update), corrupt, unreadable — answers null, which makes the
 * visitor Colombian: a broken lookup must never raise a price.
 */
class MaxMindCountryLookup implements CountryLookup
{
    private ?Reader $reader = null;

    public function __construct(private string $databasePath)
    {
    }

    public function countryFor(?string $ip): ?string
    {
        // Private, loopback and reserved ranges have no country by definition.
        if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        try {
            $reader = $this->reader();
            $record = $reader?->get($ip);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        $code = $record['country']['iso_code'] ?? $record['registered_country']['iso_code'] ?? null;

        return is_string($code) && $code !== '' ? strtoupper($code) : null;
    }

    private function reader(): ?Reader
    {
        if ($this->reader === null && is_file($this->databasePath)) {
            $this->reader = new Reader($this->databasePath);
        }

        return $this->reader;
    }
}
