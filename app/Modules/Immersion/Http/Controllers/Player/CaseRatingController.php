<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\CaseRating;
use App\Modules\Immersion\Models\Player;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CaseRatingController extends Controller
{
    public function store(Request $request, Player $player): RedirectResponse
    {
        $game = $player->game;

        abort_unless($game->endingRevealed() || $game->isFinished(), 403);

        $data = $request->validate([
            // 2 is one star, 10 is five. The integer representation makes
            // every permitted half-star exact in every database engine.
            'rating_half_steps' => ['required', 'integer', 'min:2', 'max:10'],
            'tags' => ['nullable', 'array', 'max:3'],
            'tags.*' => ['required', 'string', 'distinct', Rule::in(array_keys(CaseRating::TAGS))],
        ]);

        $existing = CaseRating::query()
            ->where('game_id', $game->id)
            ->where('player_id', $player->id)
            ->first();

        if ($existing) {
            return back()->with('status', 'Tu valoración ya fue registrada.');
        }

        try {
            CaseRating::create([
                'case_slug' => $game->case_slug,
                'game_id' => $game->id,
                'player_id' => $player->id,
                'rating_half_steps' => $data['rating_half_steps'],
                'tags' => array_values($data['tags'] ?? []),
            ]);
        } catch (QueryException) {
            // The unique index is the final guard if two clicks arrive at the
            // same time. Both requests still lead the player to the same state.
            return back()->with('status', 'Tu valoración ya fue registrada.');
        }

        return back()->with('status', 'Gracias por valorar este caso.');
    }
}
