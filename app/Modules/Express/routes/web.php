<?php

use App\Modules\Express\Http\Controllers\LandingController;
use App\Modules\Express\Http\Controllers\PlayController;
use App\Modules\Express\Http\Controllers\PosterController;
use App\Modules\Express\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Express (demo de feria) — rutas con sesion (grupo `web`)
|--------------------------------------------------------------------------
|
| El endpoint de estado (el poll) vive aparte, en routes/state.php, sin sesion.
| El token de la URL es la credencial del jugador, igual que /jugador/{token}.
|
*/

Route::prefix('express')->group(function () {
    Route::get('/', [LandingController::class, 'index'])->name('express.home');

    Route::get('crear', [SetupController::class, 'create'])->name('express.create');
    Route::post('crear', [SetupController::class, 'store'])
        ->middleware('throttle:express-create')
        ->name('express.rooms.store');

    Route::get('unirse/{code}', [SetupController::class, 'join'])
        ->where('code', '[A-Za-z0-9]{5}')
        ->name('express.join');
    Route::post('unirse/{code}', [SetupController::class, 'joinStore'])
        ->where('code', '[A-Za-z0-9]{5}')
        ->middleware('throttle:express-join')
        ->name('express.join.store');

    Route::prefix('s/{token}')->where(['token' => '[0-9a-fA-F-]{36}'])->group(function () {
        Route::get('/', [PlayController::class, 'show'])->name('express.room');

        Route::middleware('throttle:express-act')->group(function () {
            Route::post('iniciar', [PlayController::class, 'start'])->name('express.start');
            Route::post('compartir', [PlayController::class, 'share'])->name('express.share');
            Route::post('acusar', [PlayController::class, 'accuse'])->name('express.accuse');
            Route::post('otra-ronda', [PlayController::class, 'nextCreate'])->name('express.next.create');
            Route::post('siguiente', [PlayController::class, 'nextJoin'])->name('express.next.join');
        });

        Route::post('preguntar', [PlayController::class, 'ask'])
            ->middleware('throttle:express-ask')
            ->name('express.ask');
    });

    // Last, so a slug can never shadow the fixed routes above.
    Route::get('{event}/cartel', [PosterController::class, 'show'])
        ->where('event', '[a-z0-9-]+')
        ->name('express.poster');
});
