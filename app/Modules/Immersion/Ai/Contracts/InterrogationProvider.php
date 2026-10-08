<?php

namespace App\Modules\Immersion\Ai\Contracts;

use App\Modules\Immersion\Models\InterrogationSession;

/**
 * Answers a player's question in a suspect's voice.
 *
 * Two real implementations, which is why this is an interface and not just a
 * class: the gateway one, and a null one for running a case with the AI
 * mechanics switched off.
 *
 * A reply is always a real, billable answer, including the neutral in-character
 * one the null provider gives. When the gateway cannot answer at all, the
 * provider throws InterrogationUnavailable instead of faking a reply, so the
 * caller can give the question back and not charge for it.
 */
interface InterrogationProvider
{
    /**
     * @throws \App\Modules\Immersion\Ai\InterrogationUnavailable
     */
    public function ask(InterrogationSession $session, string $question): string;
}
