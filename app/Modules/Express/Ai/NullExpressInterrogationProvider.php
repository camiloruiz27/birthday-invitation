<?php

namespace App\Modules\Express\Ai;

use App\Modules\Express\Support\ExpressCase;

/**
 * No AI: every question is "unavailable", so the room answers from the
 * author's own text. This is what runs when the gateway is not configured or
 * EXPRESS_AI_ENABLED is off.
 */
final class NullExpressInterrogationProvider implements ExpressInterrogationProvider
{
    public function available(): bool
    {
        return false;
    }

    public function ask(ExpressCase $case, array $suspect, array $history, string $question, ?string $topicId, array $evidence): string
    {
        throw new ExpressAiUnavailable('La IA no está activada para Express.');
    }
}
