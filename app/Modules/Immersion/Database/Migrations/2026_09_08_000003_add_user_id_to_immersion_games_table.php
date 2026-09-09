<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A game now has an owning Game Master account.
     *
     * Nullable on purpose: games created under the old shared-password regime
     * have no account behind them, and destroying them to satisfy a NOT NULL
     * would lose real play data. They stay claimable — see
     * `php artisan platform:claim-games`.
     */
    public function up(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('immersion_games', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
