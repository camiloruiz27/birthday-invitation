<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backing table for the database queue driver.
     *
     * Until now QUEUE_CONNECTION was `sync`, which meant the every-minute
     * timeline cron ran text-to-speech (up to 60s) and SMTP delivery inline:
     * a tick with several due events could outlast its own minute. With a real
     * queue the cron only enqueues, and a worker does the slow work.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
