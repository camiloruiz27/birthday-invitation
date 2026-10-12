<?php

namespace App\Modules\Immersion\Ai;

use App\Modules\Immersion\Ai\Contracts\AccusationGrader;
use App\Modules\Immersion\Models\Game;

/**
 * No AI: no grading. The scoreboard still ranks by the culprit, which is
 * exact and needs no model.
 */
class NullAccusationGrader implements AccusationGrader
{
    public function grade(Game $game): ?array
    {
        return null;
    }
}
