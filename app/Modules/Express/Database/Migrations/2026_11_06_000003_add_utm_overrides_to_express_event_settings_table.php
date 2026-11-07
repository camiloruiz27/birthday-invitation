<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Optional campaign parameters for the public Express completion link. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('express_event_settings', function (Blueprint $table) {
            // An empty string intentionally removes the parameter from the
            // final CTA, while null keeps the value from config/express.php.
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('express_event_settings', function (Blueprint $table) {
            $table->dropColumn(['utm_medium', 'utm_campaign']);
        });
    }
};
