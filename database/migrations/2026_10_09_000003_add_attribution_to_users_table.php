<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Where an account came from: the first and last campaign touch captured
     * before it registered, plus a pending promo code and purchase intent so a
     * visitor who opens the verification mail on another device still lands
     * where they meant to go. Nullable — existing accounts have none.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('attribution')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('attribution');
        });
    }
};
