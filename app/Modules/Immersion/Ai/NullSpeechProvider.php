<?php

namespace App\Modules\Immersion\Ai;

use App\Modules\Immersion\Ai\Contracts\SpeechProvider;

/**
 * No text-to-speech. Voice-note events still arrive as ordinary emails; they
 * simply carry no recording.
 */
class NullSpeechProvider implements SpeechProvider
{
    public function synthesize(string $key, string $script, ?string $voice = null): ?string
    {
        return null;
    }
}
