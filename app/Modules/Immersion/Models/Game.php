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

    /** Someone directs the case and does not play. */
    public const MODE_GM_LED = 'gm_led';

    /** The system runs the timeline and the owner plays too. */
    public const MODE_AUTOMATIC = 'automatic';

    protected $fillable = [
        'user_id',
        'name',
        'case_slug',
        'case_version',
        'mode',
        'status',
        'started_at',
        'paused_at',
        'paused_seconds_total',
        'finished_at',
        'interrogation_enabled',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'finished_at' => 'datetime',
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

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    /**
     * In automatic mode the owner is also a player, which is why the console
     * has to withhold anything that would spoil their own game.
     */
    public function isAutomatic(): bool
    {
        return $this->mode === self::MODE_AUTOMATIC;
    }

    /**
     * The player row belonging to the owner, in automatic mode.
     */
    public function ownerPlayer(): ?Player
    {
        if (! $this->isAutomatic() || ! $this->user_id) {
            return null;
        }

        return $this->players()->where('is_owner', true)->first();
    }

    /**
     * Ends the case. Nothing else moves afterwards: pending events stop
     * mattering and, in automatic mode, this is what finally lets the owner
     * see the interrogations and the accusations.
     */
    public function finish(): void
    {
        $this->update([
            'status' => 'finished',
            'finished_at' => Carbon::now(),
            'paused_at' => null,
        ]);
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

        // The clock stops when the case ends and while it is paused; anything
        // else would keep ticking against a game nobody is playing.
        $referenceNow = match (true) {
            $this->isFinished() && $this->finished_at !== null => $this->finished_at,
            $this->isPaused() && $this->paused_at !== null => $this->paused_at,
            default => Carbon::now(),
        };

        $elapsedSeconds = $this->started_at->diffInSeconds($referenceNow) - $this->paused_seconds_total;

        return (int) max(0, floor($elapsedSeconds / 60));
    }
}
