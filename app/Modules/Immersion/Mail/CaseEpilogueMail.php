<?php

namespace App\Modules\Immersion\Mail;

use App\Modules\Immersion\Models\Accusation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The epilogue: a message from the person this player accused.
 *
 * Deliberately not styled as another case file. The investigation is over and
 * this is not evidence — it is someone writing back, so it arrives looking like
 * a personal message rather than a police envelope.
 */
class CaseEpilogueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Accusation $accusation)
    {
    }

    public function build(): self
    {
        $case = $this->accusation->game->caseDefinition();
        $suspect = $case->suspect($this->accusation->suspect_slug);
        $name = $suspect['name'] ?? $this->accusation->suspect_name;

        // No ->from(...): uses the configured MAIL_FROM_* so the sender matches
        // the authenticated SMTP account, as with every other case email.
        return $this->subject("Un mensaje de {$name}")
            ->view('immersion::mail.case-epilogue')
            ->with([
                'accusation' => $this->accusation,
                'player' => $this->accusation->player,
                'suspectName' => $name,
                'suspectPhotoUrl' => $suspect['photo_url'] ?? null,
                'body' => $this->accusation->epilogue_body,
                'wasCorrect' => (bool) $this->accusation->was_correct,
                'solutionUrl' => route(
                    'immersion.player.solution',
                    $this->accusation->player->access_token
                ),
                'assetPath' => fn (string $url) => $case->assetPath($url),
            ]);
    }
}
