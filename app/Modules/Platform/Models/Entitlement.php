<?php

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user's right to play a case.
 *
 * Access is checked against this and nothing else — never against an order or
 * a payment. See the migration for why.
 */
class Entitlement extends Model
{
    public const SOURCE_PURCHASE = 'purchase';

    public const SOURCE_GRANT = 'grant';

    public const SOURCE_PROMO = 'promo';

    protected $fillable = [
        'user_id',
        'mystery_case_id',
        'source',
        'granted_at',
        'expires_at',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mysteryCase(): BelongsTo
    {
        return $this->belongsTo(MysteryCase::class);
    }

    /**
     * Null expires_at means permanent access, which is the current model.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
