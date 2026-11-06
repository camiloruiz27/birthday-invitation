<?php

namespace App\Modules\Platform\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The password-reset notification's presentation layer.
 *
 * The token and its validation remain Laravel's responsibility. Receiving a
 * ready-made reset URL here prevents this visual layer from ever handling or
 * reconstructing a password-reset token itself.
 */
class AccountPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $resetUrl,
        public int $expiresInMinutes,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Restablece el acceso a tu expediente')
            ->view('emails.account.password-reset')
            ->text('emails.account.password-reset-text')
            ->with([
                'name' => $this->name,
                'actionUrl' => $this->resetUrl,
                'expiresInMinutes' => $this->expiresInMinutes,
                'logoPath' => public_path('brand/isotipo-email.png'),
            ]);
    }
}
