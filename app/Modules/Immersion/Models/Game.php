<?php

namespace App\Modules\Immersion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Game extends Model
{
    protected $table = 'immersion_games';

    protected $fillable = [
        'name',
        'status',
        'started_at',
        'paused_at',
        'paused_seconds_total',
        'interrogation_enabled',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'interrogation_enabled' => 'boolean',
    ];

    protected $appends = ['elapsed_minutes'];

    public function getElapsedMinutesAttribute(): int
    {
        return $this->elapsedMinutes();
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class)->orderBy('trigger_offset_minutes');
    }

    public function accusations(): HasMany
    {
        return $this->hasMany(Accusation::class);
    }

    public function interrogationSessions(): HasMany
    {
        return $this->hasMany(InterrogationSession::class);
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function accusationsUnlocked(): bool
    {
        return $this->timelineEvents()
            ->where('type', 'unlock')
            ->whereNotNull('sent_at')
            ->exists();
    }

    public function elapsedMinutes(): int
    {
        if (! $this->started_at) {
            return 0;
        }

        $referenceNow = $this->isPaused() && $this->paused_at
            ? $this->paused_at
            : Carbon::now();

        $elapsedSeconds = $this->started_at->diffInSeconds($referenceNow) - $this->paused_seconds_total;

        return (int) max(0, floor($elapsedSeconds / 60));
    }
}
