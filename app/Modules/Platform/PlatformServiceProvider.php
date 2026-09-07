<?php

namespace App\Modules\Platform;

use App\Modules\Platform\Console\Commands\ClaimGames;
use App\Modules\Platform\Console\Commands\GrantCaseAccessCommand;
use App\Modules\Platform\Console\Commands\MakeAdmin;
use App\Modules\Platform\Console\Commands\SyncMysteryCases;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * The platform layer around the game engine: catalog, ownership and commerce.
 *
 * Dependency direction is one-way. Platform reads the engine's case manifests
 * (to publish them as products) and knows about games; the Immersion engine
 * knows nothing about products, prices or accounts. The join between the two
 * is the case slug, never a foreign key into a platform table — that is what
 * keeps the engine runnable on its own.
 */
class PlatformServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        Route::middleware('web')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncMysteryCases::class,
                GrantCaseAccessCommand::class,
                ClaimGames::class,
                MakeAdmin::class,
            ]);
        }
    }
}
