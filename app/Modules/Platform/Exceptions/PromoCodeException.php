<?php

namespace App\Modules\Platform\Exceptions;

use RuntimeException;

/**
 * A code could not be redeemed. The message is written to be shown to the
 * buyer as-is — never a technical error — so every throw site in
 * RedeemPromoCode uses plain Spanish naming exactly what went wrong and,
 * where relevant, where the buyer should go instead.
 */
class PromoCodeException extends RuntimeException
{
}
