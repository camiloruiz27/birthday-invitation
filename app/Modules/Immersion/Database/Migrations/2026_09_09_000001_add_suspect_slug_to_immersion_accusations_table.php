<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * An accusation now names a suspect by slug, not by typed text.
     *
     * Free text cannot be scored: "Daniel Blake", "blake", "D. Blake" and a
     * typo all mean the same thing to a person and nothing to a comparison.
     * The slug is what makes "who got it right" exact.
     *
     * Nullable and deliberately NOT backfilled. Existing rows hold whatever
     * players typed; mapping those to slugs by string similarity would be
     * guessing, and guessing wrong would turn a historical miss into a hit.
     * A null slug scores as "not assessable" — never as a miss.
     *
     * `suspect_name` stays and keeps being written, denormalised from the
     * manifest: old rows stay readable, and an accusation keeps the name the
     * player actually saw even if the case is edited later.
     */
    public function up(): void
    {
        Schema::table('immersion_accusations', function (Blueprint $table) {
            $table->string('suspect_slug')->nullable()->after('player_id');

            // Written once, when the ending is revealed. Persisted rather than
            // derived because case_version does not really pin content:
            // Game::caseDefinition() always reads the manifest on disk, so
            // editing the culprit later would silently rescore finished games.
            $table->boolean('was_correct')->nullable()->after('suspect_slug');

            $table->index('suspect_slug');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_accusations', function (Blueprint $table) {
            $table->dropIndex(['suspect_slug']);
            $table->dropColumn(['suspect_slug', 'was_correct']);
        });
    }
};
