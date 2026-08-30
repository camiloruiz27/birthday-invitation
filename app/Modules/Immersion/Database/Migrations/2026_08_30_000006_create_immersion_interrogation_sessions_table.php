<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immersion_interrogation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('immersion_games')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('immersion_players')->cascadeOnDelete();
            $table->string('suspect_slug');
            $table->timestamp('started_at');
            $table->unsignedTinyInteger('questions_used')->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->boolean('transcript_revealed')->default(false);
            $table->timestamps();

            $table->unique(['player_id', 'suspect_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immersion_interrogation_sessions');
    }
};
