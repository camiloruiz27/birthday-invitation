<?php

namespace App\Modules\Immersion\Mail;

use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\CaseFileReader;
use App\Modules\Immersion\Support\CaseGallery;
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
        // Sin ->from(...): usa MAIL_FROM_ADDRESS/MAIL_FROM_NAME del .env (el remitente
        // real configurado), para que coincida con la cuenta SMTP autenticada y no
        // termine en spam o rechazado por no coincidir con el dominio verificado.
        $mail = $this->subject('[SF 554301] '.$this->event->title)
            ->view('immersion::mail.case-timeline')
            ->with([
                'event' => $this->event,
                'player' => $this->player,
                'bodyHtml' => $this->renderBody(),
                'gallery' => $this->event->source_file
                    ? CaseGallery::forSourceFile($this->event->source_file)
                    : [],
            ]);

        if ($this->event->audio_path && Storage::disk('local')->exists($this->event->audio_path)) {
            $mail->attachData(
                Storage::disk('local')->get($this->event->audio_path),
                'audio-'.$this->event->id.'.wav',
                ['mime' => 'audio/wav']
            );
        }

        return $mail;
    }

    private function renderBody(): string
    {
        if ($this->event->source_file) {
            return CaseFileReader::renderFileExcludingSections(
                $this->event->source_file,
                CaseGallery::excludedHeadings($this->event->source_file)
            );
        }

        return CaseFileReader::renderMarkdown((string) $this->event->body_markdown);
    }
}
