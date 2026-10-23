<?php

namespace App\Modules\Express\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Someone in a room. No account, no email: the token in their URL is the whole
 * credential, which is why it is hidden from any serialization.
 */
class Player extends Model
{
    protected $table = 'express_players';

    protected $fillable = [
        'room_id', 'lead_id', 'token', 'nickname', 'audience', 'is_gm', 'joined_order',
    ];

    protected $hidden = ['token'];

    protected $casts = [
        'is_gm' => 'boolean',
        'joined_order' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}
