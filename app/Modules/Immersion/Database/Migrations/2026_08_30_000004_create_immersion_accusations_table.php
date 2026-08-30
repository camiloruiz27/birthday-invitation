<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('immersion_accusations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('immersion_games')->cascadeOnDelete();
            $table->foreignId('player_id')->unique()->constrained('immersion_players')->cascadeOnDelete();
            $table->string('suspect_name');
            $table->text('motive');
            $table->string('weapon');
            $table->timestamp('submitted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('immersion_accusations');
    }
};
