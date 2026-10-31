<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exported_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('contacts_count');
            $table->json('filters')->nullable();
            $table->timestamp('exported_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_exports');
    }
};
