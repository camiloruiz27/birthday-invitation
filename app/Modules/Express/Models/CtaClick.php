<?php

namespace App\Modules\Express\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One anonymous participant clicking the final catalog link, at most once. */
class CtaClick extends Model
{
    protected $table = 'express_cta_clicks';

    protected $fillable = ['room_id', 'player_id'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }
}
