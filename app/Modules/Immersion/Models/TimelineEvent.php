<?php

namespace App\Modules\Immersion\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineEvent extends Model
{
    protected $table = 'immersion_timeline_events';

    protected $fillable = [
        'game_id',
        'type',
        'trigger_offset_minutes',
        'title',
        'source_file',
        'body_markdown',
        'audio_script',
        'audio_path',
        'delivery_mode',
        'cta_interrogation',
        'target_role_slug',
        'delivered_to_player_id',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'cta_interrogation' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function deliveredToPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'delivered_to_player_id');
    }

    public function scopeDue(Builder $query, int $elapsedMinutes): Builder
    {
        return $query->whereNull('sent_at')
            ->where('trigger_offset_minutes', '<=', $elapsedMinutes)
            ->orderBy('trigger_offset_minutes');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('sent_at')->orderBy('trigger_offset_minutes');
    }

    public function isAudio(): bool
    {
        return $this->type === 'audio_email';
    }

    public function isUnlock(): bool
    {
        return $this->type === 'unlock';
    }

    public function isSent(): bool
    {
        return ! is_null($this->sent_at);
    }
}
