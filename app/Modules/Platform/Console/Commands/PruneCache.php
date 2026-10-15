<?php

namespace App\Modules\Platform\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Deletes what has expired from the file-based caches and sessions.
 *
 * Laravel's file cache never removes an expired entry unless something reads it
 * again, and the session handler's own sweep (a 2-in-100 lottery per request)
 * cannot keep up with a campaign sending strangers and bots to the site: every
 * visitor gets a session file, every cached thing a cache file. The hosting plan
 * allows a fixed number of files (inodes, 600,000), and the failure when they run
 * out is not a warning, it is the whole site refusing to write anything.
 *
 * Touches only files, never the database — the scheduler runs it every hour and
 * it costs no connection.
 */
class PruneCache extends Command
{
    protected $signature = 'ops:prune-cache {--dry-run : Count what would be deleted without deleting it}';

    protected $description = 'Borra del disco la cache y las sesiones vencidas, y cuenta los archivos (el hosting limita los inodos)';

    /** A run that would take longer than this stops and finishes next hour. */
    private const MAX_SECONDS = 25;

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $deadline = microtime(true) + self::MAX_SECONDS;

        $total = 0;
        $deleted = 0;

        foreach ($this->targets() as $label => [$path, $isExpired]) {
            [$seen, $removed] = $this->sweep($path, $isExpired, $dry, $deadline);

            $total += $seen;
            $deleted += $removed;

            $this->line(sprintf('%-10s %7d archivos, %d %s', $label, $seen, $removed, $dry ? 'se borrarían' : 'borrados'));
        }

        $this->line(sprintf('Total: %d archivos revisados, %d %s.', $total, $deleted, $dry ? 'se borrarían' : 'borrados'));

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{0: string, 1: callable(SplFileInfo): bool}>
     */
    private function targets(): array
    {
        $now = time();
        $sessionTtl = max(1, (int) config('session.lifetime')) * 60;

        $cacheExpired = function (SplFileInfo $file) use ($now): bool {
            // Laravel's file store: the first ten characters are the expiry (epoch).
            $handle = @fopen($file->getPathname(), 'rb');

            if (! $handle) {
                return false;
            }

            $head = (string) fread($handle, 10);
            fclose($handle);

            return ctype_digit($head) && (int) $head < $now;
        };

        // A session untouched for longer than its lifetime (with a day's margin)
        // can never be read again.
        $sessionExpired = fn (SplFileInfo $file): bool => $file->getMTime() < $now - $sessionTtl - 86400;

        return [
            'cache' => [(string) config('cache.stores.file.path'), $cacheExpired],
            'ops' => [(string) config('cache.stores.ops.path'), $cacheExpired],
            'sesiones' => [(string) config('session.files'), $sessionExpired],
        ];
    }

    /**
     * @param  callable(SplFileInfo): bool  $isExpired
     * @return array{0: int, 1: int} files seen, files deleted
     */
    private function sweep(string $path, callable $isExpired, bool $dry, float $deadline): array
    {
        if ($path === '' || ! is_dir($path)) {
            return [0, 0];
        }

        $seen = 0;
        $removed = 0;

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getFilename() === '.gitignore') {
                continue;
            }

            $seen++;

            if ($isExpired($file)) {
                $removed++;

                if (! $dry) {
                    @unlink($file->getPathname());
                }
            }

            if (microtime(true) > $deadline) {
                $this->warn("Se detuvo en {$path}: seguirá la próxima hora.");
                break;
            }
        }

        return [$seen, $removed];
    }
}
