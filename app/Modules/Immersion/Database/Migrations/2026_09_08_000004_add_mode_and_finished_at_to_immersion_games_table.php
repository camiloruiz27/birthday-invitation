<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Two ways to run the same engine.
     *
     * `gm_led`    — someone directs: they start, pause, force events and watch
     *               the interrogations. They do not play.
     * `automatic` — the system runs the timeline and the owner plays too, so
     *               the console has to stop handing them spoilers.
     *
     * Existing games are all gm_led: that is the only mode that existed.
     *
     * finished_at is what makes "the case is over" a fact rather than a
     * convention. Until now a game had no end, which meant results could never
     * be safely revealed to someone who had been playing.
     */
    public function up(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->string('mode')->default('gm_led')->after('case_version');
            $table->timestamp('finished_at')->nullable()->after('paused_seconds_total');
        });

        // In automatic mode the owner gets a player row like everyone else.
        // This flag is what lets the console link them to their own inbox, and
        // it stays false for every player invited by name.
        Schema::table('immersion_players', function (Blueprint $table) {
            $table->boolean('is_owner')->default(false)->after('role_slug');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_players', function (Blueprint $table) {
            $table->dropColumn('is_owner');
        });

        Schema::table('immersion_games', function (Blueprint $table) {
            $table->dropColumn(['mode', 'finished_at']);
        });
    }
};
