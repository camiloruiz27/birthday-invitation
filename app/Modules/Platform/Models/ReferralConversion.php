<?php

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralConversion extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_VOIDED = 'voided';

    protected $fillable = [
        'referral_code_id',
        'referrer_user_id',
        'referred_user_id',
        'order_id',
        'reward_promo_code_id',
        'recipient_discount_percent',
        'referrer_reward_percent',
        'status',
        'risk_flags',
        'rewarded_at',
        'cancelled_at',
    ];

    protected $casts = [
        'recipient_discount_percent' => 'integer',
        'referrer_reward_percent' => 'integer',
        'risk_flags' => 'array',
        'rewarded_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function rewardPromoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class, 'reward_promo_code_id');
    }
}
