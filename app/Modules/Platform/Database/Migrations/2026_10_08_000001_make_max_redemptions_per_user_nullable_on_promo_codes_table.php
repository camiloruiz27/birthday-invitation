<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `max_redemptions_per_user` was created NOT NULL, but null is what the
     * rest of the code means by "no cap per person": RedeemPromoCode skips the
     * check when it is null, and platform:create-promo-code --max-per-user=0
     * stores null. Every such code failed to insert.
     *
     * Raw SQL because Laravel 9 needs doctrine/dbal to change a column, which
     * this project does not install. MySQL/MariaDB only: any other driver
     * (the SQLite the tests run on) builds the table from the create
     * migration, which is already nullable, so there is nothing to change.
     * Safe to run on a column that is already nullable.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE promo_codes MODIFY max_redemptions_per_user INT UNSIGNED NULL DEFAULT 1');
    }

    /**
     * Left nullable on purpose: putting NOT NULL back would have to invent a
     * cap for every code created without one.
     */
    public function down(): void
    {
    }
};
