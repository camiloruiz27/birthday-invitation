<?php

namespace App\Modules\Platform\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * The account-verification notification's presentation layer.
 *
 * Laravel's VerifyEmail notification continues to create and validate the
 * signed URL. This mailable only receives that already-safe URL so account
 * security and the email's visual design remain deliberately separate.
 */
class AccountVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $verificationUrl,
        public int $expiresInMinutes,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('Tu expediente está listo — confirma tu correo')
            ->view('emails.account.verification')
            ->text('emails.account.verification-text')
            ->with([
                'name' => $this->name,
                'actionUrl' => $this->verificationUrl,
                'expiresInMinutes' => $this->expiresInMinutes,
                'logoPath' => public_path('brand/isotipo-email.png'),
            ]);
    }
}
