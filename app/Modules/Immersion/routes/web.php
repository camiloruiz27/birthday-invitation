<?php

use App\Modules\Immersion\Http\Controllers\GameMaster\GameMasterController;
use App\Modules\Immersion\Http\Controllers\Player\AccusationController;
use App\Modules\Immersion\Http\Controllers\Player\InboxController;
use App\Modules\Immersion\Http\Controllers\Player\InterrogationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Game Master
|--------------------------------------------------------------------------
|
| Authorization is declared here rather than inside the controllers so that a
| new action cannot quietly ship without it: `auth` proves who you are and
| `can:` proves the game is yours (see GamePolicy). Route model binding on
| its own would hand over any game by id.
|
*/

Route::prefix('gm')->name('immersion.gm.')->middleware('auth')->group(function () {
    Route::get('/', [GameMasterController::class, 'index'])->name('dashboard');
    Route::post('/games', [GameMasterController::class, 'store'])->name('games.store');

    Route::middleware('can:view,game')->group(function () {
        Route::get('/games/{game}', [GameMasterController::class, 'show'])->name('game.show');
        Route::get('/games/{game}/results', [GameMasterController::class, 'results'])->name('game.results');
        Route::get('/games/{game}/interrogatorios', [GameMasterController::class, 'interrogations'])->name('game.interrogations');
    });

    Route::middleware('can:control,game')->group(function () {
        Route::post('/games/{game}/start', [GameMasterController::class, 'start'])->name('game.start');
        Route::post('/games/{game}/pause', [GameMasterController::class, 'pause'])->name('game.pause');
        Route::post('/games/{game}/resume', [GameMasterController::class, 'resume'])->name('game.resume');
        Route::post('/games/{game}/force-next', [GameMasterController::class, 'forceNext'])->name('game.force-next');
        Route::post('/games/{game}/load-default-timeline', [GameMasterController::class, 'loadDefaultTimeline'])->name('game.load-default-timeline');
        Route::post('/games/{game}/events/{event}/retry-audio', [GameMasterController::class, 'retryAudio'])->name('game.event.retry-audio');
        Route::post('/games/{game}/toggle-interrogation', [GameMasterController::class, 'toggleInterrogation'])->name('game.toggle-interrogation');
    });
});

/*
|--------------------------------------------------------------------------
| Players
|--------------------------------------------------------------------------
|
| Players are guests: they hold no account, and the access token in the URL is
| their whole credential. Nothing here requires auth by design.
|
*/

Route::prefix('jugador/{player}')->name('immersion.player.')->group(function () {
    Route::get('/', [InboxController::class, 'show'])->name('inbox');
    Route::get('/audio/{event}', [InboxController::class, 'audio'])->name('audio');
    Route::get('/acusacion', [AccusationController::class, 'show'])->name('accusation');
    Route::post('/acusacion', [AccusationController::class, 'store'])->name('accusation.store');
    Route::get('/interrogatorio', [InterrogationController::class, 'index'])->name('interrogation.index');
    Route::get('/interrogatorio/{slug}', [InterrogationController::class, 'show'])->name('interrogation.show');

    // Every question is a billable AI call, so cap how fast one token can
    // spend them regardless of the per-session budget.
    Route::post('/interrogatorio/{slug}/preguntar', [InterrogationController::class, 'ask'])
        ->middleware('throttle:'.config('immersion.rate_limits.interrogation_ask'))
        ->name('interrogation.ask');
});
