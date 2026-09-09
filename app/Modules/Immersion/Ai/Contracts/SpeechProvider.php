<?php

namespace App\Modules\Immersion\Ai\Contracts;

/**
 * Turns a script into an audio file on the local disk.
 *
 * Returns the relative storage path, or null when no audio could be produced.
 * Null is a normal outcome, not an error: the timeline email goes out without
 * its attachment rather than failing.
 *
 * `$key` names the file and acts as the cache key. It is a string rather than
 * an event id because not every recording belongs to a timeline event — the
 * culprit's confession belongs to a game.
 */
interface SpeechProvider
{
    public function synthesize(string $key, string $script, ?string $voice = null): ?string;
}
