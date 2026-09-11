<?php

namespace App\Modules\Platform\Mail;

use App\Modules\Platform\Support\CronDiagnostics;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * A branded round trip: if this reaches an inbox, the scheduler, the queue
 * and the SMTP connection all worked end to end — and the reader gets to
 * look at the platform's own email design at the same time, which nothing
 * else in the codebase currently shows: the account emails (verification,
 * password reset) still ride Laravel's stock markdown theme, not this one.
 *
 * PURELY DIAGNOSTIC. See SendCronDiagnosticMail's docblock before leaving
 * this switched on.
 */
class CronDiagnosticMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public int $sequence)
    {
    }

    public function build(): self
    {
        $diagnostics = CronDiagnostics::gather();

        $rows = [
            [
                'label' => 'Latido del scheduler',
                'value' => $diagnostics['heartbeat_ago_minutes'] === null
                    ? 'nunca'
                    : "hace {$diagnostics['heartbeat_ago_minutes']} min",
            ],
            ['label' => 'Eventos vencidos', 'value' => (string) $diagnostics['overdue_events']],
            ['label' => 'Driver de cola', 'value' => $diagnostics['queue_driver']],
            [
                'label' => 'Trabajos en cola',
                'value' => $diagnostics['queue_waiting'] === null ? '—' : (string) $diagnostics['queue_waiting'],
            ],
            [
                'label' => 'Trabajos fallidos',
                'value' => $diagnostics['queue_failed'] === null ? '—' : (string) $diagnostics['queue_failed'],
            ],
            ['label' => 'Entorno', 'value' => $diagnostics['environment']],
            ['label' => 'Modo depuración', 'value' => $diagnostics['debug'] ? 'activado' : 'desactivado'],
        ];

        return $this
            ->subject("Diagnóstico del cron #{$this->sequence} — MisterioCode")
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('emails.cron-diagnostic')
            ->with([
                'sequence' => $this->sequence,
                'sentAt' => now(),
                'rows' => $rows,
                // Not $this->embed(): the message object that turns a path
                // into a "cid:" reference only exists once Laravel builds the
                // real Swift/Symfony message, which happens while the view
                // renders — the same reason case-timeline.blade.php embeds
                // its own images via $message->embed() rather than in build().
                'logoPath' => public_path('brand/isotipo.png'),
            ]);
    }
}
