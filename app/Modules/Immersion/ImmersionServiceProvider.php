<?php

namespace App\Modules\Immersion;

use App\Modules\Immersion\Ai\Contracts\AccusationGrader;
use App\Modules\Immersion\Ai\Contracts\ConfessionProvider;
use App\Modules\Immersion\Ai\GatewayAccusationGrader;
use App\Modules\Immersion\Ai\NullAccusationGrader;
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
use App\Modules\Immersion\Console\Commands\ImmersionTick;
use App\Modules\Immersion\Console\Commands\LoadTestSeed;
use App\Modules\Immersion\Console\Commands\ProcessImmersionTimeline;
use App\Modules\Immersion\Console\Commands\ReleaseStaleCreditHolds;
use App\Modules\Immersion\Support\MailBudget;
use App\Modules\Immersion\Support\QueueTelemetry;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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

        // Grading the accusations has its own switch: it is a judgement, not
        // dialogue, and a deployment may want one without the other. Off means
        // the scoreboard ranks by the culprit alone.
        $this->app->bind(AccusationGrader::class, function () {
            return $this->aiEnabled('grading')
                ? $this->app->make(GatewayAccusationGrader::class)
                : $this->app->make(NullAccusationGrader::class);
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

    /**
     * Mail goes through one mailbox with a daily allowance (see MailBudget):
     * every message that leaves is counted, and the jobs that send game mail
     * are spread out so the mailbox is never handed a burst.
     */
    private function registerMailGuard(): void
    {
        Event::listen(MessageSent::class, fn () => MailBudget::record());

        RateLimiter::for('immersion-mail', function () {
            $perMinute = (int) config('immersion.mail.per_minute');

            return $perMinute > 0 ? Limit::perMinute($perMinute) : Limit::none();
        });
    }

    public function boot(): void
    {
        $moduleBasePath = __DIR__;

        $this->loadMigrationsFrom($moduleBasePath.'/Database/Migrations');
        $this->loadViewsFrom($moduleBasePath.'/resources/views', 'immersion');

        Route::middleware('web')->group(function () use ($moduleBasePath) {
            $this->loadRoutesFrom($moduleBasePath.'/routes/web.php');
        });

        $this->registerMailGuard();
        QueueTelemetry::register();

        if ($this->app->runningInConsole()) {
            $this->commands([
                ProcessImmersionTimeline::class,
                ImmersionTick::class,
                LoadTestSeed::class,
                GrantCredits::class,
                ReleaseStaleCreditHolds::class,
                ExportAudioScripts::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);

                // One process per minute for the timeline AND the queue (see
                // ImmersionTick): the scheduler runs every command as its own
                // PHP process, and each one costs a MySQL connection out of the
                // host's 500-per-hour allowance.
                //
                // No scheduler-level withoutOverlapping on purpose: it would
                // skip the WHOLE tick, timeline included, for as long as one
                // slow job (text-to-speech: minutes) kept the worker busy, and
                // no game would receive an envelope in the meantime. The tick
                // guards its two halves with their own self-expiring locks
                // instead, so an overlapping tick still moves the timeline.
                //
                // With the sync queue (local, tests) the worker step simply
                // finds nothing to do.
                $schedule->command(ImmersionTick::class)->everyMinute();

                // Credits frozen by games nobody closed come back on their own.
                // Once a day: the window is measured in days (a week by default)
                // and this walks every open reservation, at the cost of a
                // connection each time it runs.
                if (config('immersion.credits.enabled')) {
                    $schedule->command(ReleaseStaleCreditHolds::class)
                        ->dailyAt('04:10')
                        ->withoutOverlapping();
                }
            });
        }
    }
}
