<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Attribution frozen per room, plus anonymous completion-CTA clicks. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('express_rooms', function (Blueprint $table) {
            $table->string('promo_code', 60)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('cta_path', 500)->nullable();
            $table->boolean('campaign_snapshotted')->default(false);
            $table->index(['event_slug', 'promo_code']);
        });

        Schema::create('express_cta_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('express_rooms')->cascadeOnDelete();
            $table->foreignId('player_id')->unique()->constrained('express_players')->cascadeOnDelete();
            $table->timestamps();

            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('express_cta_clicks');

        Schema::table('express_rooms', function (Blueprint $table) {
            $table->dropIndex(['event_slug', 'promo_code']);
            $table->dropColumn(['promo_code', 'utm_medium', 'utm_campaign', 'cta_path', 'campaign_snapshotted']);
        });
    }
};
