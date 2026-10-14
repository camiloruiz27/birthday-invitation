<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Ai\Contracts\EpilogueProvider;
use App\Modules\Immersion\Mail\CaseEpilogueMail;
use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Support\EndingBilling;
use App\Modules\Immersion\Support\GameMailPolicy;
use App\Modules\Immersion\Support\GameRevision;
use App\Modules\Immersion\Support\MailBudget;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Writes and mails one player's epilogue.
 *
 * One job per player rather than one per game: each is a separate model call
 * and a separate address, and a table of six should not lose five endings
 * because one generation timed out or one mailbox bounced.
 *
 * ShouldBeUnique is keyed on the accusation, so a re-reveal or a retried queue
 * batch cannot write a second, different message to someone who already got
 * theirs — the epilogue is the last thing the table reads, and receiving two
 * conflicting versions from the same character would undo the ending.
 */
class SendEpilogue implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Unlimited attempts, bounded by retryUntil(): waiting for the mailbox's
     * daily allowance is not a failure and must not use attempts up. The
     * gateway being briefly down is worth exactly one retry, which is what
     * maxExceptions counts.
     */
    public int $tries = 0;

    public int $maxExceptions = 2;

    public int $backoff = 30;

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(12);
    }

    /**
     * Comfortably longer than the gateway's own epilogue timeout, so the job
     * is never the thing that kills a generation that was about to land.
     */
    public int $timeout = 300;

    public function __construct(public int $accusationId)
    {
        $this->onQueue((string) config('immersion.queues.slow'));
    }

    public function uniqueId(): string
    {
        return (string) $this->accusationId;
    }

    public function handle(EpilogueProvider $epilogues): void
    {
        $accusation = Accusation::with(['player', 'game'])->find($this->accusationId);

        if (! $accusation || ! $accusation->player) {
            return;
        }

        // Already written and delivered. Reaching here twice is normal (a
        // retried batch); rewriting is not.
        if ($accusation->epilogue_sent_at) {
            return;
        }

        $body = $accusation->epilogue_body ?: $epilogues->write($accusation);

        if (! $body) {
            // The provider already decided it could not produce one. Throwing
            // would burn the retry on a failure that is not transient, and the
            // classic reveal is still a complete ending on its own.
            $accusation->update(['epilogue_status' => Accusation::EPILOGUE_FAILED]);

            return;
        }

        // Stored before it is sent: an email is easy to lose, and the solution
        // page has to be able to show this again at the table.
        $accusation->update([
            'epilogue_body' => $body,
            'epilogue_status' => Accusation::EPILOGUE_READY,
        ]);

        if (GameMailPolicy::epilogue()) {
            // The body is stored, so waiting costs no second generation. An
            // epilogue is a courtesy that can wait for tomorrow; the envelopes a
            // live table is waiting on keep the room that is left.
            if (! MailBudget::canSend(MailBudget::OPTIONAL)) {
                $this->release(1800);

                return;
            }

            Mail::to($accusation->player->email)->send(new CaseEpilogueMail($accusation->fresh()));
        }

        // "Delivered": by email when that is on, and in every case on the
        // Solution page, which is where the player reads it. Without the email
        // this is the moment it reaches them, and the Game Master's counter
        // (written / total) needs it to close.
        $accusation->update(['epilogue_sent_at' => now()]);

        // The ending is paid for by the first epilogue that actually reaches
        // someone. The price is per table, so the rest find it already charged.
        app(EndingBilling::class)->chargeOnce($accusation->game);
    }

    public function failed(\Throwable $exception): void
    {
        Accusation::where('id', $this->accusationId)
            ->whereNull('epilogue_sent_at')
            ->update(['epilogue_status' => Accusation::EPILOGUE_FAILED]);

        GameRevision::bump(Accusation::whereKey($this->accusationId)->value('game_id'), GameRevision::ACCUSATION);
    }
}
