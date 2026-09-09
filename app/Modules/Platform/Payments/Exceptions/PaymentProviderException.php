<?php

namespace App\Modules\Platform\Payments\Exceptions;

use RuntimeException;

/**
 * The payment provider could not start or make sense of a checkout.
 *
 * Deliberately not caught into a graceful fallback anywhere it is thrown:
 * unlike an AI capability, there is no safe degraded mode for "we could not
 * confirm this is a real card payment" — the buyer sees an error and can
 * retry, rather than the platform guessing.
 */
class PaymentProviderException extends RuntimeException
{
}
