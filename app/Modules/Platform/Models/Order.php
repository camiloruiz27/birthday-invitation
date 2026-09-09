<?php

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A real-money transaction against a payment provider.
 *
 * Not what access is checked against — see Entitlement for that. This is the
 * accounting record: what was attempted, for how much, and what the provider
 * said. Only `BoldWebhookController` moves it out of `pending`, and only
 * through a conditional UPDATE (see its class docblock) so a retried webhook
 * can never grant twice.
 */
class Order extends Model
{
    public const TYPE_CASE = 'case';

    public const TYPE_CREDIT_PACKAGE = 'credit_package';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_VOIDED = 'voided';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'user_id',
        'type',
        'mystery_case_id',
        'credit_package_id',
        'credits_granted',
        'amount',
        'currency',
        'status',
        'reference',
        'provider_link_id',
        'provider',
        'provider_payment_id',
        'checkout_url',
        'raw_webhook',
        'paid_at',
    ];

    protected $casts = [
        'credits_granted' => 'integer',
        'amount' => 'integer',
        'raw_webhook' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mysteryCase(): BelongsTo
    {
        return $this->belongsTo(MysteryCase::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
