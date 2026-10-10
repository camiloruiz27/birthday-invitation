<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Copy written for an advertisement that points at this case: a hook, up
     * to three selling points and a call to action. Content, so it is synced
     * from the manifest on every run like the tagline and description; null
     * for a case nobody has written ad copy for, in which case the ad
     * landing falls back to the tagline and the case's own facts.
     */
    public function up(): void
    {
        Schema::table('mystery_cases', function (Blueprint $table) {
            $table->json('ad')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('mystery_cases', function (Blueprint $table) {
            $table->dropColumn('ad');
        });
    }
};
