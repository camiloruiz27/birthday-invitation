<?php

namespace App\Modules\Immersion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A participant's immutable opinion of one completed case. */
class CaseRating extends Model
{
    public const TAGS = [
        'story' => 'Historia excelente',
        'clues' => 'Pistas claras',
        'pacing' => 'Buen ritmo',
        'atmosphere' => 'Buena ambientación',
        'recommend' => 'Lo recomendaría',
    ];

    protected $fillable = [
        'case_slug',
        'game_id',
        'player_id',
        'rating_half_steps',
        'tags',
    ];

    protected $casts = [
        'rating_half_steps' => 'integer',
        'tags' => 'array',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function stars(): float
    {
        return $this->rating_half_steps / 2;
    }
}
