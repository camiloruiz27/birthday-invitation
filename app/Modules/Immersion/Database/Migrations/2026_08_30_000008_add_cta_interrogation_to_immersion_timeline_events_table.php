<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('immersion_timeline_events', function (Blueprint $table) {
            $table->boolean('cta_interrogation')->default(false)->after('delivery_mode');
        });
    }

    public function down(): void
    {
        Schema::table('immersion_timeline_events', function (Blueprint $table) {
            $table->dropColumn('cta_interrogation');
        });
    }
};
