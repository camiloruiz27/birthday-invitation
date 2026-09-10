<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Marks every account that already existed as verified.
 *
 * Email verification is being switched on for a platform that already has
 * customers. Those people registered when confirming an address was not part
 * of the deal, and several of them have paid for cases. Leaving them
 * unverified would lock them out of buying anything else until they hunted
 * down a mail that was never sent — punishing the existing customers for a
 * change they had no part in.
 *
 * So the cutoff is "accounts that exist at the moment this runs". Everyone
 * who registers afterwards confirms their address like normal.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    /**
     * Not reversible on purpose.
     *
     * Rolling back would have to un-verify people, and it cannot tell the
     * grandfathered accounts apart from everyone who genuinely confirmed
     * their address since — so it would strip real verifications to undo a
     * migration. Doing nothing is the honest reversal.
     */
    public function down(): void
    {
    }
};
