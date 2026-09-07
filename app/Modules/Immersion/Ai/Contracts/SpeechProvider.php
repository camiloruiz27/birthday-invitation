<?php

namespace App\Modules\Immersion\Ai\Contracts;

/**
 * Turns a script into an audio file on the local disk.
 *
 * Returns the relative storage path, or null when no audio could be produced.
 * Null is a normal outcome, not an error: the timeline email goes out without
 * its attachment rather than failing.
 */
interface SpeechProvider
{
    public function synthesize(int $eventId, string $script): ?string;
}
