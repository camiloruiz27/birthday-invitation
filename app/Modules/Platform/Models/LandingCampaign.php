<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LandingCampaign extends Model
{
    protected $fillable = ['promo_code_id', 'name', 'active', 'eyebrow', 'headline', 'description'];

    protected $casts = ['active' => 'boolean'];

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function selections(): HasMany
    {
        return $this->hasMany(LandingCampaignCase::class)->orderBy('sort_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(LandingCampaignEvent::class);
    }
}
