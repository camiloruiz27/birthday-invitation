<?php

use App\Modules\Express\Http\Controllers\StateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Express — el poll, SIN sesion
|--------------------------------------------------------------------------
|
| Registrada fuera del grupo `web` a proposito: no abre ni toca sesion, asi que
| decenas de celulares consultando cada pocos segundos no crean archivos de
| sesion ni gastan conexiones de base de datos (ver Support/RoomSnapshot).
| El tope es por token, no por IP: en una feria todos comparten Wi-Fi.
|
*/

Route::get('express/s/{token}/estado', [StateController::class, 'show'])
    ->where('token', '[0-9a-fA-F-]{36}')
    ->middleware('throttle:express-state')
    ->name('express.state');
