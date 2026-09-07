<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether an event's audio is on its way, ready, or gave up.
     *
     * Generation moved off the web request and onto the queue, so "audio_path
     * is still null" stopped being enough to tell the Game Master what is
     * happening: it means both "we are working on it" and "it failed". This
     * column separates the two.
     */
    public function up(): void
    {
        Schema::table('immersion_timeline_events', function (Blueprint $table) {
            $table->string('audio_status')->nullable()->after('audio_path');
        });

        // Existing audio events: a file on disk means ready, anything else was
        // a failure nobody retried.
        DB::table('immersion_timeline_events')
            ->where('type', 'audio_email')
            ->update([
                'audio_status' => DB::raw("case when audio_path is null then 'failed' else 'ready' end"),
            ]);
    }

    public function down(): void
    {
        Schema::table('immersion_timeline_events', function (Blueprint $table) {
            $table->dropColumn('audio_status');
        });
    }
};
