<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Platform\Support\Ops;
use Carbon\CarbonInterface;
use Throwable;

/**
 * Knows WHEN the next timeline event is due, so the minute-by-minute cron does
 * not have to ask the database every minute.
 *
 * The scheduler wakes the tick every minute, and each wake that touches the
 * database costs one of the host's 500 connections per hour. But the hour of
 * every event is known in advance: it is the game's start, plus the time it was
 * paused, plus the event's offset. A case has six to twelve moments over about
 * seventy minutes (a new one every eleven, on average), so on a running game
 * only a handful of the sixty wakes an hour have anything to do.
 *
 * So after each run the tick works out the EARLIEST moment any running game
 * needs it and stores it (epoch seconds) in the ops store, which a deploy does
 * not clear. The next wakes find it in the future and leave without a query. The
 * plan is thrown away whenever something that could change it happens — a game
 * starts, pauses, resumes or ends, an event is created, sent or removed — and
 * the next wake builds it again.
 *
 * It is a hint, and it can only ever make the tick run LESS than it would, never
 * skip work for good:
 *  - no plan (a cleared cache, a first run) means "unknown", which means run;
 *  - every few minutes the tick looks anyway as a safety net (SAFETY_EVERY_MINUTES),
 *    which is also what catches a plan that was somehow wrong.
 *
 * The same moment is written to a one-line file the cron script reads BEFORE it
 * starts PHP at all (see cron-scheduler.sh), because booting Laravel every minute
 * to learn "not yet" is its own cost.
 */
class TickPlanner
{
    private const KEY = 'immersion:tick:next-due';

    /** "Nothing is scheduled" is stored as a moment so far away it never arrives. */
    private const NONE = 4102444800;

    /** How often an idle tick still looks, as a safety net. */
    public const SAFETY_EVERY_MINUTES = 15;

    /**
     * Should this minute's tick look at the timeline at all?
     */
    public static function timelineDue(CarbonInterface $now): bool
    {
        if (! config('immersion.tick.idle_skip')) {
            return true;
        }

        if (self::safetyMinute($now)) {
            return true;
        }

        $next = self::nextDue();

        return $next === null || $next <= $now->getTimestamp();
    }

    /** The moment the next event is due, or null when no plan is known. */
    public static function nextDue(): ?int
    {
        try {
            $value = Ops::store()->get(self::KEY);

            return is_numeric($value) ? (int) $value : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Works out when the next event is due, across every running game, and
     * remembers it. One cheap pair of queries.
     */
    public static function plan(): void
    {
        $next = self::NONE;

        try {
            $games = Game::query()
                ->where('status', 'running')
                ->whereNotNull('started_at')
                ->get(['id', 'started_at', 'paused_seconds_total']);

            if ($games->isNotEmpty()) {
                // The earliest unsent offset of each game.
                $offsets = TimelineEvent::query()
                    ->whereIn('game_id', $games->modelKeys())
                    ->whereNull('sent_at')
                    ->selectRaw('game_id, MIN(trigger_offset_minutes) as first_offset')
                    ->groupBy('game_id')
                    ->pluck('first_offset', 'game_id');

                foreach ($games as $game) {
                    if (! $offsets->has($game->id)) {
                        continue;
                    }

                    $due = $game->started_at->getTimestamp()
                        + (int) $game->paused_seconds_total
                        + ((int) $offsets[$game->id]) * 60;

                    $next = min($next, $due);
                }
            }

            Ops::store()->put(self::KEY, $next, now()->addDays(2));
        } catch (Throwable) {
            // No plan is a safe answer: the tick simply runs.
            self::forgetPlan();
        }

        self::publish();
    }

    /**
     * Something that can change the plan happened. The next wake rebuilds it,
     * and the cron script is told to wake PHP at the next minute to do so.
     */
    public static function invalidate(): void
    {
        self::forgetPlan();
        self::publish();
    }

    /**
     * Writes the moment the cron should next start PHP: the earlier of the next
     * event and the next queued job. 0 (wake at once) when the plan is unknown.
     */
    public static function publish(): void
    {
        $next = self::nextDue();
        $queue = TickActivity::wakeAt();

        $moment = $next === null ? 0 : min($next, $queue ?? self::NONE);

        try {
            $path = config('immersion.tick.next_run_file');

            if (! is_string($path) || $path === '' || ! is_dir(dirname($path))) {
                return;
            }

            // Written whole and replaced, so the shell never reads half a number.
            $temporary = $path.'.'.getmypid().'.tmp';

            if (@file_put_contents($temporary, (string) $moment) !== false) {
                @rename($temporary, $path);
            }
        } catch (Throwable) {
            // Without the file the script runs the tick every minute, as before.
        }
    }

    private static function safetyMinute(CarbonInterface $now): bool
    {
        return $now->minute % self::SAFETY_EVERY_MINUTES === 0;
    }

    private static function forgetPlan(): void
    {
        try {
            Ops::store()->forget(self::KEY);
        } catch (Throwable) {
        }
    }
}
