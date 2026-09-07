<?php

use App\Modules\Platform\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Modules\Platform\Http\Controllers\Auth\NewPasswordController;
use App\Modules\Platform\Http\Controllers\Auth\PasswordResetLinkController;
use App\Modules\Platform\Http\Controllers\Auth\RegisteredUserController;
use App\Modules\Platform\Http\Controllers\CatalogController;
use App\Modules\Platform\Http\Controllers\CheckoutController;
use App\Modules\Platform\Http\Controllers\DashboardController;
use App\Modules\Platform\Http\Controllers\LandingController;
use App\Modules\Platform\Http\Controllers\LibraryController;
use App\Modules\Platform\Http\Controllers\ProfileController;
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

    // Stand-in for checkout until a payment provider exists; 404s when the
    // simulation is off (see config/platform.php).
    Route::post('/casos/{slug}/adquirir', [CheckoutController::class, 'store'])
        ->name('cases.acquire');

    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/perfil/clave', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/panel', DashboardController::class)->name('dashboard');
    Route::get('/biblioteca', LibraryController::class)->name('library');
});
