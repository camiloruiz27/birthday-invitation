<?php

namespace App\Modules\Platform;

use App\Modules\Platform\Console\Commands\ClaimGames;
use App\Modules\Platform\Console\Commands\CreatePromoCode;
use App\Modules\Platform\Console\Commands\CronStatus;
use App\Modules\Platform\Console\Commands\ExpireStaleOrders;
use App\Modules\Platform\Console\Commands\GrantCaseAccessCommand;
use App\Modules\Platform\Console\Commands\ListPromoCodes;
use App\Modules\Platform\Console\Commands\MakeAdmin;
use App\Modules\Platform\Console\Commands\ReconcileOrder;
use App\Modules\Platform\Console\Commands\ReconcilePendingOrders;
use App\Modules\Platform\Console\Commands\SyncMysteryCases;
use App\Modules\Platform\Payments\BoldPaymentProvider;
use App\Modules\Platform\Payments\Contracts\PaymentProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
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
    /** Where the scheduler leaves proof that it ran. Read by platform:cron-status. */
    public const HEARTBEAT_KEY = 'platform:scheduler-heartbeat';

    /**
     * The throttle behind every screen where a promo code can be typed.
     *
     * A named limiter rather than a plain `throttle:10,10` because two of
     * those screens are the ordinary checkout review — reached with no code
     * at all by every real buyer. Charging them an attempt would rate-limit
     * people for trying to pay. `Limit::none()` lets that traffic through
     * untouched and only starts counting once a code is actually submitted.
     *
     * Two limits, not one: the per-account cap is the tight one, and the
     * per-IP cap is what stops the obvious way around it — registering
     * throwaway accounts to get a fresh allowance each time.
     */
    private function registerPromoRateLimiter(): void
    {
        RateLimiter::for('promo', function (Request $request) {
            // `code` is the gift screen's field, `promo_code` the checkout
            // review's. Either one means a guess is being made.
            if (! $request->filled('code') && ! $request->filled('promo_code')) {
                return Limit::none();
            }

            return [
                $this->limitFrom('promo', '10,10')->by('promo-user:'.$request->user()?->id),
                $this->limitFrom('promo_ip', '30,10')->by('promo-ip:'.$request->ip()),
            ];
        });
    }

    /**
     * Reads one of the config's "attempts,minutes" pairs into a Limit,
     * falling back to $default if it is missing or malformed — a typo in the
     * environment must not silently remove the limit altogether.
     */
    private function limitFrom(string $key, string $default): Limit
    {
        $pair = explode(',', (string) config("platform.rate_limits.{$key}", $default));

        $attempts = (int) ($pair[0] ?? 0);
        $minutes = (int) ($pair[1] ?? 0);

        if ($attempts < 1 || $minutes < 1) {
            [$attempts, $minutes] = array_map('intval', explode(',', $default));
        }

        return Limit::perMinutes($minutes, $attempts);
    }

    public function register(): void
    {
        // Bound directly, not behind a config switch like the AI providers:
        // there is no safe "Null" fallback for real money (see
        // PaymentProvider's docblock). Whether a real checkout is offered at
        // all is decided in the controllers via platform.payments.enabled.
        $this->app->bind(PaymentProvider::class, BoldPaymentProvider::class);
    }

    /**
     * The confirmation mail, written here instead of shipping Laravel's.
     *
     * The stock notification is in English and signs off as "Laravel". This
     * is the first mail a buyer ever receives from the platform, and one that
     * arrives in the wrong language from an unfamiliar name is one people
     * report as phishing — which is the opposite of what a verification mail
     * is for.
     */
    private function registerVerificationMail(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Confirma tu correo — MisterioCode')
                ->greeting("Hola, {$notifiable->name}")
                ->line('Confirma que esta dirección es tuya para poder adquirir casos y canjear códigos.')
                ->action('Confirmar mi correo', $url)
                ->line('El enlace caduca en 60 minutos.')
                ->line('Si no creaste esta cuenta, puedes ignorar este mensaje: no se hará nada.')
                ->salutation('MisterioCode');
        });
    }

    /**
     * The password reset mail, in Spanish and signed as MisterioCode.
     *
     * Same reasoning as the verification mail above, and it matters more
     * here: this one carries a link that changes a password, arrives
     * unprompted as far as the reader can tell, and goes out from a sender
     * named after the case's fictional police department. In English and
     * signed "Laravel" it is indistinguishable from a phishing attempt, and
     * the safe reaction — deleting it — locks the person out of the account
     * they just paid for.
     *
     * The expiry is read from config rather than written into the sentence,
     * so changing auth.passwords.users.expire cannot leave the mail telling
     * people something that is no longer true.
     */
    private function registerPasswordResetMail(): void
    {
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $minutes = config('auth.passwords.users.expire');

            return (new MailMessage)
                ->subject('Restablece tu contraseña — MisterioCode')
                ->greeting("Hola, {$notifiable->name}")
                ->line('Recibimos una solicitud para cambiar la contraseña de tu cuenta.')
                ->action('Elegir una contraseña nueva', route('password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ]))
                ->line("El enlace caduca en {$minutes} minutos.")
                ->line('Si no fuiste tú, puedes ignorar este mensaje: tu contraseña actual sigue funcionando y no se hará ningún cambio.')
                ->salutation('MisterioCode');
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        $this->registerPromoRateLimiter();
        $this->registerVerificationMail();
        $this->registerPasswordResetMail();

        Route::middleware('web')->group(function () {
            $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncMysteryCases::class,
                CronStatus::class,
                GrantCaseAccessCommand::class,
                ClaimGames::class,
                MakeAdmin::class,
                ExpireStaleOrders::class,
                ReconcileOrder::class,
                ReconcilePendingOrders::class,
                CreatePromoCode::class,
                ListPromoCodes::class,
            ]);

            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);

                // The scheduler's heartbeat.
                //
                // Laravel records nothing about having run, so "is the cron
                // working?" is otherwise unanswerable without SSH — you can
                // only infer it from symptoms, days later, from a table that
                // waited for an envelope. This writes a timestamp every
                // minute and `platform:cron-status` reads it.
                //
                // Deliberately the cheapest thing in the schedule: one cache
                // write, no database, no lock. If this stops being current,
                // the cron itself stopped.
                $schedule->call(function () {
                    Cache::put(self::HEARTBEAT_KEY, now()->toIso8601String(), now()->addDays(7));
                })
                    ->everyMinute()
                    ->name('platform:scheduler-heartbeat')
                    ->withoutOverlapping(5);

                // A pending order never granted anything, so cleaning it up
                // late costs nothing — daily is plenty.
                $schedule->command(ExpireStaleOrders::class)
                    ->daily()
                    ->withoutOverlapping();

                // Catches a buyer who paid and closed the tab before the
                // confirmation page's own check (or Bold's webhook) resolved
                // it. Every 5 minutes so nobody's case or credits sit
                // unclaimed for anywhere near the 24h expiry window.
                if (config('platform.payments.enabled')) {
                    $schedule->command(ReconcilePendingOrders::class)
                        ->everyFiveMinutes()
                        ->withoutOverlapping();
                }
            });
        }
    }
}
