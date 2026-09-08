<?php

namespace App\Modules\Immersion\Console\Commands;

use App\Models\User;
use App\Modules\Immersion\Models\CreditLedgerEntry;
use App\Modules\Immersion\Support\AiCredits;
use Illuminate\Console\Command;

/**
 * Adds AI credits to an account from the console.
 *
 * The support and operations door: compensating a table whose gateway failed,
 * topping up a demo account, honouring a payment that arrived outside the
 * platform. Every grant lands in the ledger with its note, so it is always
 * distinguishable from a purchase.
 */
class GrantCredits extends Command
{
    protected $signature = 'immersion:grant-credits
                            {email : The account to credit}
                            {credits : How many credits to add}
                            {--note= : Why, for the ledger}';

    protected $description = 'Add AI credits to an account';

    public function handle(AiCredits $credits): int
    {
        $amount = (int) $this->argument('credits');

        if ($amount < 1) {
            $this->error('La cantidad tiene que ser un entero positivo.');

            return self::FAILURE;
        }

        $user = User::firstWhere('email', $this->argument('email'));

        if (! $user) {
            $this->error("No existe ninguna cuenta con el correo {$this->argument('email')}.");

            return self::FAILURE;
        }

        $wallet = $credits->grant(
            $user,
            $amount,
            CreditLedgerEntry::REASON_ADJUST,
            $this->option('note') ?: 'Ajuste manual desde consola'
        );

        $this->info("{$amount} creditos anadidos a {$user->email}.");
        $this->line("  Disponible: {$wallet->available()}  |  Reservado: {$wallet->reserved}  |  Total: {$wallet->total()}");

        return self::SUCCESS;
    }
}
