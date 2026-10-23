<?php

namespace App\Modules\Express\Ai;

use App\Modules\Express\Support\ExpressCase;

/**
 * Plays a suspect for one question of the fair demo.
 *
 * An interface so the room logic does not know whether an answer comes from
 * the AI gateway or from nowhere (tests, an install with no gateway): the
 * unavailable case is an exception, never a made-up reply, and the caller
 * decides what to do about it (fall back to the author's own answer).
 */
interface ExpressInterrogationProvider
{
    /**
     * Whether this provider will really try to answer. False means "answer
     * from the author's text instead", decided before a question is spent.
     */
    public function available(): bool;

    /**
     * @param  array<string, mixed>  $suspect  the public sheet (id, name, role, topics)
     * @param  array<int, array{role: string, content: string}>  $history  earlier turns with THIS suspect, oldest first
     * @param  array<int, array{id: string, title: string}>  $evidence  what the whole table has in hand
     * @return string the suspect's reply, in character
     *
     * @throws ExpressAiUnavailable when no real answer could be had
     */
    public function ask(ExpressCase $case, array $suspect, array $history, string $question, ?string $topicId, array $evidence): string;
}
