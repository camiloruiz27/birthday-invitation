<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Mail\PlayerAccessLinkMail;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\MailBudget;
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

    public function handle(): void
    {
        $player = Player::find($this->playerId);

        if (! $player) {
            return;
        }

        if (! MailBudget::canSend(MailBudget::OPTIONAL)) {
            $this->release(1800);

            return;
        }

        Mail::to($player->email)->send(new PlayerAccessLinkMail($player));
    }

    public function failed(Throwable $exception): void
    {
        // The link is still on the console to copy, so this is only logged.
        Log::error('player_access_link_mail_failed', [
            'player_id' => $this->playerId,
            'message' => $exception->getMessage(),
        ]);
    }
}
