<?php

namespace App\Modules\Immersion\Models;

use App\Models\User;
use App\Modules\Immersion\Cases\CaseDefinition;
use App\Modules\Immersion\Cases\CaseRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Game extends Model
{
    protected $table = 'immersion_games';

    /** Someone directs the case and does not play. */
    public const MODE_GM_LED = 'gm_led';

    /** The system runs the timeline and the owner plays too. */
    public const MODE_AUTOMATIC = 'automatic';

    /** Everyone sees the authored solution and who got it right. */
    public const ENDING_CLASSIC = 'classic';

    /** Each player gets a message from the suspect they accused. Later. */
    public const ENDING_EPILOGUE = 'epilogue';

    /** The Game Master gets an audio of the culprit confessing. */
    public const ENDING_CONFESSION_AUDIO = 'confession_audio';

    public const AUDIO_PENDING = 'pending';

    public const AUDIO_READY = 'ready';

    public const AUDIO_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'name',
        'case_slug',
        'case_version',
        'mode',
        'ending_type',
        'status',
        'started_at',
        'paused_at',
        'paused_seconds_total',
        'finished_at',
        'ending_revealed_at',
        'ending_revealed_by',
        'ending_audio_status',
        'ending_audio_path',
        'ending_audio_script',
        'interrogation_enabled',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'finished_at' => 'datetime',
        'ending_revealed_at' => 'datetime',
        'interrogation_enabled' => 'boolean',
    ];

    /**
     * A game is serialized straight to players, so nothing here may carry more
     * than they need.
     *
     * `ending_audio_path` is a storage path the browser can do nothing with —
     * the console shows the status and streams the file through a route.
     * `ending_audio_script` is the culprit's confession in words: it is the
     * solution, it belongs to the Game Master alone, and it must never ride
     * along on a player's game object.
     *
     * @var array<int, string>
     */
    protected $hidden = ['ending_audio_path', 'ending_audio_script'];

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

    /**
     * The AI capacity frozen for this run, if any.
     *
     * Null is a normal state, not a broken one: a game that has not started
     * yet, a game with no AI mechanics, a deployment with credits switched
     * off, and every game that was already running when credits shipped all
     * legitimately have no hold.
     */
    public function creditHold(): HasOne
    {
        return $this->hasOne(CreditHold::class);
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

    public function endingRevealed(): bool
    {
        return $this->ending_revealed_at !== null;
    }

    /**
     * Once the answer is on screen, an accusation can no longer be changed —
     * otherwise anyone could "correct" theirs and walk away with a perfect
     * score. Closing the case seals them too.
     */
    public function accusationsLocked(): bool
    {
        return $this->endingRevealed() || $this->isFinished();
    }

    public function pendingAccusationsCount(): int
    {
        return max(0, $this->players()->count() - $this->accusations()->count());
    }

    /**
     * Has the whole table accused?
     *
     * Everyone counts, including the owner's own player row in automatic mode:
     * leaving them out would reveal the answer before they had a chance to
     * play, which is the exact thing automatic mode protects against.
     */
    public function everyoneAccused(): bool
    {
        $players = $this->players()->count();

        return $players > 0 && $this->accusations()->count() >= $players;
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
