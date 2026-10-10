<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Campaign codes are handed out often and with different values, so a
     * code needs to be able to lapse on its own and to be limited to what it
     * was meant for.
     *
     * `expires_at` null means it never lapses. `applies_to` null means the
     * code works on a case AND on a credit package (what every existing code
     * does today); 'case' or 'credits' narrows it to one of the two. Both are
     * enforced in RedeemPromoCode, not as database constraints, for the same
     * reason as the rest of this table's rules.
     */
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('active');
            $table->string('applies_to', 16)->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'applies_to']);
        });
    }
};
