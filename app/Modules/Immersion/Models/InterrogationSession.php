<?php

namespace App\Modules\Immersion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterrogationSession extends Model
{
    protected $table = 'immersion_interrogation_sessions';

    protected $fillable = [
        'game_id',
        'player_id',
        'suspect_slug',
        'started_at',
        'questions_used',
        'max_questions',
        'closed_at',
        'transcript_revealed',
    ];

    protected $casts = [
        'game_id' => 'integer',
        'player_id' => 'integer',
        'questions_used' => 'integer',
        'max_questions' => 'integer',
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
        'transcript_revealed' => 'boolean',
    ];

    /**
     * Budget for sessions created from now on. Existing sessions keep the
     * limit stored on their own row.
     */
    public static function defaultMaxQuestions(): int
    {
        return (int) config('immersion.interrogation.max_questions', 5);
    }

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
        return max(0, $this->max_questions - $this->questions_used);
    }

    /**
     * Atomically consume one question from this session's budget. Returns
     * false when the budget is already spent or the session is closed, in
     * which case nothing was written and no AI call should be made.
     *
     * The WHERE clause is the enforcement point: a read-modify-write in PHP
     * would let two concurrent requests spend the same slot twice.
     */
    public function reserveQuestion(): bool
    {
        $reserved = static::query()
            ->whereKey($this->getKey())
            ->whereNull('closed_at')
            ->whereColumn('questions_used', '<', 'max_questions')
            ->increment('questions_used');

        if ($reserved === 0) {
            return false;
        }

        $this->refresh();

        return true;
    }

    /**
     * Hands back a slot taken by reserveQuestion() when the question never
     * actually happened. Only used when charging the game's AI credits fails
     * after the slot was taken: the player asked nothing, so they must not
     * lose one of their five.
     */
    public function releaseQuestion(): void
    {
        static::query()
            ->whereKey($this->getKey())
            ->where('questions_used', '>', 0)
            ->decrement('questions_used');

        $this->refresh();
    }
}
