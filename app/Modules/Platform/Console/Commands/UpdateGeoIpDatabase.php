<?php

namespace App\Modules\Platform\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use PharData;
use Throwable;

/**
 * Downloads the MaxMind GeoLite2-Country database the market detection reads.
 *
 * The file is not in git (it is a licensed, frequently updated binary), so a
 * fresh deploy has none until this runs — and without it every visitor is
 * simply Colombian, which is the safe failure. Scheduled weekly.
 *
 * Needs a free MaxMind account: MAXMIND_ACCOUNT_ID and MAXMIND_LICENSE_KEY.
 */
class UpdateGeoIpDatabase extends Command
{
    protected $signature = 'platform:geoip-update
                            {--max-age-days= : Skip the download if the current file is newer than this many days}';

    protected $description = 'Download the MaxMind GeoLite2-Country database used to detect the visitor market';

    private const URL = 'https://download.maxmind.com/geoip/databases/GeoLite2-Country/download?suffix=tar.gz';

    public function handle(): int
    {
        $account = (string) config('platform.market.geoip.account_id');
        $key = (string) config('platform.market.geoip.license_key');
        $target = (string) config('platform.market.geoip.database');

        if ($account === '' || $key === '') {
            $this->error('Faltan MAXMIND_ACCOUNT_ID y MAXMIND_LICENSE_KEY.');

            return self::FAILURE;
        }

        // The scheduler checks daily but only downloads when the file is a few
        // days old: a missed minute of cron costs a day, not a whole week, and
        // MaxMind's 30-downloads-a-day limit is never in play.
        $maxAge = $this->option('max-age-days');

        if ($maxAge !== null && is_file($target) && filemtime($target) > now()->subDays((int) $maxAge)->getTimestamp()) {
            $this->info("La base tiene menos de {$maxAge} dias; no se descarga.");

            return self::SUCCESS;
        }

        $work = storage_path('app/geoip/tmp-'.bin2hex(random_bytes(4)));
        File::ensureDirectoryExists($work);
        $archive = $work.'/db.tar.gz';

        try {
            $response = Http::withBasicAuth($account, $key)
                ->timeout(120)
                ->sink($archive)
                ->get(self::URL);

            if (! $response->successful()) {
                $this->error("MaxMind respondió {$response->status()}.");

                return self::FAILURE;
            }

            (new PharData($archive))->extractTo($work.'/out', null, true);

            $found = collect(File::allFiles($work.'/out'))
                ->first(fn ($file) => $file->getExtension() === 'mmdb');

            if (! $found) {
                $this->error('El archivo descargado no contiene una base .mmdb.');

                return self::FAILURE;
            }

            File::ensureDirectoryExists(dirname($target));

            // Copy beside the target, then rename: a request reading the
            // database never sees a half-written file.
            File::copy($found->getPathname(), $target.'.new');
            rename($target.'.new', $target);
        } catch (Throwable $exception) {
            report($exception);
            $this->error('No se pudo actualizar la base: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            File::deleteDirectory($work);
        }

        $this->info("Base actualizada: {$target}");

        return self::SUCCESS;
    }
}
