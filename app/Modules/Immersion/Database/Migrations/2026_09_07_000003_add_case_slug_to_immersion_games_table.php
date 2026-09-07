<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A game now says which mystery case it is playing, and which version of
     * that case's content it started with. Until this column existed there
     * was exactly one case, so every existing row is a Steve Jacobs game —
     * hence the literal backfill below rather than a config lookup, which
     * could later point somewhere else.
     *
     * case_version is what keeps a game in progress consistent: content is
     * resolved through the version the game started with, so publishing an
     * updated case never rewrites a running investigation.
     */
    public function up(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->string('case_slug')->default('steve-jacobs')->after('name');
            $table->string('case_version')->default('1.0')->after('case_slug');
            $table->index('case_slug');
        });

        DB::table('immersion_games')->update([
            'case_slug' => 'steve-jacobs',
            'case_version' => '1.0',
        ]);
    }

    public function down(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->dropIndex(['case_slug']);
            $table->dropColumn(['case_slug', 'case_version']);
        });
    }
};
