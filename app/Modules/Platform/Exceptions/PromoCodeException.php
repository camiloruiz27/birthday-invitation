<?php

namespace App\Modules\Platform\Exceptions;

use RuntimeException;

/**
 * A code could not be redeemed. The message is written to be shown to the
 * buyer as-is — never a technical error — so every throw site in
 * RedeemPromoCode uses plain Spanish naming exactly what went wrong and,
 * where relevant, where the buyer should go instead.
 *
 * $wrongArea marks the one case where the buyer is actually holding a valid
 * code, just on the wrong screen (a gift code typed into the discount field,
 * or vice versa) — the only rejection specific enough that the UI can offer
 * a link to where the code DOES belong, instead of just the error text.
 */
class PromoCodeException extends RuntimeException
{
    public function __construct(string $message, private bool $wrongArea = false)
    {
        parent::__construct($message);
    }

    public function isWrongArea(): bool
    {
        return $this->wrongArea;
    }
}
