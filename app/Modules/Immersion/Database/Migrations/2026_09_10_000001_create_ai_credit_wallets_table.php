<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One AI credit wallet per account.
 *
 * Two separate columns rather than one number, because a credit has two very
 * different states and conflating them is how a table loses its ending:
 *
 *   balance  — free to spend or to freeze
 *   reserved — frozen by a running game, already unavailable to anything else
 *
 * Reserving MOVES credits from balance to reserved; spending draws down
 * reserved; releasing moves the remainder back. Total owned is the sum, and
 * neither column may ever go negative — enforced by unsigned columns, so a
 * bug that tries to overdraw fails loudly at the database instead of quietly
 * handing out free model calls.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_credit_wallets', function (Blueprint $table) {
            $table->id();

            // One wallet per account, created on first use.
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            $table->unsignedInteger('balance')->default(0);
            $table->unsignedInteger('reserved')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_credit_wallets');
    }
};
