<?php

namespace App\Modules\Express\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One player's final answer. `how_index` and `why_index` are positions in the
 * case's option lists, not text, so what a client saw (opaque, shuffled ids)
 * can never be mistaken for the file's order.
 */
class Accusation extends Model
{
    protected $table = 'express_accusations';

    protected $fillable = [
        'room_id', 'player_id', 'who_id', 'how_index', 'why_index', 'submitted_ms',
        'score_who', 'score_how', 'score_why', 'score_total',
    ];

    protected $casts = [
        'how_index' => 'integer',
        'why_index' => 'integer',
        'submitted_ms' => 'integer',
    ];
}
