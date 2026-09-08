<?php

namespace App\Modules\Immersion\Ai;

use App\Modules\Immersion\Ai\Contracts\EpilogueProvider;
use App\Modules\Immersion\Models\Accusation;

/**
 * No AI: no epilogue.
 *
 * Returning null rather than a canned paragraph is the point. A generic "no
 * pude haber sido yo" sent to every player would read as the mechanic working
 * badly; nothing at all leaves the classic reveal standing on its own, which
 * is a complete ending.
 */
class NullEpilogueProvider implements EpilogueProvider
{
    public function write(Accusation $accusation): ?string
    {
        return null;
    }
}
