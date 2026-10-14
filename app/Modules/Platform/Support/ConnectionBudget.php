<?php

namespace App\Modules\Platform\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Counts the database connections the application opens, per hour.
 *
 * The shared host caps the database user at a number of NEW connections per
 * hour (MySQL max_connections_per_hour; 500 on the current plan, error 1226 past
 * it). A web request that runs a query opens one, and so does each scheduled
 * command. Nothing warned before the cap was reached — the first sign was every
 * page returning 500 — so this keeps the count and says so in the log at 80%.
 *
 * It lives in the cache, so counting costs no connection of its own, and it is
 * an estimate: it counts the requests and commands that RAN A QUERY, which is
 * what opens a connection (the request that only reads the cache, like the
 * change polling, does not).
 */
class ConnectionBudget
{
    public static function hit(): void
    {
        $limit = self::limit();

        try {
            $key = self::key(now());

            Cache::add($key, 0, now()->addHours(3));
            $count = (int) Cache::increment($key);

            // Exactly equal, so the warning fires once per hour and not on
            // every request after it.
            if ($limit > 0 && $count === (int) ceil($limit * 0.8)) {
                Log::warning('db_connection_budget_high', [
                    'connections_this_hour' => $count,
                    'allowance_per_hour' => $limit,
                    'hint' => 'The host limits the database user per hour; past it every page returns 503 until the hour turns over.',
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
     * This hour and the one before, for the admin dashboard.
     *
     * @return array{this_hour: int, last_hour: int, limit: int}
     */
    public static function summary(): array
    {
        try {
            return [
                'this_hour' => (int) Cache::get(self::key(now()), 0),
                'last_hour' => (int) Cache::get(self::key(now()->subHour()), 0),
                'limit' => self::limit(),
            ];
        } catch (Throwable) {
            return ['this_hour' => 0, 'last_hour' => 0, 'limit' => self::limit()];
        }
    }

    private static function key(\DateTimeInterface $at): string
    {
        return 'platform:db-connections:'.$at->format('YmdH');
    }
}
