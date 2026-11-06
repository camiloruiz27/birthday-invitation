<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL DDL is not transactional. If a previous attempt failed while
        // adding an index, these new tables may have been left half-created
        // even though Laravel did not record the migration. Cleaning only
        // this migration's uncommitted tables makes the retry deterministic.
        Schema::dropIfExists('landing_campaign_events');
        Schema::dropIfExists('landing_campaign_cases');
        Schema::dropIfExists('landing_campaigns');

        Schema::create('landing_campaigns', function (Blueprint $table) {
            $table->id();
            // A promotion's commercial rules remain on promo_codes. This row
            // only decides how that valid offer is presented.
            $table->foreignId('promo_code_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->string('eyebrow', 120)->nullable();
            $table->string('headline', 180)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('landing_campaign_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mystery_case_id')->constrained()->cascadeOnDelete();
            $table->string('placement', 12); // paid | free
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('recommended')->default(false);
            $table->timestamps();

            // Explicit short names: MySQL limits identifiers to 64 chars.
            $table->unique(['landing_campaign_id', 'mystery_case_id'], 'lcc_campaign_case_unique');
            $table->index(['landing_campaign_id', 'placement', 'sort_order'], 'lcc_campaign_place_order_idx');
        });

        Schema::create('landing_campaign_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landing_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mystery_case_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // IDs make an event attributable without persisting access tokens,
            // messages or other player data.
            $table->unsignedBigInteger('game_id')->nullable();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->string('event', 40);
            $table->char('dedupe_key', 64)->unique();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['landing_campaign_id', 'event', 'occurred_at'], 'lce_campaign_event_time_idx');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('landing_campaign_id')->nullable()->after('promo_code_id')
                ->constrained()->nullOnDelete();
            $table->index(['landing_campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['landing_campaign_id', 'status']);
            $table->dropConstrainedForeignId('landing_campaign_id');
        });

        Schema::dropIfExists('landing_campaign_events');
        Schema::dropIfExists('landing_campaign_cases');
        Schema::dropIfExists('landing_campaigns');
    }
};
