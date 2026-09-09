<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A real-money transaction, in or out of a payment provider.
     *
     * Deliberately not what access is checked against — that stays the
     * Entitlement (see its own migration). This is the accounting record: who
     * tried to buy what, for how much, and what the provider said about it.
     * A user can have a dozen abandoned or rejected orders behind one
     * successful entitlement.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // What is being bought. A credit package is not a database row
            // (it lives in config/platform.php), so it is addressed by its
            // config id rather than a foreign key.
            $table->string('type');
            $table->foreignId('mystery_case_id')->nullable()->constrained()->nullOnDelete();
            $table->string('credit_package_id')->nullable();

            // Snapshot at purchase time: a later change to the package's
            // credit amount must not retroactively change what an already
            // placed order is worth.
            $table->unsignedInteger('credits_granted')->nullable();

            // Same convention as mystery_cases.price_amount: COP has no
            // practical cents, so this is whole currency units.
            $table->unsignedInteger('amount')->default(0);
            $table->char('currency', 3)->default('COP');

            $table->string('status')->default('pending');

            // What we hand the provider and get back on its webhook. Never
            // the auto-incrementing id, so a payment link does not reveal how
            // many orders exist.
            $table->string('reference')->unique();
            $table->string('provider')->default('bold');
            $table->string('provider_payment_id')->nullable();
            $table->text('checkout_url')->nullable();

            // The last webhook payload received, kept for support and
            // reconciliation rather than trusted for anything the app decides
            // on its own.
            $table->json('raw_webhook')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // "What's pending for this account" and "history for this
            // account" are the two queries this table actually serves.
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
