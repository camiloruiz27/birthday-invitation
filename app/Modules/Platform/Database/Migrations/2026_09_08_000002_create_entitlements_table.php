<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Who may play what.
     *
     * This is the ONLY thing access is ever checked against — deliberately
     * not a payment or an order. A purchase, a manual grant and a future
     * subscription all end up writing an entitlement, so the commercial model
     * can change without touching a single authorization check.
     */
    public function up(): void
    {
        Schema::create('entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mystery_case_id')->constrained('mystery_cases')->cascadeOnDelete();

            // How this access came to be, for support and reporting.
            $table->string('source')->default('purchase');
            $table->timestamp('granted_at');

            // Null means permanent, which is the current model. A future
            // subscription would set this and let it lapse.
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Access is a fact, not a log: one row per user per case.
            $table->unique(['user_id', 'mystery_case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entitlements');
    }
};
