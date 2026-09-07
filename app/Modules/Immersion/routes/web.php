<?php

use App\Modules\Immersion\Http\Controllers\GameMaster\GameMasterController;
use App\Modules\Immersion\Http\Controllers\Player\AccusationController;
use App\Modules\Immersion\Http\Controllers\Player\InboxController;
use App\Modules\Immersion\Http\Controllers\Player\InterrogationController;
use App\Modules\Immersion\Http\Controllers\Player\SolutionController;
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

Route::prefix('partidas')->name('immersion.gm.')->middleware('auth')->group(function () {
    Route::get('/', [GameMasterController::class, 'index'])->name('games.index');
    Route::get('/crear', [GameMasterController::class, 'create'])->name('games.create');
    Route::post('/', [GameMasterController::class, 'store'])->name('games.store');

    // whereNumber keeps a game id from swallowing a literal path segment:
    // without it /partidas/crear is a candidate match for /partidas/{game}.
    Route::middleware('can:view,game')->whereNumber('game')->group(function () {
        Route::get('/{game}', [GameMasterController::class, 'show'])->name('game.show');
    });

    // Frees a quota slot. Destructive, so it has its own ability.
    Route::delete('/{game}', [GameMasterController::class, 'destroy'])
        ->middleware('can:delete,game')
        ->whereNumber('game')
        ->name('game.destroy');

    // Run controls: both modes need someone to say when the case starts and
    // ends.
    Route::middleware('can:control,game')->whereNumber('game')->group(function () {
        Route::post('/{game}/start', [GameMasterController::class, 'start'])->name('game.start');
        Route::post('/{game}/pause', [GameMasterController::class, 'pause'])->name('game.pause');
        Route::post('/{game}/resume', [GameMasterController::class, 'resume'])->name('game.resume');
        Route::post('/{game}/finish', [GameMasterController::class, 'finish'])->name('game.finish');
        Route::post('/{game}/load-default-timeline', [GameMasterController::class, 'loadDefaultTimeline'])->name('game.load-default-timeline');
        Route::post('/{game}/events/{event}/retry-audio', [GameMasterController::class, 'retryAudio'])->name('game.event.retry-audio');
    });

    // Directing: reaching into the timeline. Denied in automatic mode, where
    // the owner is a player and this would be rewriting their own game.
    Route::middleware('can:direct,game')->whereNumber('game')->group(function () {
        Route::post('/{game}/force-next', [GameMasterController::class, 'forceNext'])->name('game.force-next');
        Route::post('/{game}/toggle-interrogation', [GameMasterController::class, 'toggleInterrogation'])->name('game.toggle-interrogation');
    });

    // Publishing the ending. Its own ability: allowed in both modes, unlike
    // the directing controls.
    Route::post('/{game}/revelar', [GameMasterController::class, 'revealEnding'])
        ->middleware('can:reveal,game')
        ->whereNumber('game')
        ->name('game.reveal');

    // The two views that give the case away.
    Route::middleware('can:viewSpoilers,game')->whereNumber('game')->group(function () {
        Route::get('/{game}/results', [GameMasterController::class, 'results'])->name('game.results');
        Route::get('/{game}/interrogatorios', [GameMasterController::class, 'interrogations'])->name('game.interrogations');
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

    // Refuses with 403 until the ending is revealed; see SolutionController.
    Route::get('/solucion', [SolutionController::class, 'show'])->name('solution');
    Route::get('/interrogatorio', [InterrogationController::class, 'index'])->name('interrogation.index');
    Route::get('/interrogatorio/{slug}', [InterrogationController::class, 'show'])->name('interrogation.show');

    // Every question is a billable AI call, so cap how fast one token can
    // spend them regardless of the per-session budget.
    Route::post('/interrogatorio/{slug}/preguntar', [InterrogationController::class, 'ask'])
        ->middleware('throttle:'.config('immersion.rate_limits.interrogation_ask'))
        ->name('interrogation.ask');
});
