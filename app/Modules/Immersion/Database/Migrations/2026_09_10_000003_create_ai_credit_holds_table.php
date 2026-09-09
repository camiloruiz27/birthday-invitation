<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One reservation per game: the AI capacity frozen while it runs.
 *
 * The unique key on game_id is the real safeguard. Starting a game is the only
 * thing that reserves, and a double click on "Iniciar caso" would otherwise
 * freeze the ceiling twice — the second reservation being pure loss, since only
 * one of them would ever be released.
 *
 * `amount` is the ceiling reserved up front and never changes. `spent` grows as
 * the game consumes it, so `amount - spent` is exactly what goes back when the
 * case is closed. Keeping both means a released hold still records what the
 * game actually cost.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_credit_holds', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_id')->unique()
                ->constrained('immersion_games')->cascadeOnDelete();

            // Denormalised so releasing a hold never has to load the game — it
            // still works when the game is on its way out.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('amount');
            $table->unsignedInteger('spent')->default(0);

            // Set once, when the remainder goes back to the wallet. A released
            // hold can no longer be spent against, which is what stops a game
            // closed by the scheduler from charging for a late question.
            $table->timestamp('released_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_credit_holds');
    }
};
