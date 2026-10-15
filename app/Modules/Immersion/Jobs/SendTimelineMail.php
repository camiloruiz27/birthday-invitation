<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\GameRevision;
use App\Modules\Immersion\Support\MailBudget;
use App\Modules\Immersion\Support\MailFailures;
use App\Modules\Immersion\Support\TickActivity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * One envelope, one player, one email.
 *
 * It used to be a loop inside the job that dispatches the event: every player's
 * message went out one after another over a fresh SMTP conversation, a failure
 * was only reported, and the event was already marked sent, so nothing ever
 * tried again. A message the mailbox refused simply never existed, and the
 * table waited for an email that was not coming.
 *
 * As its own job it gets what a queue gives for free: it is retried with
 * growing delays, it is spread out by the `immersion-mail` rate limiter (the
 * mailbox does not take a burst), it waits when the day's allowance is spent
 * instead of failing (MailBudget), and when it finally gives up the Game Master
 * is told (MailFailures) — the envelope is still in the player's inbox on the
 * site either way.
 */
class SendTimelineMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Unlimited attempts, bounded by retryUntil(): being put back because of
     * the rate limit or the daily allowance is not a failure and must not use
     * attempts up. Real exceptions are counted separately by maxExceptions.
     */
    public int $tries = 0;

    public int $maxExceptions = 3;

    public int $timeout = 90;

    public function __construct(public int $eventId, public int $playerId)
    {
        $this->onQueue((string) config('immersion.queues.mail'));
    }

    /** @return array<int, int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(3);
    }

    /** @return array<int, object> */
    public function middleware(): array
    {
        return [new RateLimited('immersion-mail')];
    }

    /**
     * Sends it in the current process, with no queue, and records a failure for
     * the Game Master instead of retrying. For the cron's tick and for the
     * Game Master's own clicks, where a worker would cost a connection to start.
     */
    public static function sendNow(int $eventId, int $playerId): void
    {
        $job = new static($eventId, $playerId);

        try {
            $job->handle();
        } catch (Throwable $exception) {
            $job->failed($exception);
        }
    }

    public function handle(): void
    {
        $event = TimelineEvent::find($this->eventId);
        $player = Player::find($this->playerId);

        if (! $event || ! $player) {
            return;
        }

        // The day's allowance is spent: wait for it to come back instead of
        // sending into a refusal. Envelopes may use all of it.
        if (! MailBudget::canSend(MailBudget::ENVELOPE)) {
            // Run from a worker, it goes back to the queue and the tick looks
            // again when it is due. Run in place (no worker, nothing to retry
            // it) it is the Game Master who is told.
            if ($this->job === null) {
                MailFailures::add((int) $event->game_id, $event->id, $player->id, "{$player->name}: {$event->title} (sin cupo de correo hoy)");
                GameRevision::bump((int) $event->game_id, GameRevision::TIMELINE);

                return;
            }

            TickActivity::touch(now()->addSeconds(900)->getTimestamp());
            $this->release(900);

            return;
        }

        Mail::to($player->email)->send(new CaseTimelineMail($event, $player));

        MailFailures::clear((int) $event->game_id, $event->id, $player->id);
    }

    public function failed(Throwable $exception): void
    {
        $event = TimelineEvent::find($this->eventId);
        $player = Player::find($this->playerId);

        Log::error('timeline_mail_failed', [
            'event_id' => $this->eventId,
            'player_id' => $this->playerId,
            'message' => $exception->getMessage(),
        ]);

        if (! $event || ! $player) {
            return;
        }

        MailFailures::add((int) $event->game_id, $event->id, $player->id, "{$player->name}: {$event->title}");

        // The console shows the notice from the same stamp that tells it to
        // reload.
        GameRevision::bump((int) $event->game_id, GameRevision::TIMELINE);
    }
}
