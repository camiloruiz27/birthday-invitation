<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Who has authorised promotional email, and when the first-game offer went out.
 *
 * `marketing_opted_out_at` (2026-10-31) only ever said "do not contact"; it
 * could not say "was ever allowed to". These two columns add the missing half:
 * the authorisation itself, with where it came from, so a regulator or a user
 * asking can be answered.
 *
 * Accounts that already exist are enrolled by decision of the owner of the
 * platform (source `legacy`), and every one of them can leave with the link in
 * each mail or from their profile. New accounts are enrolled only by ticking an
 * unticked box (source `register`), or later from the profile (`profile`).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('marketing_consent_at')->nullable();
            $table->string('marketing_consent_source', 20)->nullable();
            // One offer per person for life: set when the mail leaves.
            $table->timestamp('first_game_offer_sent_at')->nullable();
        });

        // Whoever had already opted out keeps that: the exclusion column wins
        // over consent everywhere it is read.
        DB::table('users')->whereNull('marketing_consent_at')->update([
            'marketing_consent_at' => now(),
            'marketing_consent_source' => 'legacy',
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['marketing_consent_at', 'marketing_consent_source', 'first_game_offer_sent_at']);
        });
    }
};
