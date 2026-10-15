<?php

namespace App\Modules\Platform\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tells whether persistent database connections are actually being REUSED.
 *
 * Turning DB_PERSISTENT on asks PHP to keep a worker's MySQL connection open
 * between requests, which is the one thing that stops a request from spending
 * one of the host's 500 new connections per hour. Whether it works depends on
 * the web server keeping its PHP processes alive between requests — something
 * that cannot be assumed on shared hosting, and that nothing reports. A switch
 * that quietly does nothing looks exactly like a switch that works.
 *
 * So on a small sample of requests this asks MySQL for the connection's id and
 * remembers, for the PHP process serving the request (its pid), which id it saw
 * last. The same pid with the same id is a connection that was reused; a pid
 * seen for the first time, or with a different id, opened a new one. The ratio
 * is what the admin dashboard shows and what the log warns about.
 *
 * It lives in the ops store (a deploy does not reset it) and only runs when
 * persistent connections are switched on.
 */
class ConnectionReuseProbe
{
    /** Samples in an hour before the ratio means anything. */
    private const MIN_SAMPLES = 40;

    /** Below this share of reused connections the log says persistence is not working. */
    private const MIN_RATIO = 0.5;

    public static function enabled(): bool
    {
        return (bool) config('database.persistent');
    }

    /** Looks at the connection of the request in flight, if this one is sampled. */
    public static function sample(): void
    {
        if (! self::enabled() || ! self::sampled()) {
            return;
        }

        try {
            $id = self::connectionId();
            $pid = getmypid();

            // Per connection too: with extra database users (ConnectionPool) one
            // process keeps one persistent connection for each, and alternating
            // between them must not read as "a new connection every time".
            $key = 'platform:db-reuse:pid:'.$pid.':'.DB::getDefaultConnection();
            $last = Ops::store()->get($key);

            $reused = $last !== null && (int) $last === $id;

            // A process is remembered for a day: pids are recycled and a stale entry
            // for a different process must not read as a reuse.
            Ops::store()->put($key, $id, now()->addHours(2));

            self::count($reused ? 'reused' : 'fresh');
            self::warnWhenNotWorking();
        } catch (Throwable) {
            // A probe must never break the request it is measuring.
        }
    }

    /**
     * @return array{enabled: bool, reused: int, fresh: int, ratio: float|null}
     */
    public static function summary(?\DateTimeInterface $hour = null): array
    {
        $hour ??= now();

        try {
            $reused = (int) Ops::store()->get(self::counterKey('reused', $hour), 0);
            $fresh = (int) Ops::store()->get(self::counterKey('fresh', $hour), 0);
        } catch (Throwable) {
            $reused = $fresh = 0;
        }

        $total = $reused + $fresh;

        return [
            'enabled' => self::enabled(),
            'reused' => $reused,
            'fresh' => $fresh,
            'ratio' => $total > 0 ? round($reused / $total, 3) : null,
        ];
    }

    /**
     * The share of this hour's requests that opened a NEW connection (0-1), or
     * null when it cannot be told: persistence is off (then every request that
     * ran a query did open one) or too few samples have been taken yet.
     */
    public static function freshShare(?\DateTimeInterface $hour = null): ?float
    {
        $summary = self::summary($hour);
        $total = $summary['reused'] + $summary['fresh'];

        if (! $summary['enabled'] || $total < self::MIN_SAMPLES) {
            return null;
        }

        return $summary['fresh'] / $total;
    }

    /**
     * MySQL's own id for the connection. Any other database has no such thing,
     * so the identity of the PDO object stands in (it only changes when a new
     * connection is made) — which is also what the tests, running on SQLite, see.
     */
    private static function connectionId(): int
    {
        $connection = DB::connection();

        if ($connection->getDriverName() === 'mysql') {
            return (int) $connection->selectOne('select connection_id() as id')->id;
        }

        return spl_object_id($connection->getPdo());
    }

    private static function sampled(): bool
    {
        $every = max(1, (int) config('database.reuse_probe_every'));

        return $every === 1 || random_int(1, $every) === 1;
    }

    private static function count(string $kind): void
    {
        $key = self::counterKey($kind, now());

        Ops::store()->add($key, 0, now()->addHours(3));
        Ops::store()->increment($key);
    }

    private static function warnWhenNotWorking(): void
    {
        $summary = self::summary();
        $total = $summary['reused'] + $summary['fresh'];

        if ($total < self::MIN_SAMPLES || ($summary['ratio'] ?? 1) >= self::MIN_RATIO) {
            return;
        }

        if (Ops::store()->add('platform:db-reuse:warned:'.now()->format('YmdH'), true, now()->addHours(2))) {
            Log::warning('db_persistent_reuse_low', [
                'reused' => $summary['reused'],
                'fresh' => $summary['fresh'],
                'ratio' => $summary['ratio'],
                'hint' => 'DB_PERSISTENT is on but most requests still open a new connection: the web server is not keeping PHP processes alive. It saves nothing; turn it off.',
            ]);
        }
    }

    private static function counterKey(string $kind, \DateTimeInterface $hour): string
    {
        return 'platform:db-reuse:'.$hour->format('YmdH').':'.$kind;
    }
}
