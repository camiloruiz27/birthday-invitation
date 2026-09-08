<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Models\Game;

/**
 * What a game could cost in AI credits, at most.
 *
 * Pure calculation, no wallet and no side effects. It answers one question —
 * "what has to be frozen before this can start?" — and it has to answer it the
 * same way for the creation form (a preview) and for the start button (the
 * charge), or the Game Master is quoted one number and billed another.
 *
 * The interrogation ceiling does NOT depend on how many people are playing. A
 * suspect belongs to the first player who really questions them, so the whole
 * table shares one budget per suspect: nine suspects at five questions is
 * forty-five questions whether three people play or eight.
 */
class GameCost
{
    public function enabled(): bool
    {
        return (bool) config('immersion.credits.enabled', true);
    }

    /**
     * The ceiling for a game as configured, broken down so the interface can
     * explain the number instead of just presenting it.
     *
     * @return array{interrogation: int, ending: int, total: int, questions: int}
     */
    public function for(Game $game): array
    {
        return $this->estimate(
            $game->caseDefinition(),
            (bool) $game->interrogation_enabled,
            (string) $game->ending_type
        );
    }

    /**
     * @return array{interrogation: int, ending: int, total: int, questions: int}
     */
    public function estimate(CaseDefinition $case, bool $interrogation, string $endingType): array
    {
        $questions = $interrogation ? $this->maxQuestions($case) : 0;

        $interrogationCost = $questions * $this->questionCost();
        $endingCost = $this->endingCost($endingType);

        return [
            'questions' => $questions,
            'interrogation' => $interrogationCost,
            'ending' => $endingCost,
            'total' => $interrogationCost + $endingCost,
        ];
    }

    /**
     * The most expensive way this case can possibly be played: every question
     * asked, and the priciest ending it can actually deliver.
     *
     * This is what a case is sold with. Buying one has to arrive playable to
     * the limit — a number that depends on the case (nine suspects at five
     * questions is not the same as twelve at six), so it is derived here rather
     * than written into config as a figure that silently stops matching the
     * next case.
     */
    public function maxForCase(CaseDefinition $case): int
    {
        $endings = array_intersect_key(
            $this->endingPrices(),
            array_flip($case->supportedEndings())
        );

        return $this->maxQuestions($case) * $this->questionCost()
            + ($endings ? max($endings) : 0);
    }

    /**
     * Every question the table could possibly ask: one budget per interrogable
     * person, spent by whoever claims them.
     *
     * Zero for a case that does not have the mechanic at all — there is no
     * budget to reserve for something the case cannot do.
     */
    public function maxQuestions(CaseDefinition $case): int
    {
        if (! $case->hasMechanic('interrogation')) {
            return 0;
        }

        return count($case->suspects()) * $case->interrogationQuestions();
    }

    public function questionCost(): int
    {
        return (int) config('immersion.credits.costs.question', 1);
    }

    /**
     * Unknown ending types cost nothing rather than throwing: an ending this
     * build does not price yet must not make a game unstartable.
     */
    public function endingCost(string $endingType): int
    {
        return (int) (config('immersion.credits.costs.ending')[$endingType] ?? 0);
    }

    /**
     * Every ending this build offers, priced, for the creation form.
     *
     * @return array<string, int>
     */
    public function endingPrices(): array
    {
        return array_map('intval', (array) config('immersion.credits.costs.ending', []));
    }
}
