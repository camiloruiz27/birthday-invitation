<?php

namespace App\Modules\Immersion\Mail;

use App\Modules\Immersion\Models\Player;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The player's own access link, sent on demand from the Game Master console
 * instead of copy-pasted by hand.
 */
class PlayerAccessLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Player $player)
    {
    }

    public function build(): self
    {
        $case = $this->player->game->caseDefinition();
        $code = $case->code();

        // Same reasoning as CaseTimelineMail: the authenticated SMTP address
        // never changes, only the display name, taken from the case's own
        // authority so this arrives looking like it came from the case.
        return $this->subject(($code ? "[{$code}] " : '').'Tu acceso al caso')
            ->from(config('mail.from.address'), $case->authority() ?: config('mail.from.name'))
            ->view('immersion::mail.player-access-link')
            ->with([
                'player' => $this->player,
                'caseCode' => $code,
                'caseName' => $case->name(),
                'caseAuthority' => $case->authority(),
                'inboxUrl' => route('immersion.player.inbox', $this->player->access_token),
                'logoPath' => public_path('brand/isotipo-email.png'),
            ]);
    }
}
