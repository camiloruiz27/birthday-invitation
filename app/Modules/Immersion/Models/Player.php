<?php

namespace App\Modules\Immersion\Models;

use App\Modules\Immersion\Support\GameRevision;
use App\Modules\Immersion\Support\GameSnapshot;
use Illuminate\Support\Facades\Cache;
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
        'is_owner',
        'privacy_accepted_at',
        'privacy_version',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
        'privacy_accepted_at' => 'datetime',
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
     * Every page load resolves the player by token, which makes it the one
     * place that can teach the poll endpoint which game a token belongs to
     * without that endpoint ever querying the database (see GameRevision).
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // A page READ with snapshots on: the player comes from the cache and so
        // does their game, so loading the page asks the database nothing. A write
        // (asking a question, accusing) always reads the database: it is going to
        // open a connection anyway, and it should act on the truth.
        $player = $this->fromBundle($value, $field) ?? parent::resolveRouteBinding($value, $field);

        if ($player) {
            GameRevision::rememberPlayer((string) $player->access_token, (int) $player->game_id);
            $this->keepBundle($player);

            // Even when the player itself had to be read from the database, their
            // game is the shared, ready-made one: it answers what the pages ask of
            // it (is the interrogation open...) without further queries, and the
            // same copy serves every other player of the table.
            if (GameSnapshot::enabled() && request()->isMethodSafe() && ! $player->relationLoaded('game')) {
                $game = GameSnapshot::game((int) $player->game_id);

                if ($game) {
                    $player->setRelation('game', $game);
                }
            }
        }

        return $player;
    }

    private function fromBundle($token, $field): ?self
    {
        if (! GameSnapshot::enabled() || ! request()->isMethodSafe() || ! in_array($field, [null, 'access_token'], true)) {
            return null;
        }

        try {
            $bundle = Cache::get(self::bundleKey((string) $token));
        } catch (\Throwable) {
            return null;
        }

        if (! is_array($bundle)) {
            return null;
        }

        $game = GameSnapshot::game((int) ($bundle['game_id'] ?? 0));

        if (! $game) {
            return null;
        }

        $player = (new self)->newFromBuilder($bundle['attributes']);
        $player->setRelation('game', $game);

        return $player;
    }

    /** What a page load needs of a player, for the next one. */
    private function keepBundle(self $player): void
    {
        if (! GameSnapshot::enabled()) {
            return;
        }

        try {
            // add(): the bundle only changes when the player is saved, and that
            // forgets it (see booted()); rewriting it on every read is wasted work.
            Cache::add(
                self::bundleKey((string) $player->access_token),
                ['attributes' => $player->getAttributes(), 'game_id' => (int) $player->game_id],
                now()->addHour()
            );
        } catch (\Throwable) {
        }
    }

    protected static function booted(): void
    {
        // Whatever the cached copy held may be out of date now.
        $forget = function (self $player) {
            try {
                Cache::forget(self::bundleKey((string) $player->getOriginal('access_token', $player->access_token)));
                Cache::forget(self::bundleKey((string) $player->access_token));
            } catch (\Throwable) {
            }
        };

        static::saved($forget);
        static::deleted($forget);
    }

    private static function bundleKey(string $token): string
    {
        return 'immersion:pb:'.sha1($token);
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
        return $this->eventsForMeQuery()
            ->whereIn('type', ['email', 'audio_email'])
            ->get();
    }

    /**
     * The events already sent that are MEANT for this player: for everyone, for
     * them in particular (a private clue drawn at random) or for their role.
     * No type filter and no columns chosen: the inbox adds the first, the
     * notices (PlayerNotices) the second.
     */
    public function eventsForMeQuery()
    {
        return $this->game->timelineEvents()
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
            ->orderBy('sent_at');
    }
}
