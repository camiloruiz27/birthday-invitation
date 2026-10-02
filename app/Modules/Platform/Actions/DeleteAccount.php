<?php

namespace App\Modules\Platform\Actions;

use App\Models\User;
use App\Modules\Immersion\Models\CreditHold;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Models\CreditWallet;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Support\GameEraser;
use App\Modules\Platform\Models\Entitlement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Closes an account: erases the person, keeps the books.
 *
 * Deleting the `users` row outright was wrong on two counts. The foreign key
 * from `orders` cascades, so every payment record the account ever made would
 * vanish — and the privacy policy promises accounting records are kept (Ley
 * 962 de 2005, art. 28: ten years). And a game's `user_id` is set null rather
 * than cascaded, so the names and emails of the players the Game Master
 * invited would have survived the account, orphaned and unreachable.
 *
 * So the account is erased in two halves:
 *
 *  - Everything personal goes: the games with their players (via GameEraser),
 *    the case access, the credit wallet and its history, API tokens and any
 *    password-reset token.
 *  - The `users` row stays, anonymised, as the anchor for the orders that must
 *    be kept. Its name and email are replaced, its password is random and
 *    unknowable, and its email address is released for a future registration.
 *    Nobody can sign in to it again.
 *
 * Promo redemptions are kept too: they carry no personal data once the user
 * is anonymous, and they are what stops a "one per account" code being used
 * again after a delete-and-register.
 */
class DeleteAccount
{
    public function __construct(private GameEraser $games)
    {
    }

    public function handle(User $user): void
    {
        DB::transaction(function () use ($user) {
            Game::where('user_id', $user->id)->get()->each(
                fn (Game $game) => $this->games->erase($game, 'Cuenta eliminada')
            );

            Entitlement::where('user_id', $user->id)->delete();

            // Holds and ledger first: both point at the wallet's owner.
            CreditHold::where('user_id', $user->id)->delete();
            CreditLedgerEntry::where('user_id', $user->id)->delete();
            CreditWallet::where('user_id', $user->id)->delete();

            $user->tokens()->delete();
            DB::table('password_resets')->where('email', $user->email)->delete();

            $user->forceFill([
                'name' => 'Cuenta eliminada',
                'email' => "eliminada-{$user->id}@eliminada.invalid",
                'email_verified_at' => null,
                'password' => Hash::make(Str::random(64)),
                'remember_token' => null,
                'is_admin' => false,
            ])->save();
        });
    }
}
