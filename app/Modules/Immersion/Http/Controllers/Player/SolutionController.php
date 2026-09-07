<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\AccusationScoreboard;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The ending, as the player sees it.
 *
 * There is no policy here because a player has no account — the access token
 * in the URL is their whole credential, exactly as for their inbox. What
 * guards the answer is the reveal check below, and it is the single line
 * standing between a curious player and the solution.
 */
class SolutionController extends Controller
{
    public function show(Player $player, AccusationScoreboard $scoreboard): Response
    {
        $game = $player->game;
        $case = $game->caseDefinition();

        abort_unless($case->hasSolution(), 404);
        abort_unless($game->endingRevealed(), 403, 'La solucion todavia no se ha revelado.');

        $board = $scoreboard->for($game);

        return Inertia::render('Player/Solution', [
            'player' => $player->revealCredentials(),
            'game' => $game,
            'solution' => $case->solution(),

            // Only names cross between players here, as everywhere else.
            'scoreboard' => collect($board)->map(fn (array $row) => [
                'player_name' => $row['player_name'],
                'is_you' => $row['player_id'] === $player->id,
                'suspect_name' => $row['suspect_name'],
                'weapon' => $row['weapon'],
                'motive' => $row['motive'],
                'correct' => $row['correct'],
            ])->values(),

            'correctCount' => count(array_filter($board, fn ($row) => $row['correct'] === true)),
            'revealedBy' => $game->ending_revealed_by,
        ]);
    }
}
