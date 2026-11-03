<?php

namespace App\Modules\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCode extends Model
{
    protected $fillable = ['user_id', 'code', 'eligible_at', 'active'];

    protected $casts = [
        'eligible_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversions(): HasMany
    {
        return $this->hasMany(ReferralConversion::class);
    }

    public function isUsable(): bool
    {
        return $this->active && $this->eligible_at !== null;
    }
}
