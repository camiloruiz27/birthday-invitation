<?php

namespace App\Modules\Platform\Support;

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

    /** @param string $label the route name/URI template, or `cmd:<name>` for a command */
    public static function hit(string $label = 'other'): void
    {
        $limit = self::limit();

        try {
            $hour = now();
            $key = self::key($hour);

            Ops::store()->add($key, 0, now()->addHours(3));
            $count = (int) Ops::store()->increment($key);

            self::countLabel($hour, $label);

            // Exactly equal, so each warning fires once per hour and not on every
            // request after it.
            if ($limit > 0 && $count === (int) ceil($limit * 0.8)) {
                Log::warning('db_connection_budget_high', [
                    'connections_this_hour' => $count,
                    'allowance_per_hour' => $limit,
                    'heaviest' => self::top(5),
                    'hint' => 'The host limits the database user per hour; past it every page returns 503 until the hour turns over.',
                ]);
            }

            if ($limit > 0 && $count === $limit) {
                Log::error('db_connection_budget_spent', [
                    'connections_this_hour' => $count,
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
     * @return array{this_hour: int, last_hour: int, limit: int, top_this_hour: array<string, int>, top_last_hour: array<string, int>}
     */
    public static function summary(): array
    {
        try {
            return [
                'this_hour' => (int) Ops::store()->get(self::key(now()), 0),
                'last_hour' => (int) Ops::store()->get(self::key(now()->subHour()), 0),
                'limit' => self::limit(),
                'top_this_hour' => self::top(8),
                'top_last_hour' => self::top(8, now()->subHour()),
            ];
        } catch (Throwable) {
            return [
                'this_hour' => 0,
                'last_hour' => 0,
                'limit' => self::limit(),
                'top_this_hour' => [],
                'top_last_hour' => [],
            ];
        }
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

    private static function labelsKey(\DateTimeInterface $at): string
    {
        return 'platform:db-connections:'.$at->format('YmdH').':labels';
    }

    private static function labelKey(\DateTimeInterface $at, string $label): string
    {
        return 'platform:db-connections:'.$at->format('YmdH').':by:'.sha1($label);
    }
}
