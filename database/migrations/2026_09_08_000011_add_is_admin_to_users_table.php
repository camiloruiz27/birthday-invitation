<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform administrator.
     *
     * A single boolean rather than a roles table: there is exactly one
     * privileged capability today (seeing platform-wide metrics), and a
     * permissions system with one permission in it would be furniture.
     *
     * It is deliberately NOT settable through the web — see
     * `php artisan platform:make-admin`. A privilege that can only be granted
     * from the server is one that cannot be granted by a bug in a form.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }
};
