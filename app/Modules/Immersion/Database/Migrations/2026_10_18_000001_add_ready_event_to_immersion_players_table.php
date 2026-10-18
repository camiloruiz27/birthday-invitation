<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Ya lei toda la informacion": which envelope a player last confirmed.
     *
     * It holds the id of the latest sent envelope at the moment the player
     * pressed the button. A player is ready when it equals the game's current
     * latest envelope, so a new envelope turns everybody back to "not ready"
     * on its own, with nothing to reset. No foreign key: it is a marker, not a
     * relation, and an erased event must not block anything.
     */
    public function up(): void
    {
        Schema::table('immersion_players', function (Blueprint $table) {
            $table->unsignedBigInteger('ready_event_id')->nullable()->after('privacy_version');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_players', function (Blueprint $table) {
            $table->dropColumn('ready_event_id');
        });
    }
};
