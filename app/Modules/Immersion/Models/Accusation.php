<?php

namespace App\Modules\Immersion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accusation extends Model
{
    protected $table = 'immersion_accusations';

    /**
     * No isCorrect() helper here on purpose: this model is serialized straight
     * to the player's browser, and a method that reaches for the case solution
     * would put the answer one careless prop away. Scoring lives in
     * Support\AccusationScoreboard.
     */
    protected $fillable = [
        'game_id',
        'player_id',
        'suspect_slug',
        'suspect_name',
        'was_correct',
        'motive',
        'weapon',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'was_correct' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
