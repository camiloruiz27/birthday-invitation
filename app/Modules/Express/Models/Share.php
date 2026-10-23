<?php

namespace App\Modules\Express\Models;

use Illuminate\Database\Eloquent\Model;

/** A private clue its owner chose to show to the whole group. */
class Share extends Model
{
    protected $table = 'express_shares';

    protected $fillable = ['room_id', 'player_id', 'evidence_id'];
}
