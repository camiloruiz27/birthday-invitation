<?php

namespace App\Modules\Platform\Console\Commands;

use App\Modules\Platform\Mail\CronDiagnosticMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * A round-trip test: proves the scheduler, the queue and the SMTP connection
 * all work together, and shows the reader the platform's actual email design
 * instead of a blank "it works" line.
 *
 * PURELY DIAGNOSTIC. It only runs on a schedule when explicitly turned on
 * (`platform.cron_diagnostic.enabled`, see PlatformServiceProvider), and it
 * exists to be switched back off the moment the cron is confirmed working.
 * Left on, it is one email every 7 minutes, forever, to one inbox.
 */
class SendCronDiagnosticMail extends Command
{
    protected $signature = 'platform:send-cron-diagnostic
                            {--to= : Override the configured recipient}';

    protected $description = 'Send a branded diagnostic email, to verify the cron and preview the mail design';

    /** Purely cosmetic — lets the reader see the emails arriving in order. */
    private const SEQUENCE_KEY = 'platform:cron-diagnostic-sequence';

    public function handle(): int
    {
        $to = $this->option('to') ?: config('platform.cron_diagnostic.email');

        if (! $to) {
            $this->error(
                'No hay destinatario. Pasa --to=correo@ejemplo.com o define '
                .'PLATFORM_CRON_DIAGNOSTIC_EMAIL en el .env.'
            );

            return self::FAILURE;
        }

        $sequence = Cache::increment(self::SEQUENCE_KEY);

        Mail::to($to)->send(new CronDiagnosticMail($sequence));

        $this->info("Enviado a {$to} (n.º {$sequence}).");

        return self::SUCCESS;
    }
}
