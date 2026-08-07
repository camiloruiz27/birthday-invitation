<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('couple_experience_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day');
            $table->unsignedTinyInteger('level');
            $table->string('activity_key');
            $table->string('status');
            $table->timestamps();

            $table->unique(['user_id', 'activity_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couple_experience_progress');
    }
};
