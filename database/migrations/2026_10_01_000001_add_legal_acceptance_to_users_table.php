<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Proof of what a user accepted, and when.
     *
     * Decreto 1074 de 2015 (chapter 25, "prueba de la autorizacion") obliges
     * the controller to keep evidence of the data-processing authorization,
     * so a checkbox that leaves no trace would be worth little. The versions
     * say WHICH text was shown.
     *
     * All nullable: accounts created before this migration never accepted
     * anything through this flow, and pretending otherwise would be false.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('terms_accepted_at')->nullable()->after('is_admin');
            $table->string('terms_version', 20)->nullable()->after('terms_accepted_at');
            $table->timestamp('privacy_accepted_at')->nullable()->after('terms_version');
            $table->string('privacy_version', 20)->nullable()->after('privacy_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'terms_accepted_at',
                'terms_version',
                'privacy_accepted_at',
                'privacy_version',
            ]);
        });
    }
};
