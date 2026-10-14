<?php

namespace App\Modules\Immersion\Mail;

use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CaseTimelineMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TimelineEvent $event,
        public Player $player,
    ) {
    }

    /** Within `immersion.mail.attach_audio_max_kb` (0 means no limit). */
    private function fitsInEmail(string $path): bool
    {
        $maxKb = (int) config('immersion.mail.attach_audio_max_kb');

        return $maxKb <= 0 || (int) @filesize($path) <= $maxKb * 1024;
    }

    public function build(): self
    {
        $case = $this->player->game->caseDefinition();
        $code = $case->code();

        // La dirección sigue siendo la unica cuenta SMTP autenticada
        // (config('mail.from.address')) — eso nunca cambia. Solo el NOMBRE
        // para mostrar varia por caso, tomado de su "autoridad" en el
        // manifiesto (el mismo texto que ya aparece en el pie de página).
        // Cambiar solo el nombre no afecta SPF/DKIM ni cae en spam; cambiar
        // la dirección sí lo haría, y por eso esa nunca se toca.
        $mail = $this->subject(($code ? "[{$code}] " : '').$this->event->title)
            ->from(config('mail.from.address'), $case->authority() ?: config('mail.from.name'))
            ->view('immersion::mail.case-timeline')
            ->with([
                'event' => $this->event,
                'player' => $this->player,
                'caseCode' => $code,
                'caseAuthority' => $case->authority(),
                'interrogationQuestions' => $case->interrogationQuestions(),
                'bodyHtml' => $case->renderEventBody($this->event->source_file, $this->event->body_markdown, (int) $this->event->trigger_offset_minutes),
                'gallery' => $case->galleryFor($this->event->source_file),
                'assetPath' => fn (string $url) => $case->assetPath($url),
                'logoPath' => public_path('brand/isotipo-email.png'),
            ]);

        // Case audio ships with the case, generated audio lives in storage;
        // the event resolves whichever it is.
        //
        // A recording over the limit is linked, not attached: a multi-megabyte
        // attachment per player per envelope is what makes the mailbox slow and
        // eats the message size allowance, and the recording plays from the
        // player's inbox with one click. The view reads `audioLink` to say so.
        $audioLink = null;

        if ($path = $this->event->audioAbsolutePath()) {
            if ($this->fitsInEmail($path)) {
                $mime = $this->event->audioMimeType();
                $extension = $mime === 'audio/mpeg' ? 'mp3' : 'wav';

                $mail->attachData(
                    (string) file_get_contents($path),
                    'audio-'.$this->event->id.'.'.$extension,
                    ['mime' => $mime]
                );
            } else {
                $audioLink = route('immersion.player.inbox', $this->player->access_token);
            }
        }

        $mail->with('audioLink', $audioLink);

        return $mail;
    }
}
