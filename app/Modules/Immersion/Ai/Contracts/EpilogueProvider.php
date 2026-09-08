<?php

namespace App\Modules\Immersion\Ai\Contracts;

use App\Modules\Immersion\Models\Accusation;

/**
 * Writes the message a player gets from the person they accused.
 *
 * The AI never decides the ending here either: who did it, why, and why each
 * innocent could not have, are all authored in the case manifest. This only
 * puts that material into a character's voice, aimed at what one player wrote.
 *
 * Returns null when it could not produce one, which the caller treats as "this
 * player gets the classic reveal and nothing else" — never as a reason to
 * withhold the ending.
 */
interface EpilogueProvider
{
    public function write(Accusation $accusation): ?string;
}
