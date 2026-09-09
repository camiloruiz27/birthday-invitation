<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The personalised epilogue lands on the accusation, not on the player.
 *
 * It is a reply to what that person accused, so it only exists where an
 * accusation does — a player who never submitted has nothing to be answered.
 * Storing the text rather than only mailing it means the reveal page can show
 * it too: an email is easy to lose and impossible to re-read at the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('immersion_accusations', function (Blueprint $table) {
            // pending | ready | failed. Null for every game that did not use
            // this ending, which is the normal case.
            $table->string('epilogue_status', 10)->nullable()->after('was_correct');
            $table->text('epilogue_body')->nullable()->after('epilogue_status');
            $table->timestamp('epilogue_sent_at')->nullable()->after('epilogue_body');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_accusations', function (Blueprint $table) {
            $table->dropColumn(['epilogue_status', 'epilogue_body', 'epilogue_sent_at']);
        });
    }
};
