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

        // Sin ->from(...): usa MAIL_FROM_ADDRESS/MAIL_FROM_NAME del .env (el remitente
        // real configurado), para que coincida con la cuenta SMTP autenticada y no
        // termine en spam o rechazado por no coincidir con el dominio verificado.
        $mail = $this->subject(($code ? "[{$code}] " : '').$this->event->title)
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
            ]);

        // Case audio ships with the case, generated audio lives in storage;
        // the event resolves whichever it is.
        if ($path = $this->event->audioAbsolutePath()) {
            $mail->attachData(
                (string) file_get_contents($path),
                'audio-'.$this->event->id.'.wav',
                ['mime' => 'audio/wav']
            );
        }

        return $mail;
    }
}
