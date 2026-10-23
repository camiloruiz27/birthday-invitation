<?php

namespace App\Modules\Express\Ai;

use RuntimeException;

/** The AI could not give a real answer (not configured, down, slow, empty). */
final class ExpressAiUnavailable extends RuntimeException
{
}
