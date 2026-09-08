<?php

namespace App\Modules\Immersion;

use App\Modules\Immersion\Ai\Contracts\ConfessionProvider;
use App\Modules\Immersion\Ai\Contracts\EpilogueProvider;
use App\Modules\Immersion\Ai\Contracts\InterrogationProvider;
use App\Modules\Immersion\Ai\Contracts\SpeechProvider;
use App\Modules\Immersion\Ai\GatewayConfessionProvider;
use App\Modules\Immersion\Ai\GatewayEpilogueProvider;
use App\Modules\Immersion\Ai\GatewayInterrogationProvider;
use App\Modules\Immersion\Ai\GatewaySpeechProvider;
use App\Modules\Immersion\Ai\NullConfessionProvider;
use App\Modules\Immersion\Ai\NullEpilogueProvider;
use App\Modules\Immersion\Ai\NullInterrogationProvider;
use App\Modules\Immersion\Ai\NullSpeechProvider;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Console\Commands\ExportAudioScripts;
use App\Modules\Immersion\Console\Commands\GrantCredits;
use App\Modules\Immersion\Console\Commands\ProcessImmersionTimeline;
use App\Modules\Immersion\Console\Commands\ReleaseStaleCreditHolds;
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

        $this->registerAiProviders();
    }

    /**
     * Binds the AI capabilities to either the gateway or the no-AI providers.
     *
     * Each is switchable on its own: a deployment can keep the interrogations
     * and drop the generated audio, or run a case with no AI at all. The
     * gateway is only used when it is actually configured — an unconfigured
     * install silently falling back is how you end up debugging "why are the
     * suspects so evasive".
     */
    private function registerAiProviders(): void
    {
        $this->app->bind(InterrogationProvider::class, function () {
            return $this->aiEnabled('interrogation')
                ? $this->app->make(GatewayInterrogationProvider::class)
                : $this->app->make(NullInterrogationProvider::class);
        });

        $this->app->bind(SpeechProvider::class, function () {
            return $this->aiEnabled('speech')
                ? $this->app->make(GatewaySpeechProvider::class)
                : $this->app->make(NullSpeechProvider::class);
        });

        // The personalised epilogue rides on the same switch as the
        // interrogation: both are the gateway writing dialogue in character,
        // and a deployment that does not trust one should not get the other.
        $this->app->bind(EpilogueProvider::class, function () {
            return $this->aiEnabled('interrogation')
                ? $this->app->make(GatewayEpilogueProvider::class)
                : $this->app->make(NullEpilogueProvider::class);
        });

        // Same switch again: personalising the confession is a text rewrite,
        // not speech. Off means the authored script is spoken unchanged, which
        // is still a complete ending.
        $this->app->bind(ConfessionProvider::class, function () {
            return $this->aiEnabled('interrogation')
                ? $this->app->make(GatewayConfessionProvider::class)
                : $this->app->make(NullConfessionProvider::class);
        });
    }

    private function aiEnabled(string $capability): bool
    {
        if (! config("immersion.ai.{$capability}_enabled")) {
            return false;
        }

        $apiKey = (string) config('immersion.ai.api_key');

        return config('immersion.ai.base_url')
            && $apiKey !== ''
            && ! str_starts_with($apiKey, 'CHANGE_ME');
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
                GrantCredits::class,
                ReleaseStaleCreditHolds::class,
                ExportAudioScripts::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);

                // withoutOverlapping: the command only enqueues now, but a
                // slow database must never let two ticks pile up on top of
                // each other.
                $schedule->command(ProcessImmersionTimeline::class)
                    ->everyMinute()
                    ->withoutOverlapping();

                // Shared hosting has no daemon, so the worker is a scheduled
                // short-lived process: it drains the queue and exits before
                // the next minute's tick starts.
                if (config('queue.default') !== 'sync') {
                    $schedule->command('queue:work --stop-when-empty --max-time=50 --tries=2 --quiet')
                        ->everyMinute()
                        ->withoutOverlapping();
                }

                // Credits frozen by games nobody closed come back on their own.
                // Hourly rather than per minute: the window is measured in days
                // and this walks every open reservation.
                if (config('immersion.credits.enabled')) {
                    $schedule->command(ReleaseStaleCreditHolds::class)
                        ->hourly()
                        ->withoutOverlapping();
                }
            });
        }
    }
}
