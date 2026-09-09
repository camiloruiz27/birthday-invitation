<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only log of every credit movement.
 *
 * The wallet holds the current numbers; this holds how they got there. Anything
 * that can be spent needs an audit trail: when a Game Master says their credits
 * vanished, the wallet alone cannot answer, and recomputing a balance from
 * scratch is only possible if every movement was written down.
 *
 * Never updated and never deleted. A correction is a new entry with the
 * opposite delta, not an edit — a ledger you can rewrite is not a ledger.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_credit_ledger', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Which game caused this, when one did. Nulled rather than cascaded
            // on delete: deleting a game must not erase the record of what it
            // consumed.
            $table->foreignId('game_id')->nullable()
                ->constrained('immersion_games')->nullOnDelete();

            // grant | topup | reserve | spend | release | adjust
            $table->string('reason', 20);

            // Signed, from the account's point of view: what it did to the
            // total the account owns. A reserve is 0 — the credits did not
            // leave, they only froze — while a spend is negative.
            $table->integer('delta');

            // The wallet as it stood immediately after this entry, so a
            // disputed balance can be traced without replaying every row.
            $table->unsignedInteger('balance_after');
            $table->unsignedInteger('reserved_after');

            // Free-text context for support: the package bought, the suspect
            // questioned, the command that granted.
            $table->string('note')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_credit_ledger');
    }
};
