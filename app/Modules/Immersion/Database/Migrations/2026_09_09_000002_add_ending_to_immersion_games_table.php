<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How a game ends, and whether it has.
     *
     * `ending_type` is chosen when the game is created, because the advanced
     * endings cost AI capacity that has to be reserved up front. Only
     * `classic` is available today; the other two are declared now so the
     * vocabulary never has to be migrated later.
     *
     * Revealing is deliberately NOT the same as finishing. The premium ending
     * hands the Game Master an audio to play at the table, and they close the
     * case afterwards — and finishing is where the AI credit hold will be
     * released, so collapsing the two would make the order of operations
     * fragile.
     *
     * No new `status` value: `status` is an enum of fact consumed by the
     * layout, the badges, the games list and the platform metrics. Revealing
     * is orthogonal to the lifecycle, not another step in it.
     */
    public function up(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->string('ending_type', 20)->default('classic')->after('interrogation_enabled');
            $table->timestamp('ending_revealed_at')->nullable()->after('finished_at');

            // 'auto' when the last accusation triggered it, 'gm' when the Game
            // Master forced it. Drives the wording, and is worth having when
            // someone asks why a table saw the answer early.
            $table->string('ending_revealed_by', 10)->nullable()->after('ending_revealed_at');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->dropColumn(['ending_type', 'ending_revealed_at', 'ending_revealed_by']);
        });
    }
};
