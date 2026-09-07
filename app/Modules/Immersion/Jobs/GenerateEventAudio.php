<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Ai\Contracts\SpeechProvider;
use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\TimelineRecipients;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Generates a timeline event's missing audio and resends its email.
 *
 * Text-to-speech can take most of a minute, which is far too long to hold a
 * browser request open — so the Game Master's "retry audio" button enqueues
 * this and returns immediately, and the console polls for the result.
 *
 * ShouldBeUnique keeps a Game Master pressing the button repeatedly from
 * queueing the same expensive generation several times over.
 */
class GenerateEventAudio implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Two attempts: the gateway being briefly down is worth one retry. */
    public int $tries = 2;

    public int $backoff = 30;

    /** Comfortably longer than the gateway's own timeout. */
    public int $timeout = 180;

    public function __construct(public int $eventId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->eventId;
    }

    public function handle(SpeechProvider $speech): void
    {
        $event = TimelineEvent::find($this->eventId);

        if (! $event || ! $event->isAudio() || ! $event->audio_script) {
            return;
        }

        if ($event->audio_path) {
            $event->update(['audio_status' => TimelineEvent::AUDIO_READY]);

            return;
        }

        $path = $speech->synthesize($event->id, (string) $event->audio_script);

        if (! $path) {
            // Not an exception: the provider already decided it could not
            // produce audio, and throwing would only burn the retry on a
            // failure that is not transient.
            $event->update(['audio_status' => TimelineEvent::AUDIO_FAILED]);

            return;
        }

        $event->update([
            'audio_path' => $path,
            'audio_status' => TimelineEvent::AUDIO_READY,
        ]);

        // The email already went out without its attachment, so resend it now
        // that there is one.
        if ($event->isSent()) {
            foreach (TimelineRecipients::resolve($event) as $recipient) {
                try {
                    Mail::to($recipient->email)->send(new CaseTimelineMail($event, $recipient));
                } catch (\Throwable $exception) {
                    // One bad address must not stop the rest.
                    report($exception);
                }
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        TimelineEvent::where('id', $this->eventId)
            ->update(['audio_status' => TimelineEvent::AUDIO_FAILED]);
    }
}
