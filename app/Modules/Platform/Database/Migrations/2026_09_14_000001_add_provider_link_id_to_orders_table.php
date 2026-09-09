<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bold's own id for the payment link (e.g. "LNK_...", returned as
     * `payload.payment_link` when the link is created).
     *
     * The webhook is not the only way an order gets resolved: a webhook can
     * be slow, misconfigured, or never arrive, so the buyer's own return trip
     * (PaymentCallbackController) actively asks Bold via
     * GET /online/link/v1/{payment_link} instead of waiting forever. That
     * lookup needs Bold's link id, which nothing captured before this.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('provider_link_id')->nullable()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('provider_link_id');
        });
    }
};
