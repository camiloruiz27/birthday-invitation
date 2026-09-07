<?php

namespace App\Modules\Immersion;

use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Console\Commands\ProcessImmersionTimeline;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Todo el modulo "Immersion" (juego de misterio "Que le sucedio a Steve
 * Jacobs?") vive bajo app/Modules/Immersion y se registra desde este unico
 * provider. Quitar esta clase de config/app.php y borrar la carpeta
 * app/Modules/Immersion desactiva/elimina el modulo por completo, sin tocar
 * el resto de la aplicacion.
 */
class ImmersionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Case manifests are plain arrays that never change during a request,
        // so discovery and parsing happen once.
        $this->app->singleton(CaseRegistry::class);
    }

    public function boot(): void
    {
        $moduleBasePath = __DIR__;

        $this->loadMigrationsFrom($moduleBasePath.'/Database/Migrations');
        $this->loadViewsFrom($moduleBasePath.'/resources/views', 'immersion');

        Route::middleware('web')->group(function () use ($moduleBasePath) {
            $this->loadRoutesFrom($moduleBasePath.'/routes/web.php');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessImmersionTimeline::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $schedule->command(ProcessImmersionTimeline::class)->everyMinute();
            });
        }
    }
}
