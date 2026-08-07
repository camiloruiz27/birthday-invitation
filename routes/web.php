<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CoupleExperienceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('invitation');
});

Route::redirect('/nueva-pagina', '/aniversario');

Route::get('/aniversario', [CoupleExperienceController::class, 'index'])->name('couple-experience.index');
Route::get('/aniversario/asistente', [CoupleExperienceController::class, 'assistant'])->name('couple-experience.assistant');
Route::get('/aniversario/actividades', [CoupleExperienceController::class, 'activities'])->name('couple-experience.activities');
Route::get('/aniversario/cronometros', [CoupleExperienceController::class, 'timers'])->name('couple-experience.timers');
Route::get('/aniversario/biblioteca', [CoupleExperienceController::class, 'library'])->name('couple-experience.library');
Route::get('/aniversario/nivel-6', [CoupleExperienceController::class, 'levelSix'])->name('couple-experience.level-six');
Route::get('/aniversario/historial', [CoupleExperienceController::class, 'history'])->name('couple-experience.history');
Route::post('/aniversario/login', [CoupleExperienceController::class, 'login'])->name('couple-experience.login');
Route::post('/aniversario/logout', [CoupleExperienceController::class, 'logout'])->name('couple-experience.logout');
Route::post('/aniversario/consent', [CoupleExperienceController::class, 'consent'])->middleware('auth')->name('couple-experience.consent');
Route::post('/aniversario/progress', [CoupleExperienceController::class, 'progress'])->middleware('auth')->name('couple-experience.progress');
Route::post('/aniversario/answers', [CoupleExperienceController::class, 'answer'])->middleware('auth')->name('couple-experience.answers');
Route::post('/aniversario/ai/suggest', [CoupleExperienceController::class, 'suggest'])->middleware('auth')->name('couple-experience.ai.suggest');
Route::post('/aniversario/ai/suggestions/{suggestionId}/status', [CoupleExperienceController::class, 'suggestionStatus'])->middleware('auth')->name('couple-experience.ai.status');
