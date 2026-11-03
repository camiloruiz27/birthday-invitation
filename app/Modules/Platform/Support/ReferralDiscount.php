<?php

namespace App\Modules\Platform\Support;

use App\Modules\Platform\Models\ReferralCode;

/** A validated referral discount before its order exists. */
final class ReferralDiscount
{
    public function __construct(
        public ReferralCode $referralCode,
        public int $discountedAmount,
        public int $recipientDiscountPercent,
        public int $referrerRewardPercent,
    ) {
    }
}
