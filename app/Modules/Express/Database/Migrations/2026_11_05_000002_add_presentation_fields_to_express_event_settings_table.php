<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Public-facing fair copy and availability, editable between events. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('express_event_settings', function (Blueprint $table) {
            $table->string('display_name', 160)->nullable();
            $table->text('tagline')->nullable();
            $table->string('landing_title', 180)->nullable();
            $table->string('landing_emphasis', 180)->nullable();
            $table->string('closed_title', 160)->nullable();
            $table->string('closed_reason', 255)->nullable();
            $table->boolean('active')->nullable();
            $table->timestamp('ends_at')->nullable();
            // Allows a rehearsal to deliberately remove config's close date.
            $table->boolean('ends_at_overridden')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('express_event_settings', function (Blueprint $table) {
            $table->dropColumn([
                'display_name', 'tagline', 'landing_title', 'landing_emphasis',
                'closed_title', 'closed_reason', 'active', 'ends_at', 'ends_at_overridden',
            ]);
        });
    }
};
