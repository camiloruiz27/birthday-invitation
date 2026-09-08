<?php

namespace App\Modules\Immersion\Console\Commands;

use App\Modules\Immersion\Models\CreditHold;
use App\Modules\Immersion\Support\AiCredits;
use Illuminate\Console\Command;

/**
 * Gives back credits frozen by games nobody closed or paused.
 *
 * The safety net, not the main path: pausing a game already returns its
 * capacity, which is what a table continuing another day should do. This only
 * catches games left running. Without it, a handful of forgotten games quietly
 * freeze an account's whole balance and the only visible symptom is "no puedo
 * iniciar partidas" with credits apparently sitting in the wallet.
 *
 * It releases the hold and leaves the game alone: closing someone's case from a
 * cron job would hide the ending from a table that might still come back. The
 * game's interrogation stops working, which is the honest outcome once the
 * capacity is back in the wallet — and its console offers a one-click re-arm
 * that re-freezes only what the game has left.
 */
class ReleaseStaleCreditHolds extends Command
{
    protected $signature = 'immersion:release-stale-holds
                            {--hours= : Override the inactivity window}
                            {--dry-run : List what would be released without touching anything}';

    protected $description = 'Release AI credit reservations from games that have been inactive for too long';

    public function handle(AiCredits $credits): int
    {
        $hours = (int) ($this->option('hours') ?: config('immersion.credits.stale_hold_hours', 48));

        if ($hours < 1) {
            $this->error('El plazo tiene que ser de al menos una hora.');

            return self::FAILURE;
        }

        // updated_at moves every time the hold is charged, so it doubles as
        // "last time this game used any AI". A game created and never played
        // keeps its creation timestamp, which is the intent.
        $stale = CreditHold::query()
            ->whereNull('released_at')
            ->where('updated_at', '<', now()->subHours($hours))
            ->with('game:id,name,status')
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No hay reservas vencidas.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');
        $total = 0;

        foreach ($stale as $hold) {
            $name = $hold->game?->name ?? "partida #{$hold->game_id}";
            $remaining = $hold->remaining();

            if ($dryRun) {
                $this->line("  [simulacion] {$name}: devolveria {$remaining} creditos");
                $total += $remaining;

                continue;
            }

            $released = $credits->release($hold, "Reserva liberada tras {$hours}h sin actividad: \"{$name}\"");
            $total += $released;

            $this->line("  {$name}: {$released} creditos devueltos");
        }

        $verb = $dryRun ? 'Se devolverian' : 'Devueltos';
        $this->info("{$verb} {$total} creditos de {$stale->count()} reservas.");

        return self::SUCCESS;
    }
}
