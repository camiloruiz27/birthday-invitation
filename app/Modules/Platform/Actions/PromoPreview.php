<?php

namespace App\Modules\Platform\Actions;

use App\Modules\Platform\Models\PromoCode;

/**
 * A read-only look at what a discount code would do to an amount, for the
 * order review screen — no redemption behind it, unlike PromoDiscount.
 * Nothing has been claimed yet.
 */
final class PromoPreview
{
    public function __construct(
        public readonly int $discountedAmount,
        public readonly PromoCode $promoCode,
    ) {
    }
}
