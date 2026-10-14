<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Support\Geo\CountryLookup;
use Tests\TestCase;

class MarketCheckTest extends TestCase
{
    private function lookup(array $map): void
    {
        $this->app->instance(CountryLookup::class, new class($map) implements CountryLookup {
            public function __construct(private array $map)
            {
            }

            public function countryFor(?string $ip): ?string
            {
                return $this->map[$ip] ?? null;
            }
        });
    }

    public function test_it_says_which_market_each_ip_gets(): void
    {
        config(['platform.market.international_enabled' => true]);
        $this->lookup(['8.8.8.8' => 'US', '190.1.1.1' => 'CO']);

        $this->artisan('platform:market-check', ['ip' => ['8.8.8.8', '190.1.1.1', '10.0.0.1']])
            ->expectsOutputToContain('ENCENDIDO')
            ->expectsTable(['IP', 'Pais', 'Mercado', 'Nota'], [
                ['8.8.8.8', 'US', 'Internacional (USD)', ''],
                ['190.1.1.1', 'CO', 'Colombia (COP)', ''],
                ['10.0.0.1', 'desconocido', 'Colombia (COP)', ''],
            ])
            ->assertExitCode(0);
    }

    public function test_it_warns_that_an_international_ip_still_sees_cop_while_the_flag_is_off(): void
    {
        config(['platform.market.international_enabled' => false]);
        $this->lookup(['8.8.8.8' => 'US']);

        $this->artisan('platform:market-check', ['ip' => ['8.8.8.8']])
            ->expectsOutputToContain('apagado')
            ->expectsTable(['IP', 'Pais', 'Mercado', 'Nota'], [
                ['8.8.8.8', 'US', 'Internacional (USD)', 'veria COP: el flag esta apagado'],
            ])
            ->assertExitCode(0);
    }

    public function test_it_reports_a_missing_database(): void
    {
        config(['platform.market.geoip.database' => storage_path('framework/testing/no-existe.mmdb')]);

        $this->artisan('platform:market-check')->expectsOutputToContain('NO existe')->assertExitCode(0);
    }
}
