<?php

namespace App\Modules\Express\Support;

use RuntimeException;

/**
 * A move the game does not allow right now ("too early", "budget spent",
 * "not the host"). Carries a stable `key` the page switches on and a sentence
 * written for the player, so controllers never have to invent either.
 */
final class ExpressException extends RuntimeException
{
    public function __construct(
        public readonly string $key,
        string $message,
        public readonly int $status = 422,
    ) {
        parent::__construct($message);
    }
}
