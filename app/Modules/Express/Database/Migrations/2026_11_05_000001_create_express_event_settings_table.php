<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Small per-event overrides that an administrator may change between fairs.
 * Event identity, schedule and UTM parameters deliberately remain in
 * config/express.php; the promotion is operational data that changes often.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('express_event_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_slug', 60)->unique();
            // Null is meaningful: the event deliberately finishes without a
            // promotional code, rather than falling back to config.
            $table->string('promo_code', 60)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('express_event_settings');
    }
};
