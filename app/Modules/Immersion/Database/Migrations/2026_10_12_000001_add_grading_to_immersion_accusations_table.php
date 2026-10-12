<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An accusation is now more than "who": a case can ask extra questions (who
 * caused the breakdown, what part of the provenance was forged...), and once
 * the ending is revealed an AI pass grades every answer against the authored
 * solution. The score is the tie-breaker between players who named the same,
 * correct culprit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('immersion_accusations', function (Blueprint $table) {
            // The case's extra questions, keyed by the question's key.
            $table->json('answers')->nullable()->after('weapon');

            // pending | ready | failed. Null until the ending is revealed.
            $table->string('grading_status', 10)->nullable()->after('was_correct');

            // Total points, culprit included. Null until graded.
            $table->unsignedSmallInteger('score')->nullable()->after('grading_status');

            // Per criterion: key, label, points, max and a one-line comment.
            $table->json('grading')->nullable()->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_accusations', function (Blueprint $table) {
            $table->dropColumn(['answers', 'grading_status', 'score', 'grading']);
        });
    }
};
