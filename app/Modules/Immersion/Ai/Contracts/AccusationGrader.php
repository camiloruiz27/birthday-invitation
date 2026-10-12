<?php

namespace App\Modules\Immersion\Ai\Contracts;

use App\Modules\Immersion\Models\Game;

/**
 * Grades every accusation of a revealed game against the authored solution.
 *
 * One call for the whole table, not one per player: the score is a
 * tie-breaker between players, so it only means something if the same reader
 * marked every answer by the same standard.
 *
 * The culprit is NOT graded here — that is an exact comparison of slugs, done
 * by the scoreboard. This only reads the free-text answers.
 */
interface AccusationGrader
{
    /**
     * Points and a one-line comment per criterion, keyed by accusation id and
     * then by criterion key. Null when no grading could be produced.
     *
     * @return array<int, array<string, array{points: int, feedback: string}>>|null
     */
    public function grade(Game $game): ?array;
}
