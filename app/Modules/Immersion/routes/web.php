<?php

use App\Modules\Immersion\Http\Controllers\GameMaster\AuthController;
use App\Modules\Immersion\Http\Controllers\GameMaster\GameMasterController;
use App\Modules\Immersion\Http\Controllers\Player\AccusationController;
use App\Modules\Immersion\Http\Controllers\Player\InboxController;
use App\Modules\Immersion\Http\Controllers\Player\InterrogationController;
use App\Modules\Immersion\Http\Middleware\EnsureGameMaster;
use Illuminate\Support\Facades\Route;

Route::prefix('gm')->name('immersion.gm.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(EnsureGameMaster::class)->group(function () {
        Route::get('/', [GameMasterController::class, 'index'])->name('dashboard');
        Route::post('/games', [GameMasterController::class, 'store'])->name('games.store');
        Route::get('/games/{game}', [GameMasterController::class, 'show'])->name('game.show');
        Route::post('/games/{game}/start', [GameMasterController::class, 'start'])->name('game.start');
        Route::post('/games/{game}/pause', [GameMasterController::class, 'pause'])->name('game.pause');
        Route::post('/games/{game}/resume', [GameMasterController::class, 'resume'])->name('game.resume');
        Route::post('/games/{game}/force-next', [GameMasterController::class, 'forceNext'])->name('game.force-next');
        Route::post('/games/{game}/load-default-timeline', [GameMasterController::class, 'loadDefaultTimeline'])->name('game.load-default-timeline');
        Route::post('/games/{game}/events/{event}/retry-audio', [GameMasterController::class, 'retryAudio'])->name('game.event.retry-audio');
        Route::get('/games/{game}/results', [GameMasterController::class, 'results'])->name('game.results');
        Route::post('/games/{game}/toggle-interrogation', [GameMasterController::class, 'toggleInterrogation'])->name('game.toggle-interrogation');
        Route::get('/games/{game}/interrogatorios', [GameMasterController::class, 'interrogations'])->name('game.interrogations');
    });
});

Route::prefix('jugador/{player}')->name('immersion.player.')->group(function () {
    Route::get('/', [InboxController::class, 'show'])->name('inbox');
    Route::get('/audio/{event}', [InboxController::class, 'audio'])->name('audio');
    Route::get('/acusacion', [AccusationController::class, 'show'])->name('accusation');
    Route::post('/acusacion', [AccusationController::class, 'store'])->name('accusation.store');
    Route::get('/interrogatorio', [InterrogationController::class, 'index'])->name('interrogation.index');
    Route::get('/interrogatorio/{slug}', [InterrogationController::class, 'show'])->name('interrogation.show');
    Route::post('/interrogatorio/{slug}/preguntar', [InterrogationController::class, 'ask'])->name('interrogation.ask');
});
