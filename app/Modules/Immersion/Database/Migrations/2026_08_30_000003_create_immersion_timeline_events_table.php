<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immersion_timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('immersion_games')->cascadeOnDelete();
            $table->string('type');
            $table->unsignedInteger('trigger_offset_minutes');
            $table->string('title');
            $table->string('source_file')->nullable();
            $table->longText('body_markdown')->nullable();
            $table->text('audio_script')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('delivery_mode')->default('all');
            $table->string('target_role_slug')->nullable();
            $table->foreignId('delivered_to_player_id')->nullable()->constrained('immersion_players')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immersion_timeline_events');
    }
};
