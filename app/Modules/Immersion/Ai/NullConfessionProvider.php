<?php

namespace App\Modules\Immersion\Ai;

use App\Modules\Immersion\Ai\Contracts\ConfessionProvider;
use App\Modules\Immersion\Models\Game;

/**
 * No AI: the authored confession, exactly as written.
 *
 * Unlike the epilogue, there is a real thing to fall back to here — the script
 * is a complete confession on its own, written to be played as-is. The table
 * loses the personal references, not the ending.
 */
class NullConfessionProvider implements ConfessionProvider
{
    public function script(Game $game): ?string
    {
        return $game->caseDefinition()->confessionScript();
    }
}
