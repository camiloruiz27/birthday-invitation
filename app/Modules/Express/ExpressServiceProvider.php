<?php

namespace App\Modules\Express;

use App\Modules\Express\Ai\ExpressInterrogationProvider;
use App\Modules\Express\Ai\GatewayExpressInterrogationProvider;
use App\Modules\Express\Console\ExportLeadsCommand;
use App\Modules\Express\Console\PruneCommand;
use App\Modules\Express\Console\ReportCommand;
use App\Modules\Express\Support\AuthoredMatcher;
use App\Modules\Express\Support\ExpressCaseRegistry;
use App\Modules\Express\Support\QuestionMatcher;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * El modulo "Express": la demo de feria (mini-casos de 5 minutos, sin
 * registro). Vive entero bajo app/Modules/Express; quitar esta clase de
 * config/app.php y borrar la carpeta lo desactiva sin tocar nada mas.
 * Ver app/Modules/Express/README.md.
 */
class ExpressServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The case files never change during a request: read and validated once.
        $this->app->singleton(ExpressCaseRegistry::class);

        // Free-text questions are matched against the author's own paraphrases
        // (the topic a question is about, and the whole answer when the AI is off).
        $this->app->bind(QuestionMatcher::class, AuthoredMatcher::class);

        // The suspect is played by the AI gateway when it is configured and
        // EXPRESS_AI_ENABLED is on; otherwise the author's own text answers.
        // Decided per call (available()), so changing .env needs no redeploy.
        $this->app->bind(ExpressInterrogationProvider::class, GatewayExpressInterrogationProvider::class);
    }

    public function boot(): void
    {
        $base = __DIR__;

        $this->loadMigrationsFrom($base.'/Database/Migrations');

        Route::middleware('web')->group(fn () => $this->loadRoutesFrom($base.'/routes/web.php'));

        // The poll opens no session: see routes/state.php.
        Route::group([], fn () => $this->loadRoutesFrom($base.'/routes/state.php'));

        $this->registerRateLimits();

        if ($this->app->runningInConsole()) {
            $this->commands([ReportCommand::class, ExportLeadsCommand::class, PruneCommand::class]);

            $this->app->booted(function () {
                // Hourly, from the 5-minute `schedule:run` that already exists:
                // each extra scheduled command costs a database connection.
                $this->app->make(Schedule::class)->command(PruneCommand::class)->hourly()->withoutOverlapping();
            });
        }
    }

    /**
     * "attempts,minutes" strings in config/express.php, with a safe fallback
     * if one is malformed (a typo must not remove a limit).
     *
     * Per IP where nothing identifies the visitor yet (creating a room,
     * joining) — generous, because a fair shares one Wi-Fi and so one IP —
     * and per player token everywhere else.
     */
    private function registerRateLimits(): void
    {
        $limit = function (string $key, string $fallback): array {
            $pair = explode(',', (string) config("express.rate_limits.{$key}", $fallback));
            $attempts = (int) ($pair[0] ?? 0);
            $minutes = (int) ($pair[1] ?? 0);

            return $attempts < 1 || $minutes < 1
                ? array_map('intval', explode(',', $fallback))
                : [$attempts, $minutes];
        };

        $perIp = fn (string $name, string $key, string $fallback) => RateLimiter::for(
            $name,
            function (Request $request) use ($limit, $key, $fallback, $name) {
                [$attempts, $minutes] = $limit($key, $fallback);

                return Limit::perMinutes($minutes, $attempts)->by($name.':'.$request->ip());
            },
        );

        $perToken = fn (string $name, string $key, string $fallback) => RateLimiter::for(
            $name,
            function (Request $request) use ($limit, $key, $fallback, $name) {
                [$attempts, $minutes] = $limit($key, $fallback);

                return Limit::perMinutes($minutes, $attempts)->by($name.':'.sha1((string) $request->route('token')));
            },
        );

        $perIp('express-create', 'create', '60,1');
        $perIp('express-join', 'join', '120,1');
        $perToken('express-ask', 'ask', '12,1');
        $perToken('express-act', 'act', '30,1');
        $perToken('express-state', 'state', '90,1');
    }
}
