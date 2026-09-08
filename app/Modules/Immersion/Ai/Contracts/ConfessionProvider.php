<?php

namespace App\Modules\Immersion\Ai\Contracts;

use App\Modules\Immersion\Models\Game;

/**
 * Personalises the culprit's authored confession with the table's own
 * interrogation questions, so the players hear their investigation in it.
 *
 * It never writes a confession. The script is authored in the case manifest
 * and comes back with one to three references woven in — or unchanged, which
 * is always a valid answer and is what happens when nobody questioned the
 * culprit, or when the AI is unavailable.
 */
interface ConfessionProvider
{
    public function script(Game $game): ?string;
}
