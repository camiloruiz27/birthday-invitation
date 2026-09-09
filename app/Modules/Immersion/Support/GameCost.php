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
 * Nothing here scales with the size of the table, deliberately. The
 * interrogation cannot — a suspect belongs to the first player who really
 * questions them, so nine suspects at five questions is forty-five questions
 * whether three people play or eight. The endings could have, and do not:
 * inviting one more person to the table must never be a cost decision.
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
     * What a case is sold with: one full game, with the buyer free to pick
     * either premium ending.
     *
     * The priciest supported ending rather than a chosen one, so the choice is
     * real: quoting the cheaper would leave someone who wanted the other short
     * on their very first table. Derived rather than configured as a figure,
     * so a case with twelve suspects arrives with more without anyone
     * remembering to change a number.
     */
    public function maxForCase(CaseDefinition $case): int
    {
        $games = max(1, (int) config('immersion.credits.included_games', 1));

        $endings = array_intersect_key(
            $this->endingPrices(),
            array_flip($case->supportedEndings())
        );

        return $this->maxQuestions($case) * $this->questionCost() * $games
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
