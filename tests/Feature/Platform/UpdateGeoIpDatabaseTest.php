<?php

namespace Tests\Feature\Platform;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use PharData;
use Tests\TestCase;

/**
 * platform:geoip-update: downloads MaxMind's GeoLite2-Country and keeps it
 * fresh without anyone remembering to.
 */
class UpdateGeoIpDatabaseTest extends TestCase
{
    private string $dir;

    private string $target;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = storage_path('framework/testing/geoip-'.bin2hex(random_bytes(3)));
        File::ensureDirectoryExists($this->dir);
        $this->target = $this->dir.'/GeoLite2-Country.mmdb';

        config([
            'platform.market.geoip.database' => $this->target,
            'platform.market.geoip.account_id' => '123456',
            'platform.market.geoip.license_key' => 'secret-key',
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    /** A tar.gz shaped like MaxMind's: a dated folder holding the .mmdb. */
    private function archiveWith(string $content): string
    {
        $build = storage_path('framework/testing/build-'.bin2hex(random_bytes(3)));
        File::ensureDirectoryExists($build);

        $phar = new PharData($build.'/db.tar');
        $phar->addFromString('GeoLite2-Country_20261013/GeoLite2-Country.mmdb', $content);
        $phar->addFromString('GeoLite2-Country_20261013/COPYRIGHT.txt', 'x');
        $phar->compress(\Phar::GZ);

        $bytes = file_get_contents($build.'/db.tar.gz');
        unset($phar);
        File::deleteDirectory($build);

        return $bytes;
    }

    public function test_it_downloads_with_the_account_credentials_and_installs_the_database(): void
    {
        Http::fake(['download.maxmind.com/*' => Http::response($this->archiveWith('FAKE-MMDB-BYTES'))]);

        $this->artisan('platform:geoip-update')->expectsOutputToContain('Base actualizada')->assertExitCode(0);

        $this->assertSame('FAKE-MMDB-BYTES', file_get_contents($this->target));
        $this->assertSame([], glob($this->dir.'/tmp-*'), 'the working folder is cleaned up');

        Http::assertSent(fn ($request) => str_contains($request->url(), 'GeoLite2-Country')
            && $request->hasHeader('Authorization'));
    }

    public function test_it_replaces_an_older_file_with_the_new_one(): void
    {
        file_put_contents($this->target, 'OLD');
        Http::fake(['download.maxmind.com/*' => Http::response($this->archiveWith('NEW'))]);

        $this->artisan('platform:geoip-update')->assertExitCode(0);

        $this->assertSame('NEW', file_get_contents($this->target));
    }

    public function test_with_max_age_it_skips_a_file_that_is_still_fresh(): void
    {
        file_put_contents($this->target, 'FRESH');
        Http::fake();

        $this->artisan('platform:geoip-update', ['--max-age-days' => 6])
            ->expectsOutputToContain('no se descarga')
            ->assertExitCode(0);

        Http::assertNothingSent();
        $this->assertSame('FRESH', file_get_contents($this->target));
    }

    public function test_with_max_age_it_downloads_when_the_file_is_old(): void
    {
        file_put_contents($this->target, 'STALE');
        touch($this->target, now()->subDays(8)->getTimestamp());
        Http::fake(['download.maxmind.com/*' => Http::response($this->archiveWith('UPDATED'))]);

        $this->artisan('platform:geoip-update', ['--max-age-days' => 6])->assertExitCode(0);

        $this->assertSame('UPDATED', file_get_contents($this->target));
    }

    public function test_it_downloads_when_there_is_no_file_yet_even_with_max_age(): void
    {
        Http::fake(['download.maxmind.com/*' => Http::response($this->archiveWith('FIRST'))]);

        $this->artisan('platform:geoip-update', ['--max-age-days' => 6])->assertExitCode(0);

        $this->assertSame('FIRST', file_get_contents($this->target));
    }

    public function test_a_failed_download_keeps_the_existing_file(): void
    {
        file_put_contents($this->target, 'KEEP');
        Http::fake(['download.maxmind.com/*' => Http::response('', 401)]);

        $this->artisan('platform:geoip-update')->expectsOutputToContain('401')->assertExitCode(1);

        $this->assertSame('KEEP', file_get_contents($this->target));
    }

    public function test_it_needs_both_credentials(): void
    {
        config(['platform.market.geoip.license_key' => '']);
        Http::fake();

        $this->artisan('platform:geoip-update')->assertExitCode(1);

        Http::assertNothingSent();
    }
}
