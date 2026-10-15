<?php

namespace App\Modules\Platform\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Counts the database connections the application opens, per hour, and says
 * WHO opened them.
 *
 * The shared host caps the database user at a number of NEW connections per
 * hour (MySQL max_connections_per_hour; 500 on the current plan, error 1226 past
 * it). A web request that runs a query opens one, and so does each scheduled
 * command. Nothing warned before the cap was reached — the first sign was every
 * page returning 500 — and when it was reached nothing said what had spent it.
 *
 * So each connection is counted under a label (the route it served, or the
 * command that ran), and the log names the heaviest ones when the hour crosses
 * 80% of the allowance and when it runs out. The labels are what make the next
 * outage diagnosable from the log alone.
 *
 * It lives in the ops store (see Ops), so counting costs no connection of its
 * own and a deploy does not reset it. It is an estimate: it counts the requests
 * and commands that RAN A QUERY, which is what opens a connection (a request
 * answered from the cache, like the change polling, opens none).
 */
class ConnectionBudget
{
    /** How many labels are remembered per hour: routes are few, but never unbounded. */
    private const MAX_LABELS = 60;

    /**
     * @param  string  $label  the route name/URI template, or `cmd:<name>` for a command
     * @param  string|null  $connection  the connection that was opened; the default one when omitted.
     *                                    The host's allowance is per database USER, and extra users can be
     *                                    configured (ConnectionPool), so each connection is counted, and
     *                                    warned about, on its own.
     */
    public static function hit(string $label = 'other', ?string $connection = null): void
    {
        $limit = self::limit();

        try {
            $hour = now();
            $key = self::key($hour);
            $connection ??= DB::getDefaultConnection();

            Ops::store()->add($key, 0, now()->addHours(3));
            Ops::store()->increment($key);

            $count = self::countConnection($hour, $connection);

            self::countLabel($hour, $label);

            // With persistent connections most requests reuse one and cost the
            // allowance nothing, so the raw count would cry wolf (a 200-player test
            // counted 1,075 against a cap of 500 and nothing failed). Once the reuse
            // probe has enough samples the warnings go by the estimate of NEW
            // connections instead; until then, by the raw count (the safe side).
            $effective = self::estimateNew($count, $hour);

            // Once per hour per connection: the estimate can sit on the same number
            // for several requests, so "exactly equal" is not enough.
            if ($limit > 0 && $effective >= (int) ceil($limit * 0.8) && self::firstTime($hour, $connection, 'high')) {
                Log::warning('db_connection_budget_high', [
                    'connection' => $connection,
                    'connections_this_hour' => $count,
                    'estimated_new_connections' => $effective,
                    'allowance_per_hour' => $limit,
                    'heaviest' => self::top(5),
                    'hint' => 'The host limits the database user per hour; past it every page returns 503 until the hour turns over.',
                ]);
            }

            if ($limit > 0 && $effective >= $limit && self::firstTime($hour, $connection, 'spent')) {
                Log::error('db_connection_budget_spent', [
                    'connection' => $connection,
                    'connections_this_hour' => $count,
                    'estimated_new_connections' => $effective,
                    'allowance_per_hour' => $limit,
                    'heaviest' => self::top(5),
                ]);
            }
        } catch (Throwable) {
            // A broken counter must never break a request.
        }
    }

    public static function limit(): int
    {
        return max(0, (int) config('platform.connection_budget.per_hour'));
    }

    /**
     * The labels that opened the most connections in an hour, heaviest first.
     *
     * @return array<string, int>
     */
    public static function top(int $limit = 5, ?\DateTimeInterface $hour = null): array
    {
        try {
            $hour ??= now();
            $counts = [];

            foreach ((array) Ops::store()->get(self::labelsKey($hour), []) as $label) {
                $counts[$label] = (int) Ops::store()->get(self::labelKey($hour, $label), 0);
            }

            arsort($counts);

            return array_slice($counts, 0, $limit, true);
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * This hour and the one before, for the admin dashboard.
     *
     * `limit` is what ONE database user may open per hour; `by_connection` says
     * how this hour's connections split over the users in play (one entry
     * unless extra users are configured).
     *
     * `estimated_new_this_hour` is what the cap really counts when persistent
     * connections are on: this hour's requests that ran a query, times the share
     * the reuse probe saw open a new connection. Null when it cannot be told.
     *
     * @return array{this_hour: int, last_hour: int, limit: int, estimated_new_this_hour: int|null, by_connection: array<string, int>, top_this_hour: array<string, int>, top_last_hour: array<string, int>}
     */
    public static function summary(): array
    {
        try {
            $thisHour = (int) Ops::store()->get(self::key(now()), 0);
            $share = ConnectionReuseProbe::freshShare(now());

            return [
                'this_hour' => $thisHour,
                'last_hour' => (int) Ops::store()->get(self::key(now()->subHour()), 0),
                'limit' => self::limit(),
                'estimated_new_this_hour' => $share === null ? null : (int) ceil($thisHour * $share),
                'by_connection' => self::byConnection(now()),
                'top_this_hour' => self::top(8),
                'top_last_hour' => self::top(8, now()->subHour()),
            ];
        } catch (Throwable) {
            return [
                'this_hour' => 0,
                'last_hour' => 0,
                'limit' => self::limit(),
                'estimated_new_this_hour' => null,
                'by_connection' => [],
                'top_this_hour' => [],
                'top_last_hour' => [],
            ];
        }
    }

    /**
     * Connections opened in an hour per database connection (user).
     *
     * @return array<string, int>
     */
    public static function byConnection(\DateTimeInterface $hour): array
    {
        $counts = [];

        foreach ((array) Ops::store()->get(self::connectionsKey($hour), []) as $name) {
            $counts[$name] = (int) Ops::store()->get(self::connectionKey($hour, $name), 0);
        }

        arsort($counts);

        return $counts;
    }

    /** What the count means for the cap: the raw count, or the estimate of new connections. */
    private static function estimateNew(int $count, \DateTimeInterface $hour): int
    {
        $share = ConnectionReuseProbe::freshShare($hour);

        return $share === null ? $count : (int) ceil($count * $share);
    }

    /** True only the first time a given warning is asked for in an hour, per connection. */
    private static function firstTime(\DateTimeInterface $hour, string $connection, string $kind): bool
    {
        return (bool) Ops::store()->add(
            'platform:db-connections:'.$hour->format('YmdH').":warned:{$kind}:{$connection}",
            true,
            now()->addHours(3)
        );
    }

    /** Counts one connection against its own user and returns that user's total this hour. */
    private static function countConnection(\DateTimeInterface $hour, string $connection): int
    {
        $names = (array) Ops::store()->get(self::connectionsKey($hour), []);

        if (! in_array($connection, $names, true)) {
            // A handful at most (the pool has five users): no cap needed.
            $names[] = $connection;
            Ops::store()->put(self::connectionsKey($hour), $names, now()->addHours(3));
        }

        $key = self::connectionKey($hour, $connection);

        Ops::store()->add($key, 0, now()->addHours(3));

        return (int) Ops::store()->increment($key);
    }

    private static function countLabel(\DateTimeInterface $hour, string $label): void
    {
        $label = mb_substr($label, 0, 120);
        $labelsKey = self::labelsKey($hour);
        $labels = (array) Ops::store()->get($labelsKey, []);

        if (! in_array($label, $labels, true)) {
            // Past the cap everything new is "other": a bot walking random URLs
            // must not be able to grow this without bound.
            if (count($labels) >= self::MAX_LABELS) {
                $label = 'other';
            }

            if (! in_array($label, $labels, true)) {
                $labels[] = $label;
                Ops::store()->put($labelsKey, $labels, now()->addHours(3));
            }
        }

        $key = self::labelKey($hour, $label);

        Ops::store()->add($key, 0, now()->addHours(3));
        Ops::store()->increment($key);
    }

    private static function key(\DateTimeInterface $at): string
    {
        return 'platform:db-connections:'.$at->format('YmdH');
    }

    private static function connectionsKey(\DateTimeInterface $at): string
    {
        return 'platform:db-connections:'.$at->format('YmdH').':conns';
    }

    private static function connectionKey(\DateTimeInterface $at, string $connection): string
    {
        return 'platform:db-connections:'.$at->format('YmdH').':conn:'.$connection;
    }

    private static function labelsKey(\DateTimeInterface $at): string
    {
        return 'platform:db-connections:'.$at->format('YmdH').':labels';
    }

    private static function labelKey(\DateTimeInterface $at, string $label): string
    {
        return 'platform:db-connections:'.$at->format('YmdH').':by:'.sha1($label);
    }
}
