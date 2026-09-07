<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The question budget used to be a class constant (MAX_QUESTIONS = 5),
     * which meant changing it would silently re-open sessions of games
     * already in progress. Storing it per session anchors the rule a session
     * was created under, and lets a future case definition pick its own
     * budget without touching sessions that already exist.
     */
    public function up(): void
    {
        Schema::table('immersion_interrogation_sessions', function (Blueprint $table) {
            $table->unsignedTinyInteger('max_questions')->default(5)->after('questions_used');
        });

        // Sessions created before this column existed were all played under
        // the old hardcoded limit of 5.
        DB::table('immersion_interrogation_sessions')->update(['max_questions' => 5]);
    }

    public function down(): void
    {
        Schema::table('immersion_interrogation_sessions', function (Blueprint $table) {
            $table->dropColumn('max_questions');
        });
    }
};
