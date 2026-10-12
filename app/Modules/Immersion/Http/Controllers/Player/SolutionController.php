<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Game;
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

        $board = $scoreboard->ranked($game);
        $form = $case->accusationForm();

        return Inertia::render('Player/Solution', [
            'player' => $player->revealCredentials(),
            'game' => $game->forPlayerView(),
            'solution' => $case->solution(),

            // Only names cross between players here, as everywhere else. The
            // detailed grading (and the extra answers) are each player's own:
            // everyone else sees a rank and a total.
            'scoreboard' => collect($board)->map(function (array $row) use ($player) {
                $isYou = $row['player_id'] === $player->id;

                return [
                    'player_name' => $row['player_name'],
                    'is_you' => $isYou,
                    'suspect_name' => $row['suspect_name'],
                    'weapon' => $row['weapon'],
                    'motive' => $row['motive'],
                    'correct' => $row['correct'],
                    'rank' => $row['rank'],
                    'score' => $row['score'],
                    'grading_status' => $row['grading_status'],
                    'grading' => $isYou ? $row['grading'] : [],
                    'answers' => $isYou ? $row['answers'] : [],
                ];
            })->values(),

            'maxScore' => $case->accusationRubric()['max'],
            'questions' => $form['questions'],

            'correctCount' => count(array_filter($board, fn ($row) => $row['correct'] === true)),
            'revealedBy' => $game->ending_revealed_by,

            // Their own epilogue, and only their own: it answers what THIS
            // person wrote, and reading someone else's would be reading their
            // accusation. It also arrives by email, so this is the copy that
            // survives a lost inbox.
            'epilogue' => fn () => $this->epilogueFor($player),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function epilogueFor(Player $player): ?array
    {
        if ($player->game->ending_type !== Game::ENDING_EPILOGUE) {
            return null;
        }

        $accusation = $player->accusation()->first();

        if (! $accusation || ! $accusation->epilogue_status) {
            return null;
        }

        return [
            'status' => $accusation->epilogue_status,
            'suspect_name' => $accusation->suspect_name,
            // Explicit opt-in; the column is hidden by default.
            'body' => $accusation->revealEpilogue()->epilogue_body,
        ];
    }
}
