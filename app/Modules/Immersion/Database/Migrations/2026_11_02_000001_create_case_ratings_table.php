<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_ratings', function (Blueprint $table) {
            $table->id();
            $table->string('case_slug')->index();
            // Ratings remain useful as anonymous aggregates after account or
            // game cleanup, so both foreign references are nullable on delete.
            $table->foreignId('game_id')->nullable()->constrained('immersion_games')->nullOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('immersion_players')->nullOnDelete();
            $table->unsignedTinyInteger('rating_half_steps');
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_ratings');
    }
};
