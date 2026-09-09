<?php

namespace App\Modules\Platform\Actions;

use App\Modules\Platform\Models\PromoCodeRedemption;

/**
 * What redeeming a discount code produced: the amount to actually charge,
 * and the redemption row claiming that use — handed to
 * RedeemPromoCode::release() if the checkout that was supposed to use it
 * never completes.
 */
final class PromoDiscount
{
    public function __construct(
        public readonly int $amount,
        public readonly PromoCodeRedemption $redemption,
    ) {
    }
}
