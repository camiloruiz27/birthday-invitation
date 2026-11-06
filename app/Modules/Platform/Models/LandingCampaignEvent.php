<?php

namespace App\Modules\Platform\Models;

use Illuminate\Database\Eloquent\Model;

class LandingCampaignEvent extends Model
{
    public const VIEW = 'landing_view';
    public const PAID_CLICK = 'paid_click';
    public const FREE_CLICK = 'free_click';
    public const POST_FREE_VIEW = 'post_free_view';
    public const POST_FREE_CLICK = 'post_free_click';

    protected $fillable = [
        'landing_campaign_id', 'mystery_case_id', 'user_id', 'game_id', 'player_id',
        'event', 'dedupe_key', 'occurred_at',
    ];

    protected $casts = ['occurred_at' => 'datetime'];
}
