<?php

namespace App\Modules\Immersion\Models;

use App\Models\User;
use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Cases\CaseRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Game extends Model
{
    protected $table = 'immersion_games';

    protected $fillable = [
        'user_id',
        'name',
        'case_slug',
        'case_version',
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

    /**
     * Stamp the case a game belongs to at creation time. The columns carry
     * database defaults too, but those are not reflected back onto the model
     * instance, so code holding a freshly created Game would see nulls.
     */
    protected static function booted(): void
    {
        static::creating(function (self $game) {
            if (! $game->case_slug) {
                $case = app(CaseRegistry::class)->default();
                $game->case_slug = $case->slug;
                $game->case_version ??= $case->version();
            }
        });
    }

    public function getElapsedMinutesAttribute(): int
    {
        return $this->elapsedMinutes();
    }

    /**
     * The mystery case this game is playing. Every piece of narrative content
     * — suspects, envelopes, gallery, timeline — is resolved through here, so
     * the engine itself stays case-agnostic.
     */
    public function caseDefinition(): CaseDefinition
    {
        return app(CaseRegistry::class)->get(
            $this->case_slug ?: (string) config('immersion.default_case')
        );
    }

    /**
     * The Game Master account that owns this run. Null for games created
     * before accounts existed; those are claimable, never web-reachable.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
