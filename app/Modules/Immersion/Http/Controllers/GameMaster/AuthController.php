<?php

namespace App\Modules\Immersion\Http\Controllers\GameMaster;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('GameMaster/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $expected = (string) config('immersion.game_master_password');

        if ($expected === '' || ! hash_equals($expected, $data['password'])) {
            return back()->withErrors(['password' => 'Contrasena incorrecta.']);
        }

        $request->session()->put('immersion_gm_ok', true);
        $request->session()->regenerate();

        return redirect()->route('immersion.gm.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('immersion_gm_ok');
        $request->session()->regenerateToken();

        return redirect()->route('immersion.gm.login');
    }
}
