<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * What an order cost before and after a code, and where the buyer came
     * from.
     *
     * `amount` stays what is actually charged (what Bold is asked for), so
     * nothing that reads it changes. `list_amount` and `discount_amount` keep
     * the price a code started from, which is otherwise lost, so a promotion's
     * real cost can be measured. All nullable: orders created before this
     * migration have none of it.
     *
     * `attribution` is the campaign the buyer arrived from (utm_*, click ids);
     * `client_ip` and `client_user_agent` are captured when the order is
     * created because the payment webhook that later settles it has no
     * browser to ask. `marketing_consent` is a snapshot of whether the buyer
     * allowed advertising measurement at that moment — it is what lets a
     * server-side conversion event be sent, or not, long after the browser
     * is gone.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('list_amount')->nullable()->after('amount');
            $table->unsignedInteger('discount_amount')->nullable()->after('list_amount');
            $table->foreignId('promo_code_id')->nullable()->after('discount_amount')
                ->constrained('promo_codes')->nullOnDelete();
            $table->json('attribution')->nullable()->after('promo_code_id');
            $table->string('client_ip', 45)->nullable()->after('attribution');
            $table->string('client_user_agent', 255)->nullable()->after('client_ip');
            $table->boolean('marketing_consent')->default(false)->after('client_user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_code_id');
            $table->dropColumn([
                'list_amount',
                'discount_amount',
                'attribution',
                'client_ip',
                'client_user_agent',
                'marketing_consent',
            ]);
        });
    }
};
