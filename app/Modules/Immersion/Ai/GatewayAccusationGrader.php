<?php

namespace App\Modules\Immersion\Ai;

use App\Modules\Immersion\Ai\Contracts\AccusationGrader;
use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Asks the "mystery-case" gateway to grade a table's accusations.
 *
 * What the model sees: each criterion with its authored reference answer and
 * its points, and each player's free-text answers, marked as data. What it
 * never sees: the culprit's verdict (scored exactly elsewhere) or anything
 * from the reveal beyond those reference answers.
 *
 * Whatever comes back is clamped to each criterion's points: a model that
 * returns 99 out of 15 does not get to decide the tie-break.
 */
class GatewayAccusationGrader implements AccusationGrader
{
    public function grade(Game $game): ?array
    {
        $case = $game->caseDefinition();
        $rubric = $case->accusationRubric();

        $accusations = $game->accusations()
            ->with('player:id,name')
            ->whereNotNull('suspect_slug')
            ->get();

        if ($rubric['criteria'] === [] || $accusations->isEmpty()) {
            return [];
        }

        $baseUrl = rtrim((string) config('immersion.ai.base_url'), '/');
        $projectId = (string) config('immersion.ai.project_id');
        $apiKey = (string) config('immersion.ai.api_key');

        $culprit = $case->culpritSlug();

        $payload = [
            'case_code' => $case->code(),
            'victim_name' => $case->victim()['name'],
            'incident' => $case->incident(),
            'culprit_name' => $culprit ? $case->suspect($culprit)['name'] : null,
            'criteria' => array_map(fn (array $criterion) => [
                'key' => $criterion['key'],
                'label' => $criterion['label'],
                'answer' => $criterion['answer'],
                'points' => $criterion['points'],
                'guidance' => $criterion['guidance'],
            ], $rubric['criteria']),
            'accusations' => $accusations->map(fn (Accusation $accusation) => [
                'id' => $accusation->id,
                'player_name' => $accusation->player?->name,
                'suspect_name' => $accusation->suspect_name,
                'answers' => array_merge(
                    ['weapon' => (string) $accusation->weapon, 'motive' => (string) $accusation->motive],
                    (array) ($accusation->answers ?? [])
                ),
            ])->values()->all(),
        ];

        try {
            $response = Http::timeout((int) config('immersion.ai.grading_timeout', 180))
                ->withHeaders([
                    'x-project-id' => $projectId,
                    'x-internal-api-key' => $apiKey,
                ])
                ->post("{$baseUrl}/api/projects/{$projectId}/grade", $payload);
        } catch (\Throwable $exception) {
            Log::warning('immersion_grading_exception', [
                'game_id' => $game->id,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('immersion_grading_failed_response', [
                'game_id' => $game->id,
                'status' => $response->status(),
            ]);

            return null;
        }

        $grid = $this->normalise((array) $response->json('results', []), $rubric['criteria'], $accusations->pluck('id')->all());

        if ($grid === null) {
            Log::warning('immersion_grading_empty', ['game_id' => $game->id]);
        }

        return $grid;
    }

    /**
     * Keeps only known accusations and known criteria, clamps every score to
     * the criterion's points and fills a criterion the model skipped with
     * zero, so the caller always gets a complete, trustworthy grid.
     *
     * Null when the model graded nobody at all: that is a failed grading, and
     * reporting it as "everyone scored zero" would rank the table on nothing.
     *
     * @param  array<int, mixed>  $results
     * @param  array<int, array<string, mixed>>  $criteria
     * @param  array<int, int>  $ids
     * @return array<int, array<string, array{points: int, feedback: string}>>|null
     */
    private function normalise(array $results, array $criteria, array $ids): ?array
    {
        $byId = [];

        foreach ($results as $result) {
            $id = (int) ($result['id'] ?? 0);

            if (! in_array($id, $ids, true)) {
                continue;
            }

            foreach ((array) ($result['scores'] ?? []) as $score) {
                $byId[$id][(string) ($score['key'] ?? '')] = $score;
            }
        }

        if ($byId === []) {
            return null;
        }

        $grid = [];

        foreach ($ids as $id) {
            foreach ($criteria as $criterion) {
                $score = $byId[$id][$criterion['key']] ?? [];

                $grid[$id][$criterion['key']] = [
                    'points' => max(0, min($criterion['points'], (int) ($score['points'] ?? 0))),
                    'feedback' => mb_substr(trim((string) ($score['feedback'] ?? '')), 0, 400),
                ];
            }
        }

        return $grid;
    }
}
