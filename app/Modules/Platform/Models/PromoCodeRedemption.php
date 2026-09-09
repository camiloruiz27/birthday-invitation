<?php

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One immutable fact: this user redeemed this code, optionally against this
 * order. Never updated after creation — release() (RedeemPromoCode) deletes
 * the row outright rather than marking it voided, because an order that
 * never completed (Bold failed) never really consumed the code's use.
 */
class PromoCodeRedemption extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'promo_code_id',
        'user_id',
        'order_id',
    ];

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
