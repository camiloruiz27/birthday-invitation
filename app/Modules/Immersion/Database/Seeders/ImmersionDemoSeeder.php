<?php

namespace App\Modules\Immersion\Database\Seeders;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Support\DefaultTimeline;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Partida de ejemplo para probar la linea de tiempo localmente. Usa la misma
 * linea de tiempo por defecto (ver App\Modules\Immersion\Support\DefaultTimeline)
 * que se adjunta automaticamente a cualquier partida creada desde el panel del GM.
 */
class ImmersionDemoSeeder extends Seeder
{
    public function run(): void
    {
        $game = Game::create([
            'name' => 'Partida de prueba - Steve Jacobs',
            'status' => 'draft',
        ]);

        foreach ($this->demoPlayers() as $player) {
            $game->players()->create([
                'name' => $player['name'],
                'email' => $player['email'],
                'access_token' => Str::uuid(),
            ]);
        }

        foreach (DefaultTimeline::events() as $event) {
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
