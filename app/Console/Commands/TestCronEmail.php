<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestCronEmail extends Command
{
    protected $signature = 'cron:test-email';

    protected $description = 'Send a test email every 5 minutes to verify cron is working on Hostinger';

    public function handle(): int
    {
        $to = 'camiruiza27@gmail.com';
        $subject = 'Prueba de Cron - ' . now()->format('Y-m-d H:i:s');
        
        try {
            Mail::raw(
                "Este es un correo de prueba enviado por el cron de tu servidor.\n\n" .
                "Hora de envío: " . now()->format('Y-m-d H:i:s') . "\n" .
                "Zona horaria: " . config('app.timezone') . "\n\n" .
                "Si estás recibiendo este correo cada 5 minutos, tu cron está funcionando correctamente.",
                function ($message) use ($to, $subject) {
                    $message->to($to)
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
                }
            );

            $this->info("✓ Correo de prueba enviado exitosamente a {$to}");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Error al enviar el correo: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
