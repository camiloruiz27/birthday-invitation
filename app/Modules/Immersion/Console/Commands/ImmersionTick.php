<?php

namespace App\Modules\Immersion\Console\Commands;

use App\Exceptions\Handler;
use App\Modules\Immersion\Support\TickActivity;
use App\Modules\Immersion\Support\TickPlanner;
use App\Modules\Platform\Support\ConnectionBudget;
use App\Modules\Platform\Support\Ops;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The once-a-minute heartbeat of the engine: fire the timeline events that are
 * due, then drain the queue.
 *
 * It used to be two scheduled commands, and the scheduler runs each one as its
 * own PHP process — so each opened its own MySQL connection every minute. The
 * shared host caps the database user at 500 new connections per hour, and two
 * of every minute is 120 of them before a single player has loaded a page.
 * Running both in ONE process shares one connection, and when nothing is going
 * on it opens none (TickActivity).
 *
 * The two halves must never wait for each other:
 *
 * - The timeline is quick and is what makes a game move, so it runs on every
 *   tick.
 * - The worker can be busy for minutes (text-to-speech, grading, an epilogue).
 *   Each half has its own lock instead of the scheduler's overlap guard, which
 *   would have skipped the WHOLE tick — timeline included — for as long as one
 *   job ran, and no game would have received an envelope in the meantime. A tick
 *   that finds the worker busy does the timeline and leaves.
 *
 * A failure in one half does not stop the other, and the exit code still says
 * something went wrong.
 */
class ImmersionTick extends Command
{
    protected $signature = 'immersion:tick';

    protected $description = 'Dispara los eventos de linea de tiempo vencidos y procesa la cola, en un solo proceso (una sola conexion a la base de datos, y ninguna si no hay nada que hacer).';

    public function handle(): int
    {
        $now = now();
        $timelineDue = TickPlanner::timelineDue($now);
        $workerDue = config('queue.default') !== 'sync' && TickActivity::workerDue($now);

        // No event is due and nothing is queued: leave without touching the
        // database. This is the common case — a case has a new moment about every
        // eleven minutes — and it is what keeps the cron off the host's hourly
        // connection allowance.
        if (! $timelineDue && ! $workerDue) {
            return self::SUCCESS;
        }

        // This run opens a database connection of its own (the scheduler gives
        // every command its own process), which is worth counting.
        ConnectionBudget::hit('cmd:immersion:tick');

        $failed = false;

        // Timeline first. Its events run inside this process (see
        // DispatchTimelineEvent::run), so there is nothing for the worker to pick
        // up for them. The lock is short: it only stops two overlapping ticks
        // from both scanning.
        if ($timelineDue) {
            $failed = $this->locked('immersion:tick:timeline', 120, fn () => $this->step('immersion:process-timeline')) || $failed;

            // What is due next, for the wakes that follow (and for the cron
            // script, which does not even start PHP until then).
            TickPlanner::plan();
        }

        // Shared hosting has no daemon, so the worker is a short-lived process:
        // it drains the queue and exits. `queue:work` rejects the sync driver,
        // and with it there is nothing to drain anyway: jobs already ran inline.
        //
        // Queues in priority order: mail first, slow jobs last, so an envelope
        // never sits behind a text-to-speech that takes minutes. The lock
        // outlives the longest job (420 s) with room to spare, and expires on
        // its own if a host kill leaves it behind.
        if ($workerDue) {
            // Cleared BEFORE the worker starts: a job queued while it runs leaves
            // a new note, and the next tick looks again.
            TickActivity::clear();

            $queues = implode(',', [
                (string) config('immersion.queues.mail'),
                'default',
                (string) config('immersion.queues.slow'),
            ]);

            $failed = $this->locked('immersion:tick:worker', 600, fn () => $this->step('queue:work', [
                '--queue' => $queues,
                '--stop-when-empty' => true,
                '--max-time' => 50,
                '--tries' => 2,
                '--quiet' => true,
            ])) || $failed;
        }

        TickPlanner::publish();

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Runs the step unless another tick holds the lock. A busy lock is not a
     * failure: the other tick is doing that very work.
     *
     * @return bool true when the step failed
     */
    private function locked(string $name, int $seconds, callable $step): bool
    {
        try {
            $lock = Cache::lock($name, $seconds);
        } catch (Throwable) {
            // No lock to be had: run unguarded, as before.
            return $step();
        }

        if (! $lock->get()) {
            return false;
        }

        try {
            return $step();
        } finally {
            $lock->release();
        }
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return bool true when the step failed
     */
    private function step(string $command, array $arguments = []): bool
    {
        try {
            return $this->call($command, $arguments) !== self::SUCCESS;
        } catch (Throwable $exception) {
            // The database refusing connections (the host's hourly allowance) fails
            // EVERY tick the same way until the hour turns over; one line every ten
            // minutes says it, a line a minute buries the log.
            if (Handler::databaseUnavailable($exception)
                && ! Ops::store()->add('immersion:tick:db-error-logged', true, now()->addMinutes(10))) {
                return true;
            }

            Log::error('immersion_tick_step_failed', [
                'command' => $command,
                'message' => $exception->getMessage(),
            ]);

            return true;
        }
    }
}
