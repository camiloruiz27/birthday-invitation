<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immersion_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('immersion_games')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->uuid('access_token')->unique();
            $table->string('role_slug')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immersion_players');
    }
};
