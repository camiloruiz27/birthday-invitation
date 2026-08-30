<?php

namespace App\Modules\Immersion\Http\Controllers\GameMaster;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('immersion::game-master.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        $expected = (string) env('IMMERSION_GM_PASSWORD', '');

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
