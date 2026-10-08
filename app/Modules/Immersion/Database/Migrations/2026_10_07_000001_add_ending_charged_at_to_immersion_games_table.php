<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a game's paid ending was charged.
 *
 * The ending used to be charged when it was revealed, before the model had
 * produced anything. It is now charged once the first epilogue is mailed or the
 * confession recording is ready, and the epilogue is generated per player, so
 * "charge once for the table" needs a marker that many jobs can race for. The
 * conditional UPDATE on this column is that lock.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->timestamp('ending_charged_at')->nullable()->after('ending_revealed_by');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->dropColumn('ending_charged_at');
        });
    }
};
