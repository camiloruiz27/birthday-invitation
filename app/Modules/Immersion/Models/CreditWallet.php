<?php

namespace App\Modules\Immersion\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An account's AI credit balance.
 *
 * Read freely; write only through Support\AiCredits. Every movement has to be
 * mirrored into the ledger and applied under a row lock, and a model with
 * `increment()` on it invites doing neither.
 */
class CreditWallet extends Model
{
    protected $table = 'ai_credit_wallets';

    protected $fillable = [
        'user_id',
        'balance',
        'reserved',
    ];

    protected $casts = [
        'balance' => 'integer',
        'reserved' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * What can still be spent or frozen. `balance` already excludes what is
     * reserved, so this is simply the balance — the method exists to make the
     * distinction unmissable at call sites.
     */
    public function available(): int
    {
        return $this->balance;
    }

    /**
     * Everything the account owns, frozen or not. This is the number a person
     * expects to see after buying credits, so it is what the UI shows.
     */
    public function total(): int
    {
        return $this->balance + $this->reserved;
    }

    public function canAfford(int $credits): bool
    {
        return $this->balance >= $credits;
    }
}
