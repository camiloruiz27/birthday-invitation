<?php

namespace App\Modules\Platform\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Finds out, against Bold's own API, how it treats a USD payment link.
 *
 * Bold's documentation says `currency` may be USD but not what unit
 * `total_amount` is in, or whether the payment-method limits (min 1000 for a
 * card) are numbers in the link's own currency — which would reject any USD
 * price under 1000. This creates a few UNPAID whole-dollar test links and
 * reports what Bold did with each, so BOLD_USD_AMOUNT_UNIT rests on an answer
 * instead of a guess.
 *
 * Nothing is charged: a link only costs something if someone pays it. Use the
 * sandbox keys; it refuses to run in production without --force.
 */
class ProbeBoldUsd extends Command
{
    protected $signature = 'platform:bold-probe-usd
                            {--force : Run even in production (creates unpaid links on the live account)}';

    protected $description = 'Create unpaid USD test links on Bold to learn if it accepts decimals and in what unit';

    /** What to send, and what it is meant to reveal. Whole dollars only: no cent is ever charged. */
    private const PROBES = [
        ['label' => 'USD 28 (un caso)', 'total' => 28],
        ['label' => 'USD 5 (la recarga corta; prueba el minimo)', 'total' => 5],
        ['label' => 'USD 1 (lo minimo que deja un codigo en porcentaje)', 'total' => 1],
    ];

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Esto crea links reales (sin pagar) en la cuenta de Bold. Usa llaves de sandbox o agrega --force.');

            return self::FAILURE;
        }

        $key = (string) config('platform.payments.identity_key');

        if ($key === '') {
            $this->error('Falta BOLD_IDENTITY_KEY (llave de identidad de sandbox).');

            return self::FAILURE;
        }

        $http = Http::baseUrl(rtrim((string) config('platform.payments.base_url'), '/'))
            ->withHeaders(['Authorization' => "x-api-key {$key}"])
            ->timeout((int) config('platform.payments.timeout'));

        $this->showLimits($http);

        $accepted = 0;

        foreach (self::PROBES as $probe) {
            $accepted += $this->probe($http, $probe['label'], $probe['total']) ? 1 : 0;
        }

        $this->newLine();
        $this->line('Que hacer ahora:');
        $this->line(' 1. Abre cada URL de arriba (NO pagues) y mira el monto: debe verse USD 28, USD 5 y USD 1.');
        $this->line('    - Si ves USD 0,28 / 0,05 / 0,01 la unidad son centavos: BOLD_USD_AMOUNT_UNIT=cents.');
        $this->line(' 2. En "Bold guardo", total y subtotal deben ser iguales (28 y 28.00).');
        $this->line(' 3. Si alguno fue rechazado por el minimo, Bold compara contra el limite de 1000 sin mirar la');
        $this->line('    moneda y las recargas en USD no se podran vender: hablalo con Bold antes de encender.');

        return $accepted === count(self::PROBES) ? self::SUCCESS : self::FAILURE;
    }

    private function showLimits(PendingRequest $http): void
    {
        try {
            $response = $http->get('/online/link/v1/payment_methods');
            $this->line('Limites por metodo de pago ('.$response->status().'): '.$this->short($response->body()));
        } catch (Throwable $exception) {
            $this->warn('No se pudieron leer los limites: '.$exception->getMessage());
        }

        $this->newLine();
    }

    private function probe(PendingRequest $http, string $label, int|float $total): bool
    {
        $this->info($label);

        try {
            $response = $http->post('/online/link/v1', [
                'amount_type' => 'CLOSE',
                'amount' => ['currency' => 'USD', 'tip_amount' => 0, 'total_amount' => $total],
                'reference' => 'PROBE'.strtoupper(bin2hex(random_bytes(6))),
                'description' => 'Prueba de moneda USD - NO PAGAR',
                'payment_methods' => ['CREDIT_CARD'],
            ]);
        } catch (Throwable $exception) {
            $this->error('  Fallo de conexion: '.$exception->getMessage());

            return false;
        }

        $id = $response->json('payload.payment_link');
        $url = $response->json('payload.url');

        if (! $response->successful() || ! is_string($id)) {
            $this->error("  Rechazado ({$response->status()}): ".$this->short($response->body()));

            return false;
        }

        $this->line("  Aceptado. URL: {$url}");

        // What Bold stored for it: if 10.5 comes back as 10 or 11, decimals are
        // being cut, whatever the checkout page shows.
        try {
            $stored = $http->get("/online/link/v1/{$id}");
            $this->line('  Bold guardo: '.$this->short($stored->body()));
        } catch (Throwable $exception) {
            $this->warn('  No se pudo leer de vuelta: '.$exception->getMessage());
        }

        return true;
    }

    private function short(string $body): string
    {
        return mb_strlen($body) > 400 ? mb_substr($body, 0, 400).'…' : $body;
    }
}
