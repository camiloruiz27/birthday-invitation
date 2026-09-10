<?php

use App\Modules\Platform\Http\Controllers\AdminDashboardController;
use App\Modules\Platform\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Modules\Platform\Http\Controllers\Auth\EmailVerificationController;
use App\Modules\Platform\Http\Controllers\Auth\NewPasswordController;
use App\Modules\Platform\Http\Controllers\Auth\PasswordResetLinkController;
use App\Modules\Platform\Http\Controllers\Auth\RegisteredUserController;
use App\Modules\Platform\Http\Controllers\CatalogController;
use App\Modules\Platform\Http\Controllers\CheckoutController;
use App\Modules\Platform\Http\Controllers\CreditsController;
use App\Modules\Platform\Http\Controllers\DashboardController;
use App\Modules\Platform\Http\Controllers\LandingController;
use App\Modules\Platform\Http\Controllers\LibraryController;
use App\Modules\Platform\Http\Controllers\Payments\BoldWebhookController;
use App\Modules\Platform\Http\Controllers\Payments\PaymentCallbackController;
use App\Modules\Platform\Http\Controllers\ProfileController;
use App\Modules\Platform\Http\Controllers\RedeemCodeController;
use App\Modules\Platform\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public pages
|--------------------------------------------------------------------------
|
| Open to everyone, signed in or not: someone browsing the catalog while
| logged in should see it, not be bounced to their dashboard.
|
*/

Route::get('/', LandingController::class)->name('home');
Route::get('/casos', [CatalogController::class, 'index'])->name('cases.index');
Route::get('/casos/{slug}', [CatalogController::class, 'show'])->name('cases.show');
Route::get('/mecanicas', [CatalogController::class, 'mechanics'])->name('mechanics');
Route::get('/inteligencia-artificial', [CatalogController::class, 'ai'])->name('ai');
Route::get('/precios', [CatalogController::class, 'pricing'])->name('pricing');

/*
|--------------------------------------------------------------------------
| Guest routes
|--------------------------------------------------------------------------
|
| Throttled because each one is a credential-guessing or email-spamming
| surface: sign-in attempts, account enumeration and reset-mail floods.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('/registro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registro', [RegisteredUserController::class, 'store'])
        ->middleware('throttle:'.config('platform.rate_limits.register'));

    Route::get('/ingresar', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/ingresar', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:'.config('platform.rate_limits.login'));

    Route::get('/recuperar-clave', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/recuperar-clave', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:'.config('platform.rate_limits.password_email'))
        ->name('password.email');

    Route::get('/restablecer-clave/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/restablecer-clave', [NewPasswordController::class, 'store'])
        ->middleware('throttle:'.config('platform.rate_limits.password_reset'))
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/salir', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    /*
    | Email verification. Not itself behind `verified`, obviously — this is
    | how an account stops being unverified.
    |
    | `signed` is what authenticates the link in the mail; the throttles are
    | because `resend` makes our SMTP server send mail on request.
    */
    Route::get('/verificar-correo', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('/verificar-correo/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:'.config('platform.rate_limits.verify_email')])
        ->name('verification.verify');

    Route::post('/verificar-correo/reenviar', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:'.config('platform.rate_limits.verify_email_resend'))
        ->name('verification.send');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/perfil/clave', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/panel', DashboardController::class)->name('dashboard');
    Route::get('/biblioteca', LibraryController::class)->name('library');

    // The AI credit wallet. Reading the balance and the ledger needs no
    // confirmed address; spending money does — see the group below.
    Route::get('/creditos', [CreditsController::class, 'index'])->name('credits');

    // Gift codes only — a discount code is entered on the checkout screens
    // themselves (it needs a price to discount), never here.
    Route::get('/canjear', [RedeemCodeController::class, 'show'])->name('promo.redeem');

    /*
    |----------------------------------------------------------------------
    | Anything that spends money or claims value
    |----------------------------------------------------------------------
    |
    | `verified` is the line: an address nobody has proved they own must not
    | be able to buy. It is not squeamishness about spam — it is that the
    | receipt, the player links and the password recovery for that purchase
    | all go to an address the buyer may not be able to read, and a payment
    | that lands nowhere is the worst kind of support ticket to receive.
    |
    | `throttle:promo` only counts requests that actually carry a code, so a
    | buyer walking through the review screen without one is never charged an
    | attempt (see PlatformServiceProvider::registerPromoRateLimiter).
    |
    */
    Route::middleware('verified')->group(function () {
        // A real purchase always lands on review() first — what you're
        // buying, any discount applied, the actual total — before store()
        // ever runs. review() 404s when payments are off; store() is also
        // the simulated stand-in's endpoint, so it stays reachable either
        // way.
        Route::get('/casos/{slug}/comprar', [CheckoutController::class, 'review'])
            ->middleware('throttle:promo')
            ->name('cases.checkout.review');
        Route::post('/casos/{slug}/adquirir', [CheckoutController::class, 'store'])
            ->middleware('throttle:promo')
            ->name('cases.acquire');

        Route::get('/creditos/comprar', [CreditsController::class, 'review'])
            ->middleware('throttle:promo')
            ->name('credits.checkout.review');
        Route::post('/creditos/recargar', [CreditsController::class, 'purchase'])
            ->middleware('throttle:promo')
            ->name('credits.purchase');

        Route::post('/canjear', [RedeemCodeController::class, 'store'])
            ->middleware('throttle:promo')
            ->name('promo.redeem.store');
    });

    // Platform administration. Granted only from the console
    // (php artisan platform:make-admin), never through a screen.
    Route::middleware(EnsureAdmin::class)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
    });

    // Where Bold sends the buyer back after checkout. The page itself polls
    // this same route (Inertia partial reload) while it waits for the
    // webhook below to resolve the order.
    Route::get('/pagos/{order}/confirmando', [PaymentCallbackController::class, 'show'])
        ->whereNumber('order')
        ->name('payments.confirm');
});

/*
|--------------------------------------------------------------------------
| Payment provider webhook
|--------------------------------------------------------------------------
|
| Bold, not a signed-in user: no `auth`, and excluded from CSRF verification
| in VerifyCsrfToken::$except. The signature check inside the controller is
| this route's real authentication.
|
*/

Route::post('/webhooks/bold', BoldWebhookController::class)->name('payments.webhook.bold');
