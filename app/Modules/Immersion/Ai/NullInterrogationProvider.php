<?php

namespace App\Modules\Immersion\Ai;

use App\Modules\Immersion\Ai\Contracts\InterrogationProvider;
use App\Modules\Immersion\Models\InterrogationSession;

/**
 * The no-AI experience.
 *
 * Every suspect deflects in character. The case is still playable: the
 * envelopes, the evidence and the written testimonies carry it, and the
 * transcript still reveals the official statement when the session closes.
 */
class NullInterrogationProvider implements InterrogationProvider
{
    public const REPLY = 'Ya dije todo lo que se sobre eso. No tengo nada mas que agregar a lo que ya declare.';

    public function ask(InterrogationSession $session, string $question): string
    {
        return self::REPLY;
    }
}
