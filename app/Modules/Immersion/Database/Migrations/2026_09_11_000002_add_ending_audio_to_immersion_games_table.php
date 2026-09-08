<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The confession audio belongs to the game, not to a timeline event.
 *
 * It is not part of the case's schedule: it is produced on reveal, at whatever
 * minute the table got there, and it is handed to the Game Master to play out
 * loud rather than mailed to anyone. Modelling it as a timeline event would
 * have put it in every player's inbox, which is the opposite of the intent.
 *
 * The path mirrors timeline audio: a file on disk, with the row holding where
 * it went. Deleting a game already sweeps storage/app/audio for its files.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            // pending | ready | failed. Null means this game never asked for
            // an ending audio.
            $table->string('ending_audio_status', 10)->nullable()->after('ending_revealed_by');
            $table->string('ending_audio_path')->nullable()->after('ending_audio_status');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->dropColumn(['ending_audio_status', 'ending_audio_path']);
        });
    }
};
