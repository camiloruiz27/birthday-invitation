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
                'bodyHtml' => $case->renderEventBody($this->event->source_file, $this->event->body_markdown),
                'gallery' => $case->galleryFor($this->event->source_file),
                'assetPath' => fn (string $url) => $case->assetPath($url),
                'logoPath' => public_path('brand/isotipo-email.png'),
            ]);

        // Case audio ships with the case, generated audio lives in storage;
        // the event resolves whichever it is.
        if ($path = $this->event->audioAbsolutePath()) {
            $mime = $this->event->audioMimeType();
            $extension = $mime === 'audio/mpeg' ? 'mp3' : 'wav';

            $mail->attachData(
                (string) file_get_contents($path),
                'audio-'.$this->event->id.'.'.$extension,
                ['mime' => $mime]
            );
        }

        return $mail;
    }
}
