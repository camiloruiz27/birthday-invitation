<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Platform\Support\Ops;
use Carbon\CarbonInterface;
use Throwable;

/**
 * "Is there a queued job waiting for the worker?", answered without the
 * database.
 *
 * The queue is a table, and asking it costs a connection. The worker runs inside
 * the minute-by-minute tick, so asking every minute would cost sixty an hour even
 * though jobs are rare now: the slow AI work after a case's reveal, an audio to
 * synthesise, the occasional retry. Instead, whoever queues a job leaves a note
 * here (see QueueTelemetry), and the tick only starts the worker when the note
 * says a job is waiting — or, as a safety net, every few minutes in case a note
 * was lost.
 *
 * (When the next timeline EVENT is due is a different question, answered by
 * TickPlanner.)
 */
class TickActivity
{
    private const KEY = 'immersion:tick:queue-wake';

    /**
     * Something is waiting for the worker from `$epoch` on (now by default). The
     * EARLIEST such moment is kept: a job waiting now must not be pushed back by
     * a retry that is due later.
     */
    public static function touch(?int $epoch = null): void
    {
        try {
            $epoch ??= now()->getTimestamp();
            $current = Ops::store()->get(self::KEY);

            if (! is_numeric($current) || (int) $current > $epoch) {
                Ops::store()->put(self::KEY, $epoch, now()->addDays(2));
            }
        } catch (Throwable) {
            // Without the note the worker still runs at the safety minutes.
        }

        TickPlanner::publish();
    }

    /** The moment a queued job becomes available to work on, or null for none. */
    public static function wakeAt(): ?int
    {
        try {
            $value = Ops::store()->get(self::KEY);

            return is_numeric($value) ? (int) $value : null;
        } catch (Throwable) {
            return null;
        }
    }

    /** Called as the worker starts: anything queued from now on leaves a new note. */
    public static function clear(): void
    {
        try {
            Ops::store()->forget(self::KEY);
        } catch (Throwable) {
        }
    }

    /** Should this minute's tick start the worker? */
    public static function workerDue(CarbonInterface $now): bool
    {
        if (! config('immersion.tick.idle_skip')) {
            return true;
        }

        if ($now->minute % TickPlanner::SAFETY_EVERY_MINUTES === 0) {
            return true;
        }

        $wake = self::wakeAt();

        return $wake !== null && $wake <= $now->getTimestamp();
    }
}
