<?php

namespace App\Modules\Immersion\Database\Seeders;

use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Sample game for exercising the timeline locally. It uses the same case
 * manifest that any game created from the Game Master panel uses, so the demo
 * cannot drift from the real thing.
 */
class ImmersionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $case = app(CaseRegistry::class)->default();

        $game = Game::create([
            'name' => 'Partida de prueba - '.$case->name(),
            'case_slug' => $case->slug,
            'case_version' => $case->version(),
            'status' => 'draft',
        ]);

        foreach ($this->demoPlayers() as $player) {
            $game->players()->create([
                'name' => $player['name'],
                'email' => $player['email'],
                'access_token' => Str::uuid(),
            ]);
        }

        foreach ($case->timeline() as $event) {
            $game->timelineEvents()->create($event);
        }
    }

    private function demoPlayers(): array
    {
        return [
            ['name' => 'Jugador 1', 'email' => 'jugador1@example.test'],
            ['name' => 'Jugador 2', 'email' => 'jugador2@example.test'],
            ['name' => 'Jugador 3', 'email' => 'jugador3@example.test'],
            ['name' => 'Jugador 4', 'email' => 'jugador4@example.test'],
            ['name' => 'Jugador 5', 'email' => 'jugador5@example.test'],
            ['name' => 'Jugador 6', 'email' => 'jugador6@example.test'],
        ];
    }
}
