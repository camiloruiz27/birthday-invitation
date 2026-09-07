<?php

namespace App\Modules\Immersion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Player extends Model
{
    protected $table = 'immersion_players';

    protected $fillable = [
        'game_id',
        'name',
        'email',
        'access_token',
        'role_slug',
    ];

    /**
     * The access token IS the player's credential: whoever holds it can read
     * that player's inbox, interrogations and accusation. Players are shown
     * each other (a claimed suspect names the player who claimed it), so both
     * the token and the email are hidden by default and must be opted back in
     * with revealCredentials() — only for the player who owns them, or for
     * the Game Master.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_token',
        'email',
    ];

    public function getRouteKeyName(): string
    {
        return 'access_token';
    }

    /**
     * Opt this player's own token and email back into the serialized payload.
     */
    public function revealCredentials(): static
    {
        return $this->makeVisible(['access_token', 'email']);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function accusation(): HasOne
    {
        return $this->hasOne(Accusation::class);
    }

    public function inboxEvents()
    {
        return $this->game->timelineEvents()
            ->whereIn('type', ['email', 'audio_email'])
            ->whereNotNull('sent_at')
            ->where(function ($query) {
                $query->where('delivery_mode', 'all')
                    ->orWhere(function ($query) {
                        $query->where('delivery_mode', 'random_player')
                            ->where('delivered_to_player_id', $this->id);
                    })
                    ->orWhere(function ($query) {
                        $query->where('delivery_mode', 'role_slug')
                            ->where('target_role_slug', $this->role_slug);
                    });
            })
            ->orderBy('sent_at')
            ->get();
    }
}
