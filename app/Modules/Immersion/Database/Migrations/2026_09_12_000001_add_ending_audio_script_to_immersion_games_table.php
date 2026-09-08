<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the culprit actually says in this game's confession.
 *
 * The confession stopped being the same recording for everyone: it is the
 * authored script rewritten around the questions THIS table put to the
 * culprit, so it differs per game and has to be stored.
 *
 * Kept separately from the audio path so a synthesis that fails does not throw
 * the writing away — the Game Master can still read it out at the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->text('ending_audio_script')->nullable()->after('ending_audio_path');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->dropColumn('ending_audio_script');
        });
    }
};
