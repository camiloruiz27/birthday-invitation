<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A redeemable code: either a GIFT (grants_case_slug and/or grants_credits)
 * or a DISCOUNT (discount_type + discount_value), never both — enforced by
 * whoever creates one (CreatePromoCode), not by this model.
 *
 * Access and credits are never checked against this table directly — only
 * RedeemPromoCode ever writes to it, going through Entitlement/AiCredits the
 * same way a purchase does.
 */
class PromoCode extends Model
{
    public const DISCOUNT_PERCENT = 'percent';

    public const DISCOUNT_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'grants_case_slug',
        'grants_credits',
        'discount_type',
        'discount_value',
        'max_redemptions',
        'max_redemptions_per_user',
        'redemptions_count',
        'active',
        'note',
    ];

    protected $casts = [
        'grants_credits' => 'integer',
        'discount_value' => 'integer',
        'max_redemptions' => 'integer',
        'max_redemptions_per_user' => 'integer',
        'redemptions_count' => 'integer',
        'active' => 'boolean',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }

    public function isGift(): bool
    {
        return $this->grants_case_slug !== null || $this->grants_credits !== null;
    }

    public function isDiscount(): bool
    {
        return $this->discount_type !== null;
    }

    public function isExhausted(): bool
    {
        return $this->max_redemptions !== null && $this->redemptions_count >= $this->max_redemptions;
    }

    /**
     * Applies this code's discount to an amount, floored at zero — a fixed
     * discount larger than the price (or a stray value above 100%) can never
     * produce a negative charge.
     */
    public function discountedAmount(int $amount): int
    {
        $discounted = match ($this->discount_type) {
            self::DISCOUNT_PERCENT => (int) floor($amount * (100 - $this->discount_value) / 100),
            self::DISCOUNT_FIXED => $amount - $this->discount_value,
            default => $amount,
        };

        return max(0, $discounted);
    }
}
