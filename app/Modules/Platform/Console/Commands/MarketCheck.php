<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Support\Geo\CountryLookup;
use App\Modules\Platform\Support\Market;
use App\Modules\Platform\Support\MarketResolver;
use Illuminate\Console\Command;

/**
 * Answers "what would a visitor from this IP be shown?" without having to be
 * there: the country the database finds, the market that makes them, and the
 * state of everything that decides it (flag, database file, credentials).
 *
 * Read-only. The visitor's real IP still has to be checked once on the real
 * server (the Order's client_ip), because this only tests the lookup.
 */
class MarketCheck extends Command
{
    protected $signature = 'platform:market-check
                            {ip?* : One or more IP addresses to look up (e.g. 8.8.8.8 and your own)}';

    protected $description = 'Show the country and market (COP / USD) a visitor IP would get, and the state of the setup';

    public function handle(CountryLookup $countries): int
    {
        $database = (string) config('platform.market.geoip.database');
        $enabled = (bool) config('platform.market.international_enabled');

        $this->line('Mercado internacional: '.($enabled ? 'ENCENDIDO' : 'apagado (todos ven COP)'));

        if (is_file($database)) {
            $days = (int) floor((time() - filemtime($database)) / 86400);
            $this->line("Base de paises: {$database}");
            $this->line('  actualizada: '.date('Y-m-d H:i', filemtime($database))." (hace {$days} dias)".($days > 20 ? '  <-- vieja, revisa la actualizacion' : ''));
        } else {
            $this->error("Base de paises: NO existe ({$database}). Todos se tratan como Colombia.");
        }

        $this->line('Credenciales MaxMind: '.(config('platform.market.geoip.license_key') ? 'puestas' : 'sin llave (no se actualiza sola)'));
        $this->line('Unidad USD en Bold: '.(config('platform.payments.usd_amount_unit') ?: 'SIN DEFINIR (las ordenes en USD se rechazan)'));

        $ips = (array) $this->argument('ip');

        if ($ips === []) {
            $this->newLine();
            $this->comment('Pasa una IP para probar: php artisan platform:market-check 8.8.8.8');

            return self::SUCCESS;
        }

        $this->newLine();

        $rows = [];

        foreach ($ips as $ip) {
            $country = $countries->countryFor($ip);

            // Same decision MarketResolver makes, minus the cookie and session.
            $market = $country === null || $country === MarketResolver::HOME_COUNTRY ? Market::CO : Market::INTL;

            $rows[] = [
                $ip,
                $country ?? 'desconocido',
                $market === Market::INTL ? 'Internacional (USD)' : 'Colombia (COP)',
                $market === Market::INTL && ! $enabled ? 'veria COP: el flag esta apagado' : '',
            ];
        }

        $this->table(['IP', 'Pais', 'Mercado', 'Nota'], $rows);

        return self::SUCCESS;
    }
}
