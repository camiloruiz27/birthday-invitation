<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\RevealEnding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AccusationController extends Controller
{
    public function show(Player $player): Response
    {
        $player->load('accusation');
        $game = $player->game;
        $case = $game->caseDefinition();

        return Inertia::render('Player/Accusation', [
            'player' => $player->revealCredentials(),
            'game' => $game,
            'unlocked' => $game->accusationsUnlocked(),
            'locked' => $game->accusationsLocked(),

            // Roster for the picker. Names and portraits only — this is the
            // same data the suspect board already shows, and it carries
            // nothing about who actually did it.
            'suspects' => collect($case->accusableSuspects())
                ->map(fn (array $suspect, string $slug) => [
                    'slug' => $slug,
                    'name' => $suspect['name'],
                    'role' => $suspect['role'],
                ])
                ->values(),

            'pending' => $game->pendingAccusationsCount(),
        ]);
    }

    public function store(Request $request, Player $player, RevealEnding $reveal): RedirectResponse
    {
        $game = $player->game;

        abort_unless($game->accusationsUnlocked(), 403, 'Las acusaciones todavia no estan disponibles.');
        abort_if($game->accusationsLocked(), 403, 'La fase de acusaciones ya se cerro.');

        $case = $game->caseDefinition();
        $roster = $case->accusableSuspects();

        $data = $request->validate([
            // A slug, not typed text: it is what makes scoring exact.
            'suspect_slug' => ['required', 'string', Rule::in(array_keys($roster))],
            'motive' => ['required', 'string', 'max:2000'],
            'weapon' => ['required', 'string', 'max:255'],
        ]);

        $player->accusation()->updateOrCreate(
            ['player_id' => $player->id],
            [
                'game_id' => $player->game_id,
                'suspect_slug' => $data['suspect_slug'],
                // Denormalised so old rows stay readable and the accusation
                // keeps the name the player actually saw.
                'suspect_name' => $roster[$data['suspect_slug']]['name'],
                'motive' => $data['motive'],
                'weapon' => $data['weapon'],
                'submitted_at' => Carbon::now(),
            ]
        );

        // The only moment "everyone has accused" can become true.
        $revealed = $reveal->attemptAutomatic($game->fresh());

        return redirect()
            ->route($revealed ? 'immersion.player.solution' : 'immersion.player.accusation', $player->access_token)
            ->with('status', $revealed
                ? 'Ya acusaron todos. Aqui esta la solucion del caso.'
                : 'Tu acusacion quedo registrada. Puedes cambiarla hasta que se revele la solucion.');
    }
}
