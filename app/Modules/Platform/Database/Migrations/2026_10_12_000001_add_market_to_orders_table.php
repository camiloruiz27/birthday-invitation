<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which market an order was sold in, and the COP price it was derived from.
     *
     * COP is the base price of everything. An international order is charged
     * in USD (`amount`/`currency` hold what Bold is asked for, USD in cents),
     * but the COP it came from is what a discount was applied to and what
     * finance reconciles against, so it is kept: `base_list_amount` before a
     * code, `base_amount` after it. `pricing_snapshot` records the rate, fee
     * and markup used so a price can be explained long after the config moves.
     *
     * All nullable: every order before this migration was a Colombian COP sale
     * and needs none of it.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('market', 8)->nullable()->after('currency');
            $table->unsignedInteger('base_list_amount')->nullable()->after('market');
            $table->unsignedInteger('base_amount')->nullable()->after('base_list_amount');
            $table->json('pricing_snapshot')->nullable()->after('base_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['market', 'base_list_amount', 'base_amount', 'pricing_snapshot']);
        });
    }
};
