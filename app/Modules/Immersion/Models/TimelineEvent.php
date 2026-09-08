<?php

namespace App\Modules\Immersion\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineEvent extends Model
{
    protected $table = 'immersion_timeline_events';

    /** Audio is being generated on the queue. */
    public const AUDIO_PENDING = 'pending';

    /** There is a file on disk. */
    public const AUDIO_READY = 'ready';

    /** Generation gave up; the Game Master can retry. */
    public const AUDIO_FAILED = 'failed';

    protected $fillable = [
        'game_id',
        'type',
        'trigger_offset_minutes',
        'title',
        'source_file',
        'body_markdown',
        'audio_script',
        'audio_path',
        'audio_status',
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

    /**
     * Was this recording shipped with the case rather than generated?
     *
     * Case audio is authored once, recorded outside the platform and deployed
     * with the case like its photographs. It lives under public/immersion/,
     * so it is recognised by where it points rather than by a second column.
     */
    public function audioIsCaseAsset(): bool
    {
        return (bool) $this->audio_path && str_starts_with($this->audio_path, 'immersion/');
    }

    /**
     * Where the recording actually is on disk, whichever kind it is.
     *
     * The two live in different roots — case audio ships with the case, and
     * anything still generated lands in storage — so every reader goes through
     * here instead of guessing.
     */
    public function audioAbsolutePath(): ?string
    {
        if (! $this->audio_path) {
            return null;
        }

        $path = $this->audioIsCaseAsset()
            ? public_path($this->audio_path)
            : \Illuminate\Support\Facades\Storage::disk('local')->path($this->audio_path);

        return is_file($path) ? $path : null;
    }

    /**
     * An audio event that went out without its recording and is not currently
     * being generated: the one case the Game Master can act on.
     *
     * Never true for case audio: if a file shipped with the case is missing,
     * retrying would generate a different recording from the one the author
     * approved. That is a deployment problem, not something to paper over.
     */
    public function needsAudioRetry(): bool
    {
        return $this->isAudio()
            && $this->isSent()
            && ! $this->audio_path
            && $this->audio_status !== self::AUDIO_PENDING;
    }
}
