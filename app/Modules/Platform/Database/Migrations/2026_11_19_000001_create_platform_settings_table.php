<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Switches an administrator flips from the panel, as opposed to the ones set
 * in the server's .env. A row exists only once someone has chosen: with no
 * row, the .env value stays the default.
 *
 * Today it holds one switch (the first-game offer email), kept as a key/value
 * table so the next one does not need its own migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            // Who last changed it. Nulled, not cascaded, if that account is deleted.
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
