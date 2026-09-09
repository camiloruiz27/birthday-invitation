<?php

namespace App\Modules\Immersion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accusation extends Model
{
    protected $table = 'immersion_accusations';

    /**
     * No isCorrect() helper here on purpose: this model is serialized straight
     * to the player's browser, and a method that reaches for the case solution
     * would put the answer one careless prop away. Scoring lives in
     * Support\AccusationScoreboard.
     */
    public const EPILOGUE_PENDING = 'pending';

    public const EPILOGUE_READY = 'ready';

    public const EPILOGUE_FAILED = 'failed';

    protected $fillable = [
        'game_id',
        'player_id',
        'suspect_slug',
        'suspect_name',
        'was_correct',
        'motive',
        'weapon',
        'submitted_at',
        'epilogue_status',
        'epilogue_body',
        'epilogue_sent_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'was_correct' => 'boolean',
        'epilogue_sent_at' => 'datetime',
    ];

    /**
     * The epilogue is written only after the ending is revealed, so unlike the
     * verdict it is never a spoiler by the time it exists. It is still hidden
     * by default: this model is serialized to the player on the accusation
     * page, which is reachable before the reveal.
     *
     * @var array<int, string>
     */
    protected $hidden = ['epilogue_body'];

    /**
     * Opts this row into carrying its epilogue, for the one page that should
     * show it. Mirrors Player::revealCredentials(): opting in is explicit and
     * greppable, so nothing leaks by simply forgetting.
     */
    public function revealEpilogue(): self
    {
        return $this->makeVisible('epilogue_body');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
