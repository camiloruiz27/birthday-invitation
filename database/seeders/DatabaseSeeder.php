<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // The catalog is derived from the installed case manifests rather than
        // hand-written seed data, so there is one source of truth. Same
        // command the deploy runs.
        Artisan::call('platform:sync-cases');
    }
}
