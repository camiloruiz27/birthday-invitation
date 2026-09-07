<?php

namespace App\Modules\Immersion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterrogationSession extends Model
{
    protected $table = 'immersion_interrogation_sessions';

    public const MAX_QUESTIONS = 5;

    protected $fillable = [
        'game_id',
        'player_id',
        'suspect_slug',
        'started_at',
        'questions_used',
        'closed_at',
        'transcript_revealed',
    ];

    protected $casts = [
        'game_id' => 'integer',
        'player_id' => 'integer',
        'questions_used' => 'integer',
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
        'transcript_revealed' => 'boolean',
    ];

    public function isOwnedBy(Player $player): bool
    {
        return $this->player_id === $player->id;
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InterrogationMessage::class, 'session_id')->orderBy('created_at');
    }

    public function isClosed(): bool
    {
        return ! is_null($this->closed_at);
    }

    public function questionsRemaining(): int
    {
        return max(0, self::MAX_QUESTIONS - $this->questions_used);
    }
}
