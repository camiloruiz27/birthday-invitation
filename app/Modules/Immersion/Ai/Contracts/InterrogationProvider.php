<?php

namespace App\Modules\Immersion\Ai\Contracts;

use App\Modules\Immersion\Models\InterrogationSession;

/**
 * Answers a player's question in a suspect's voice.
 *
 * Two real implementations, which is why this is an interface and not just a
 * class: the gateway one, and a null one for running a case with the AI
 * mechanics switched off. A provider never throws — a failed turn degrades to
 * a neutral in-character reply so the investigation keeps going.
 */
interface InterrogationProvider
{
    public function ask(InterrogationSession $session, string $question): string;
}
