<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Proof that a player was told how their data is handled.
     *
     * A player never creates an account: the Game Master types their name and
     * email, and the access link is their whole credential. Ley 1581 de 2012
     * still asks for the owner's prior, express and informed authorization, so
     * the first screen they open is a privacy notice and this records the
     * answer — when, and which version of the policy it referred to.
     *
     * Nullable: players invited before this migration have not seen the
     * notice, and they are shown it on their next visit.
     */
    public function up(): void
    {
        Schema::table('immersion_players', function (Blueprint $table) {
            $table->timestamp('privacy_accepted_at')->nullable();
            $table->string('privacy_version', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('immersion_players', function (Blueprint $table) {
            $table->dropColumn(['privacy_accepted_at', 'privacy_version']);
        });
    }
};
