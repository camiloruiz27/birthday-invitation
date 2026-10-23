<?php

namespace App\Modules\Express\Support;

/**
 * Decides which of a suspect's topics a typed question is about.
 *
 * An interface because the demo answers from the author's own words today,
 * but a live-AI matcher (`ia_live_optional` in the case files) would slot in
 * here without touching the room logic.
 */
interface QuestionMatcher
{
    /**
     * @param  array<int, array{id: string, paraphrases: array<int, string>, answer: string, unlock_second: int}>  $topics
     * @return string|null the matching topic id, or null when it is not clear
     */
    public function match(string $question, array $topics): ?string;
}
