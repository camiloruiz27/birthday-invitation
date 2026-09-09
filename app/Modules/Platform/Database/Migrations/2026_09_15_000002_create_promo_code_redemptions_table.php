<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One immutable row per redemption — what enforces "one use per person"
     * (RedeemPromoCode checks this table, locked, before honoring a claim)
     * and doubles as the audit trail for "why does this user already have
     * this case?".
     */
    public function up(): void
    {
        Schema::create('promo_code_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Only meaningful for a discount redemption — which purchase it
            // was applied to. Null for a pure gift: nothing was bought.
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('created_at');

            $table->index(['promo_code_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_redemptions');
    }
};
