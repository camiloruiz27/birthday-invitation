<?php

namespace App\Modules\Immersion\Support;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Queue\Events\JobReleasedAfterException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Two small things the queue says about itself.
 *
 * It keeps TickActivity fresh while there is work (a job queued, a job waiting
 * for its retry), and it logs a job that ran for a long time. One worker drains
 * every queue, so a job that takes minutes holds up everything behind it; the
 * log is the data to decide, later, whether a second worker is worth its extra
 * database connection per minute.
 */
class QueueTelemetry
{
    /** Seconds after which a job is worth a log line. */
    private const SLOW_SECONDS = 60;

    /** @var array<string, float> */
    private static array $started = [];

    public static function register(): void
    {
        Event::listen(JobQueued::class, fn () => TickActivity::touch());

        // A job put back for a retry sits in the table until its delay is up
        // (from half a minute to a quarter of an hour). The delay is not in the
        // event, so look again in a minute; the worker simply finds nothing yet
        // if it is early, and the safety minutes catch the rest.
        Event::listen(JobReleasedAfterException::class, fn () => TickActivity::touch(now()->addMinute()->getTimestamp()));

        Event::listen(JobProcessing::class, function (JobProcessing $event) {
            self::$started[self::key($event)] = microtime(true);
        });

        Event::listen(JobProcessed::class, function (JobProcessed $event) {
            $key = self::key($event);
            $seconds = isset(self::$started[$key]) ? microtime(true) - self::$started[$key] : null;
            unset(self::$started[$key]);

            if ($seconds !== null && $seconds >= self::SLOW_SECONDS) {
                Log::warning('immersion_slow_job', [
                    'job' => $event->job->resolveName(),
                    'queue' => $event->job->getQueue(),
                    'seconds' => round($seconds, 1),
                    'hint' => 'One worker drains every queue: this held up whatever was behind it.',
                ]);
            }
        });
    }

    private static function key(JobProcessing|JobProcessed $event): string
    {
        return (string) ($event->job->getJobId() ?? spl_object_id($event->job));
    }
}
