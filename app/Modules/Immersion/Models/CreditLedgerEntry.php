<?php

namespace App\Modules\Immersion\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One movement in an account's credit history. Append-only.
 */
class CreditLedgerEntry extends Model
{
    public const REASON_GRANT = 'grant';

    public const REASON_TOPUP = 'topup';

    public const REASON_RESERVE = 'reserve';

    public const REASON_SPEND = 'spend';

    public const REASON_RELEASE = 'release';

    public const REASON_ADJUST = 'adjust';

    public const REASON_PROMO = 'promo';

    protected $table = 'ai_credit_ledger';

    /**
     * Entries are written once and never touched again, so there is no
     * updated_at to maintain.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'game_id',
        'reason',
        'delta',
        'balance_after',
        'reserved_after',
        'note',
    ];

    protected $casts = [
        'delta' => 'integer',
        'balance_after' => 'integer',
        'reserved_after' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
