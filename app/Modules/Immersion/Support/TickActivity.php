<?php

namespace App\Modules\Immersion\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * "Is there anything for the minute-by-minute tick to do?", answered without the
 * database.
 *
 * The scheduler runs the tick as its own PHP process every minute, and each run
 * that touches the database costs a connection out of the host's 500-per-hour
 * allowance — 60 of them an hour, around the clock, to find out that nobody is
 * playing. A hint in the cache says when somebody is: a game is running, a job
 * was queued, a job is waiting to be retried. While the hint is fresh the tick
 * runs every minute (timeline events are due to the minute); when it is not, it
 * only runs every few minutes as a safety net, which is also what notices a
 * game or a job the hint somehow missed.
 *
 * The hint can only ever make the tick run LESS than it would, never skip work
 * for good: the safety net bounds any miss to a few minutes.
 */
class TickActivity
{
    private const KEY = 'immersion:tick:active-until';

    /** How often an idle tick still looks, as a safety net. */
    public const IDLE_EVERY_MINUTES = 5;

    public static function touch(int $minutes = 15): void
    {
        try {
            $until = now()->addMinutes($minutes)->getTimestamp();

            // Never shorten a longer window that is already set.
            if ((int) Cache::get(self::KEY, 0) < $until) {
                Cache::put(self::KEY, $until, now()->addMinutes($minutes + 5));
            }
        } catch (Throwable) {
            // Without the hint the tick simply runs every time, as before.
        }
    }

    public static function isActive(): bool
    {
        try {
            return (int) Cache::get(self::KEY, 0) > now()->getTimestamp();
        } catch (Throwable) {
            return true;
        }
    }

    /** Should this minute's tick do anything at all? */
    public static function shouldRun(CarbonInterface $now): bool
    {
        if (! config('immersion.tick.idle_skip')) {
            return true;
        }

        return self::isActive() || $now->minute % self::IDLE_EVERY_MINUTES === 0;
    }
}
