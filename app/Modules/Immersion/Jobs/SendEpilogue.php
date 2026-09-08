<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Ai\Contracts\EpilogueProvider;
use App\Modules\Immersion\Mail\CaseEpilogueMail;
use App\Modules\Immersion\Models\Accusation;
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

    /** The gateway being briefly down is worth exactly one retry. */
    public int $tries = 2;

    public int $backoff = 30;

    public int $timeout = 120;

    public function __construct(public int $accusationId)
    {
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

        Mail::to($accusation->player->email)->send(new CaseEpilogueMail($accusation->fresh()));

        $accusation->update(['epilogue_sent_at' => now()]);
    }

    public function failed(\Throwable $exception): void
    {
        Accusation::where('id', $this->accusationId)
            ->whereNull('epilogue_sent_at')
            ->update(['epilogue_status' => Accusation::EPILOGUE_FAILED]);
    }
}
