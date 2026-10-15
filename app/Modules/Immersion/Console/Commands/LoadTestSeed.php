<?php

namespace App\Modules\Immersion\Console\Commands;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Support\GameEraser;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Creates running games with players for the load test (tests/Load), and removes
 * them afterwards.
 *
 * The load test needs a table's worth of real tokens to play with. Making them by
 * hand is hopeless at fifty games, so this makes them and writes the tokens where
 * the k6 script reads them. Everything it creates is named "LOAD-TEST n", which is
 * how --purge finds it again.
 *
 * Meant for the TEST site. It refuses to run in production: fifty fake games and
 * two hundred fake players in the real database is not a thing to do by accident.
 */
class LoadTestSeed extends Command
{
    protected $signature = 'immersion:load-seed
                            {--games=10 : How many games to create}
                            {--players=4 : Players per game}
                            {--case=habitacion-314 : Case slug the games play}
                            {--minutes=40 : How far into the case each game already is}
                            {--output=storage/app/load-tokens.json : Where to write the tokens, relative to the project}
                            {--purge : Delete every LOAD-TEST game instead of creating any}
                            {--force : Allow it in production}';

    protected $description = 'Crea partidas y jugadores de prueba para la prueba de carga (solo sitio de test), o los borra con --purge';

    private const PREFIX = 'LOAD-TEST ';

    public function handle(GameEraser $eraser): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Esto crea partidas falsas en la base de datos: no se ejecuta en producción (usa --force si de verdad es lo que quieres).');

            return self::FAILURE;
        }

        if ($this->option('purge')) {
            return $this->purge($eraser);
        }

        $games = max(1, (int) $this->option('games'));
        $players = max(1, min(12, (int) $this->option('players')));
        $caseSlug = (string) $this->option('case');
        $minutes = max(0, (int) $this->option('minutes'));

        $tokens = [];

        foreach (range(1, $games) as $number) {
            $game = Game::create([
                'name' => self::PREFIX.$number,
                'case_slug' => $caseSlug,
                'status' => 'running',
                'interrogation_enabled' => true,
                'started_at' => now()->subMinutes($minutes),
                'paused_seconds_total' => 0,
            ]);

            $events = $game->caseDefinition()->timeline();

            foreach ($events as $event) {
                $game->timelineEvents()->create($event);
            }

            // What the case would have sent by now.
            $game->timelineEvents()
                ->where('trigger_offset_minutes', '<=', $minutes)
                ->get()
                ->each(fn ($event) => $event->update(['sent_at' => now()->subMinutes(max(0, $minutes - (int) $event->trigger_offset_minutes))]));

            foreach (range(1, $players) as $index) {
                $token = Str::lower(Str::random(40));

                $game->players()->create([
                    'name' => "Carga {$number}-{$index}",
                    'email' => "carga-{$number}-{$index}@example.invalid",
                    'access_token' => $token,
                    // Past the privacy notice, so the load test is not stopped by it.
                    'privacy_accepted_at' => now(),
                    'privacy_version' => config('legal.privacy_version'),
                ]);

                $tokens[] = ['game' => $game->id, 'token' => $token];
            }
        }

        $path = base_path((string) $this->option('output'));

        @mkdir(dirname($path), 0777, true);
        file_put_contents($path, json_encode(['case' => $caseSlug, 'players' => $tokens], JSON_PRETTY_PRINT));

        $this->info(sprintf('%d partidas, %d jugadores. Tokens en %s', $games, count($tokens), $path));
        $this->line('Cuando termines: php artisan immersion:load-seed --purge');

        return self::SUCCESS;
    }

    private function purge(GameEraser $eraser): int
    {
        $games = Game::query()->where('name', 'like', self::PREFIX.'%')->get();

        foreach ($games as $game) {
            $eraser->erase($game, 'Prueba de carga');
        }

        $path = base_path((string) $this->option('output'));

        if (is_file($path)) {
            @unlink($path);
        }

        $this->info("{$games->count()} partida(s) de prueba borradas.");

        return self::SUCCESS;
    }
}
