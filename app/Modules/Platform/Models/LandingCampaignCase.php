<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingCampaignCase extends Model
{
    public const PAID = 'paid';
    public const FREE = 'free';

    protected $fillable = ['landing_campaign_id', 'mystery_case_id', 'placement', 'sort_order', 'recommended'];

    protected $casts = ['recommended' => 'boolean', 'sort_order' => 'integer'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(LandingCampaign::class, 'landing_campaign_id');
    }

    public function mysteryCase(): BelongsTo
    {
        return $this->belongsTo(MysteryCase::class);
    }
}
