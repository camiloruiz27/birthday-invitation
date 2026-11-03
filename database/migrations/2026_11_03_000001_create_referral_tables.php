<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 60)->unique();
            // Null until the account completes its first paid case order.
            $table->timestamp('eligible_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['active', 'eligible_at']);
        });

        Schema::create('referral_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_code_id')->nullable()->constrained()->nullOnDelete();
            // Denormalized ownership keeps the audit useful even if a code is removed.
            $table->foreignId('referrer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reward_promo_code_id')->nullable()->constrained('promo_codes')->nullOnDelete();
            $table->unsignedTinyInteger('recipient_discount_percent');
            $table->unsignedTinyInteger('referrer_reward_percent');
            $table->string('status', 16)->default('pending');
            $table->json('risk_flags')->nullable();
            $table->timestamp('rewarded_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index(['referrer_user_id', 'status']);
            $table->index(['referred_user_id', 'status']);
            $table->index('status');
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            // Referral rewards are private coupons. Campaign codes keep this null.
            $table->foreignId('assigned_user_id')->nullable()->after('active')
                ->constrained('users')->nullOnDelete();
            $table->index('assigned_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_user_id');
        });

        Schema::dropIfExists('referral_conversions');
        Schema::dropIfExists('referral_codes');
    }
};
