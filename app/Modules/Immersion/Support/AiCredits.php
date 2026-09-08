<?php

namespace App\Modules\Immersion\Support;

use App\Models\User;
use App\Modules\Immersion\Models\CreditHold;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Models\CreditWallet;
use App\Modules\Immersion\Models\Game;
use Illuminate\Support\Facades\DB;

/**
 * The only thing allowed to move AI credits.
 *
 * Single writer on purpose. Every movement has to do three things together —
 * change the wallet, keep balance and reserved consistent, and append to the
 * ledger — and a second place that does two of the three is how balances start
 * disagreeing with history.
 *
 * Every mutation runs inside a transaction with a lock on the wallet row.
 * Reading a balance and then writing a new one is a read-modify-write, so two
 * requests arriving together (a double-clicked start button, two questions at
 * once) would otherwise both read the same number and both write over each
 * other. The unsigned columns are the backstop: an overdraw that slips through
 * fails at the database rather than granting free model calls.
 */
class AiCredits
{
    public function __construct(private GameCost $cost)
    {
    }

    public function enabled(): bool
    {
        return $this->cost->enabled();
    }

    /**
     * The account's wallet, created empty on first sight. Reading a balance
     * must never be the thing that fails for a brand new account.
     */
    public function walletFor(User|int $user): CreditWallet
    {
        $userId = $user instanceof User ? $user->id : $user;

        return CreditWallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'reserved' => 0]
        );
    }

    public function holdFor(Game $game): ?CreditHold
    {
        return CreditHold::where('game_id', $game->id)->first();
    }

    /**
     * Adds credits to an account: a purchase, a welcome grant, a support
     * correction. Returns the wallet as it now stands.
     */
    public function grant(
        User|int $user,
        int $credits,
        string $reason = CreditLedgerEntry::REASON_GRANT,
        ?string $note = null
    ): CreditWallet {
        $userId = $user instanceof User ? $user->id : $user;

        if ($credits <= 0) {
            return $this->walletFor($userId);
        }

        return DB::transaction(function () use ($userId, $credits, $reason, $note) {
            $wallet = $this->lockedWallet($userId);

            $wallet->balance += $credits;
            $wallet->save();

            $this->record($wallet, $credits, $reason, null, $note);

            return $wallet;
        });
    }

    /**
     * Freezes a game's whole ceiling before it starts.
     *
     * Returns false only when the account cannot cover it — the one case the
     * caller must turn into "recarga y vuelve a intentar". Returns true when
     * there was nothing to reserve (credits off, or a game with no AI at all)
     * and when a hold already exists, so starting twice is harmless.
     */
    public function reserve(Game $game): bool
    {
        if (! $this->enabled() || $game->user_id === null) {
            return true;
        }

        if ($hold = $this->holdFor($game)) {
            // A game that already ran and gave its capacity back is re-armed,
            // not reserved from scratch: it re-freezes only what it has left.
            return $hold->isReleased() ? $this->rearm($game) : true;
        }

        $amount = $this->cost->for($game)['total'];

        if ($amount <= 0) {
            return true;
        }

        return DB::transaction(function () use ($game, $amount) {
            $wallet = $this->lockedWallet($game->user_id);

            if (! $wallet->canAfford($amount)) {
                return false;
            }

            // Unique on game_id: if a second request won the race between the
            // check above and here, it already froze this game's ceiling and
            // freezing it again would be pure loss.
            $hold = CreditHold::firstOrCreate(
                ['game_id' => $game->id],
                ['user_id' => $game->user_id, 'amount' => $amount, 'spent' => 0]
            );

            if (! $hold->wasRecentlyCreated) {
                return true;
            }

            $wallet->balance -= $amount;
            $wallet->reserved += $amount;
            $wallet->save();

            // Delta zero: the credits did not leave the account, they only
            // stopped being available.
            $this->record($wallet, 0, CreditLedgerEntry::REASON_RESERVE, $game->id, "Reserva de {$amount} para \"{$game->name}\"");

            return true;
        });
    }

    /**
     * Draws credits for something the game just did.
     *
     * True means "go ahead": either it was paid for, or nothing was owed.
     *
     * The two ways a game can have no live hold are deliberately NOT the same
     * thing. A game that never had one runs free — that is what keeps games
     * already in progress when credits shipped playable to the end. A game
     * whose hold was released has been settled: its credits went back to the
     * wallet, so letting it keep calling the model would be spending them
     * twice.
     */
    public function spend(Game $game, int $credits, ?string $note = null): bool
    {
        if (! $this->enabled() || $credits <= 0) {
            return true;
        }

        $hold = $this->holdFor($game);

        if (! $hold) {
            return true;
        }

        if ($hold->isReleased()) {
            return false;
        }

        // The hold is the authority on the budget, and it is consumed first:
        // if this fails, no wallet row was touched and no model call happens.
        if (! $hold->consume($credits)) {
            return false;
        }

        DB::transaction(function () use ($game, $credits, $note) {
            $wallet = $this->lockedWallet($game->user_id);

            $wallet->reserved = max(0, $wallet->reserved - $credits);
            $wallet->save();

            $this->record($wallet, -$credits, CreditLedgerEntry::REASON_SPEND, $game->id, $note);
        });

        return true;
    }

    /**
     * Gives back whatever a game did not use.
     *
     * Idempotent: a hold is released once, and closing an already closed case
     * or deleting it afterwards returns zero rather than minting credits.
     * Returns how many credits went back.
     */
    public function release(Game|CreditHold $target, ?string $note = null): int
    {
        $hold = $target instanceof CreditHold ? $target : $this->holdFor($target);

        if (! $hold || $hold->isReleased()) {
            return 0;
        }

        return DB::transaction(function () use ($hold, $note) {
            // Re-read under the lock: another request may have released it
            // between the check above and here.
            $fresh = CreditHold::whereKey($hold->getKey())->lockForUpdate()->first();

            if (! $fresh || $fresh->isReleased()) {
                return 0;
            }

            $remaining = $fresh->remaining();

            $fresh->released_at = now();
            $fresh->save();

            if ($remaining === 0) {
                return 0;
            }

            $wallet = $this->lockedWallet($fresh->user_id);

            $wallet->reserved = max(0, $wallet->reserved - $remaining);
            $wallet->balance += $remaining;
            $wallet->save();

            $this->record(
                $wallet,
                0,
                CreditLedgerEntry::REASON_RELEASE,
                $fresh->game_id,
                $note ?? "Devolucion de {$remaining} creditos sin usar"
            );

            return $remaining;
        });
    }

    /**
     * Freezes again what a game gave back, so it can carry on.
     *
     * A case played over two weekends is a normal way to play, and both the
     * pause button and the stale-hold sweeper hand its capacity back in the
     * meantime — which is the point, since nobody should have credits frozen
     * by a game nobody is playing. Coming back has to be possible, and it has
     * to re-freeze only what is LEFT: a game that already asked twelve
     * questions must not be charged for forty-five again.
     *
     * Returns false when the balance no longer covers the remainder, typically
     * because it went into another table in between.
     */
    public function rearm(Game $game): bool
    {
        if (! $this->enabled() || $game->user_id === null) {
            return true;
        }

        $hold = $this->holdFor($game);

        // Nothing to re-arm: a game with no hold runs free, and one whose hold
        // is still live never gave anything back.
        if (! $hold || ! $hold->isReleased()) {
            return true;
        }

        return DB::transaction(function () use ($game, $hold) {
            $fresh = CreditHold::whereKey($hold->getKey())->lockForUpdate()->first();

            if (! $fresh || ! $fresh->isReleased()) {
                return true;
            }

            $remaining = max(0, $fresh->amount - $fresh->spent);

            if ($remaining === 0) {
                return true;
            }

            $wallet = $this->lockedWallet($fresh->user_id);

            if (! $wallet->canAfford($remaining)) {
                return false;
            }

            $fresh->released_at = null;
            $fresh->save();

            $wallet->balance -= $remaining;
            $wallet->reserved += $remaining;
            $wallet->save();

            $this->record(
                $wallet,
                0,
                CreditLedgerEntry::REASON_RESERVE,
                $game->id,
                "Reserva reactivada: {$remaining} creditos para \"{$game->name}\""
            );

            return true;
        });
    }

    /**
     * Is this game's capacity currently frozen?
     *
     * False for a game that had a hold and gave it back — the state a paused or
     * long-abandoned game sits in, and the one the console has to offer a way
     * out of. Also false for a game that never had a hold, which is why callers
     * check `holdFor()` when they need to tell those two apart.
     */
    public function isArmed(Game $game): bool
    {
        $hold = $this->holdFor($game);

        return $hold !== null && ! $hold->isReleased();
    }

    /**
     * How many credits short the account is for putting this game on the board:
     * starting it, or re-arming one that gave its capacity back. Zero means it
     * can go. Only ever used to phrase the message — the reservation itself is
     * what actually decides.
     */
    public function shortfallFor(Game $game): int
    {
        if (! $this->enabled() || $game->user_id === null) {
            return 0;
        }

        $hold = $this->holdFor($game);

        if ($hold && ! $hold->isReleased()) {
            return 0;
        }

        $needed = $hold
            ? max(0, $hold->amount - $hold->spent)
            : $this->cost->for($game)['total'];

        return max(0, $needed - $this->walletFor($game->user_id)->balance);
    }

    private function lockedWallet(int $userId): CreditWallet
    {
        // Ensure the row exists before locking it: SELECT ... FOR UPDATE on a
        // missing row locks nothing, and two requests would both then create it.
        $this->walletFor($userId);

        return CreditWallet::where('user_id', $userId)->lockForUpdate()->firstOrFail();
    }

    private function record(
        CreditWallet $wallet,
        int $delta,
        string $reason,
        ?int $gameId = null,
        ?string $note = null
    ): void {
        CreditLedgerEntry::create([
            'user_id' => $wallet->user_id,
            'game_id' => $gameId,
            'reason' => $reason,
            'delta' => $delta,
            'balance_after' => $wallet->balance,
            'reserved_after' => $wallet->reserved,
            'note' => $note,
        ]);
    }
}
