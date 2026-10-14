<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Ai\Contracts\AccusationGrader;
use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Immersion\Support\GameCost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Grades a revealed game's accusations and stores each player's score.
 *
 * The score is culprit points (exact: the verdict frozen at the reveal) plus
 * whatever the grader gave the free-text answers. It decides the order of the
 * scoreboard, so two players who both named the right culprit are separated
 * by how well they explained the rest.
 *
 * If grading cannot happen — AI off, gateway down, nothing usable back — every
 * accusation still gets its culprit points, marked as failed, so the table is
 * ranked on the one thing that is certain rather than left unranked.
 */
class GradeAccusations implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 30;

    /** Longer than the gateway's own grading timeout. */
    public int $timeout = 300;

    public function __construct(public int $gameId)
    {
        $this->onQueue((string) config('immersion.queues.slow'));
    }

    public function uniqueId(): string
    {
        return (string) $this->gameId;
    }

    public function handle(AccusationGrader $grader, AiCredits $credits, GameCost $cost): void
    {
        $game = Game::find($this->gameId);

        if (! $game) {
            return;
        }

        $pending = $game->accusations()->where('grading_status', Accusation::GRADING_PENDING)->get();

        if ($pending->isEmpty()) {
            return;
        }

        $price = $cost->gradingCost();

        // Pre-flight, like the paid endings: never produce something nobody
        // can pay for. The cost was frozen with the game, so this should pass.
        $grid = $credits->canSpend($game, $price) ? $grader->grade($game) : null;

        $rubric = $game->caseDefinition()->accusationRubric();

        DB::transaction(function () use ($pending, $grid, $rubric) {
            foreach ($pending as $accusation) {
                $this->store($accusation, $grid, $rubric);
            }
        });

        if ($grid !== null && $price > 0 && ! $credits->spend($game, $price, 'Calificación de acusaciones')) {
            Log::warning('immersion_grading_unbilled', ['game_id' => $game->id, 'credits' => $price]);
        }
    }

    /**
     * @param  array<int, array<string, array{points: int, feedback: string}>>|null  $grid
     * @param  array{culprit_points: int, criteria: array<int, array<string, mixed>>, max: int}  $rubric
     */
    private function store(Accusation $accusation, ?array $grid, array $rubric): void
    {
        $culpritPoints = $accusation->was_correct ? $rubric['culprit_points'] : 0;

        $breakdown = [[
            'key' => 'culprit',
            'label' => 'Culpable',
            'points' => $culpritPoints,
            'max' => $rubric['culprit_points'],
            'feedback' => null,
        ]];

        $graded = $grid !== null && $accusation->suspect_slug !== null && isset($grid[$accusation->id]);

        if ($graded) {
            foreach ($rubric['criteria'] as $criterion) {
                $score = $grid[$accusation->id][$criterion['key']] ?? ['points' => 0, 'feedback' => ''];

                $breakdown[] = [
                    'key' => $criterion['key'],
                    'label' => $criterion['label'],
                    'points' => $score['points'],
                    'max' => $criterion['points'],
                    'feedback' => $score['feedback'] !== '' ? $score['feedback'] : null,
                ];
            }
        }

        $accusation->update([
            'grading_status' => $graded || $rubric['criteria'] === [] ? Accusation::GRADING_READY : Accusation::GRADING_FAILED,
            'score' => array_sum(array_column($breakdown, 'points')),
            'grading' => $breakdown,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $game = Game::find($this->gameId);

        if (! $game) {
            return;
        }

        $culpritPoints = $game->caseDefinition()->accusationRubric()['culprit_points'];

        // Same fallback as an empty grading: rank on the culprit alone.
        foreach ($game->accusations()->where('grading_status', Accusation::GRADING_PENDING)->get() as $accusation) {
            $accusation->update([
                'grading_status' => Accusation::GRADING_FAILED,
                'score' => $accusation->was_correct ? $culpritPoints : 0,
            ]);
        }
    }
}
