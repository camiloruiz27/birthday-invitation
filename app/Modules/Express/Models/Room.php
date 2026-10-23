<?php

namespace App\Modules\Express\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One group's round: a case, a code to join it and a clock that starts when
 * the host presses "Iniciar". `started_at_ms` is the only clock anything is
 * derived from (see Support/Clock).
 */
class Room extends Model
{
    public const LOBBY = 'lobby';

    public const RUNNING = 'running';

    public const FINISHED = 'finished';

    public const EXPIRED = 'expired';

    protected $table = 'express_rooms';

    protected $fillable = [
        'code', 'event_slug', 'case_slug', 'status', 'gm_player_id',
        'started_at_ms', 'revealed_at', 'next_room_id',
    ];

    protected $casts = [
        'started_at_ms' => 'integer',
        'rev' => 'integer',
        'revealed_at' => 'datetime',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class, 'room_id')->orderBy('joined_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'room_id')->orderBy('id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(Share::class, 'room_id');
    }

    public function accusations(): HasMany
    {
        return $this->hasMany(Accusation::class, 'room_id');
    }
}
