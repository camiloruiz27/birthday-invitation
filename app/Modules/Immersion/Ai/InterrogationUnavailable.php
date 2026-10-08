<?php

namespace App\Modules\Immersion\Ai;

use RuntimeException;

/**
 * The gateway could not produce a real answer for this question.
 *
 * Distinct from a suspect who simply declines to add anything: that is a
 * legitimate reply and is paid for. This is a service that was not delivered,
 * so the caller must not charge for it or spend the player's question slot.
 */
class InterrogationUnavailable extends RuntimeException
{
}
