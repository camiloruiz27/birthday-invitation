<?php

namespace App\Modules\Platform\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * The promotional email that follows a person's first closed game, carrying
 * their private discount code. See Actions\FirstGameOffer.
 *
 * Promotional, unlike the account mail next to it: it says why it was sent,
 * and it carries the way out — a visible link and the List-Unsubscribe header
 * that mail clients turn into their own "unsubscribe" button.
 */
class FirstGameOfferMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $code,
        public int $percent,
        public Carbon $expiresAt,
        public string $catalogUrl,
        public string $unsubscribeUrl,
    ) {
    }

    public function build(): self
    {
        $unsubscribeUrl = $this->unsubscribeUrl;

        return $this
            ->subject("Tu {$this->percent}% de descuento en el próximo caso — MisterioCode")
            ->view('emails.offers.first-game')
            ->text('emails.offers.first-game-text')
            ->withSymfonyMessage(function ($message) use ($unsubscribeUrl) {
                $message->getHeaders()->addTextHeader('List-Unsubscribe', "<{$unsubscribeUrl}>");
            })
            ->with([
                'name' => $this->name,
                'code' => $this->code,
                'percent' => $this->percent,
                'expiresOn' => $this->expiresAt->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
                'catalogUrl' => $this->catalogUrl,
                'unsubscribeUrl' => $this->unsubscribeUrl,
                'logoPath' => public_path('brand/isotipo-email.png'),
            ]);
    }
}
