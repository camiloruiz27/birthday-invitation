<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AccusationController extends Controller
{
    public function show(Player $player): Response
    {
        $player->load('accusation');

        return Inertia::render('Player/Accusation', [
            'player' => $player->revealCredentials(),
            'game' => $player->game,
            'unlocked' => $player->game->accusationsUnlocked(),
        ]);
    }

    public function store(Request $request, Player $player): RedirectResponse
    {
        abort_unless($player->game->accusationsUnlocked(), 403, 'Las acusaciones todavia no estan disponibles.');

        $data = $request->validate([
            'suspect_name' => ['required', 'string', 'max:255'],
            'motive' => ['required', 'string', 'max:2000'],
            'weapon' => ['required', 'string', 'max:255'],
        ]);

        $player->accusation()->updateOrCreate(
            ['player_id' => $player->id],
            [
                'game_id' => $player->game_id,
                'suspect_name' => $data['suspect_name'],
                'motive' => $data['motive'],
                'weapon' => $data['weapon'],
                'submitted_at' => Carbon::now(),
            ]
        );

        return redirect()
            ->route('immersion.player.accusation', $player->access_token)
            ->with('status', 'Tu acusacion quedo registrada. Puedes actualizarla mientras el Game Master no revele la solucion.');
    }
}
