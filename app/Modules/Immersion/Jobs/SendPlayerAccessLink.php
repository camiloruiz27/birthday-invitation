<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Mail\PlayerAccessLinkMail;
use App\Modules\Immersion\Models\Player;
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
 * One player's access link, one email (the fan-out is SendPlayerAccessLinks).
 *
 * Same treatment as the envelopes (SendTimelineMail) but with the lower
 * priority it deserves: the link is also copyable from the console, so while the
 * day's allowance is mostly spent it waits for tomorrow and leaves the room for
 * envelopes a live table is waiting on.
 */
class SendPlayerAccessLink implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 0;

    public int $maxExceptions = 3;

    public int $timeout = 90;

    public function __construct(public int $playerId)
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
        return now()->addHours(12);
    }

    /** @return array<int, object> */
    public function middleware(): array
    {
        return [new RateLimited('immersion-mail')];
    }

    /**
     * Sends it in the current process, with no queue (see SendTimelineMail).
     * The link is a click away from the Game Master, so a failure only has to
     * say so: they can copy it from the console.
     */
    public static function sendNow(int $playerId): void
    {
        $job = new static($playerId);

        try {
            $job->handle();
        } catch (Throwable $exception) {
            $job->failed($exception);
        }
    }

    public function handle(): void
    {
        $player = Player::find($this->playerId);

        if (! $player) {
            return;
        }

        if (! MailBudget::canSend(MailBudget::OPTIONAL)) {
            // In place there is no worker to retry later: say it, so the Game
            // Master copies the link instead of waiting for an email.
            if ($this->job === null) {
                MailFailures::add((int) $player->game_id, 0, $player->id, "Enlace de {$player->name}: no se envio por el cupo de correo de hoy, copialo de la lista de jugadores");

                return;
            }

            TickActivity::touch(now()->addSeconds(1800)->getTimestamp());
            $this->release(1800);

            return;
        }

        Mail::to($player->email)->send(new PlayerAccessLinkMail($player));
    }

    public function failed(Throwable $exception): void
    {
        Log::error('player_access_link_mail_failed', [
            'player_id' => $this->playerId,
            'message' => $exception->getMessage(),
        ]);

        // The link is still on the console to copy; the Game Master is told so
        // they know to, instead of waiting for an email that did not leave.
        $player = Player::find($this->playerId);

        if ($player) {
            MailFailures::add((int) $player->game_id, 0, $player->id, "Enlace de {$player->name}: el correo no salio, copialo de la lista de jugadores");
        }
    }
}
