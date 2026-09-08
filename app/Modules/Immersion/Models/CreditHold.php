<?php

namespace App\Modules\Immersion\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * The AI capacity frozen for one running game.
 */
class CreditHold extends Model
{
    protected $table = 'ai_credit_holds';

    protected $fillable = [
        'game_id',
        'user_id',
        'amount',
        'spent',
        'released_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'spent' => 'integer',
        'released_at' => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isReleased(): bool
    {
        return $this->released_at !== null;
    }

    /**
     * Still frozen and still spendable by this game.
     */
    public function remaining(): int
    {
        return $this->isReleased() ? 0 : max(0, $this->amount - $this->spent);
    }

    /**
     * Draws credits against this hold, atomically.
     *
     * The conditional UPDATE is the budget: two players asking a question in
     * the same instant would otherwise both read the same `spent` and both
     * write the same increment, spending one credit twice. Returns false when
     * the hold is exhausted or already released, and the caller must then not
     * make the model call.
     */
    public function consume(int $credits): bool
    {
        if ($credits <= 0) {
            return true;
        }

        $applied = static::query()
            ->whereKey($this->getKey())
            ->whereNull('released_at')
            ->whereRaw('amount - spent >= ?', [$credits])
            ->update([
                'spent' => DB::raw('spent + '.(int) $credits),
                'updated_at' => now(),
            ]);

        if ($applied === 0) {
            return false;
        }

        $this->spent += $credits;

        return true;
    }
}
