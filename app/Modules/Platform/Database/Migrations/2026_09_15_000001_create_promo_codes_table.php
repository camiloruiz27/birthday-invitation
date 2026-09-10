<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A redeemable code, created by hand from the console (rare — a handful
     * of campaigns a year, never through a screen; see the payment gateway
     * plan for why administrative writes in this app stay console-only).
     *
     * Deliberately flexible columns rather than a rigid "type" enum: a code
     * is either a GIFT (grants_case_slug and/or grants_credits — the two
     * together is exactly the "case + credits" bundle) or a DISCOUNT
     * (discount_type + discount_value), never both. That split is enforced
     * by CreatePromoCode, not a database constraint — mixing them makes no
     * product sense ("free case AND 20% off"), so it is caught before a row
     * is ever written rather than modeled in SQL.
     */
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();

            // Stored and compared uppercase, so "lanzamiento2026" and
            // "LANZAMIENTO2026" are the same code to a buyer typing it in.
            $table->string('code')->unique();

            // Gift: handed over directly, no Order involved. Both may be set
            // at once (the gift bundle).
            $table->string('grants_case_slug')->nullable();
            $table->unsignedInteger('grants_credits')->nullable();

            // Discount: reduces an Order's amount before Bold ever sees it.
            $table->string('discount_type')->nullable();
            $table->unsignedInteger('discount_value')->nullable();

            // Null means no cap on that dimension.
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('max_redemptions_per_user')->default(1);

            // Denormalized running total: checking "is this code exhausted?"
            // on every redemption attempt should not require counting rows
            // in promo_code_redemptions every time.
            $table->unsignedInteger('redemptions_count')->default(0);

            $table->boolean('active')->default(true);

            // A human's reminder of what campaign this was for — these are
            // created rarely and read back rarely, so nothing else records
            // why a code exists.
            $table->string('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
