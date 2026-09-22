<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A gift that lets the redeemer pick which case they get, instead of one
     * fixed by `grants_case_slug` — mutually exclusive with it, enforced by
     * CreatePromoCode/CreatePromoCodeBatch, not a database constraint, same
     * reasoning as the gift/discount split on this table.
     */
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->boolean('grants_any_case')->default(false)->after('grants_case_slug');
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn('grants_any_case');
        });
    }
};
