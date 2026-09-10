<?php

namespace App\Modules\Platform\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Platform\Rules\CaptchaRule;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ] + CaptchaRule::rules($request->ip()));

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Registering grants no case access: access comes from an Entitlement,
        // never from the account itself. The event is also what sends the
        // verification mail, now that User implements MustVerifyEmail.
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        // Straight into the dashboard, not onto a "confirm your email" wall:
        // an unverified account can browse and look around, and only pays for
        // the missing confirmation at the point where it matters (see the
        // `verified` middleware on the buying routes). Being told about it
        // here is what keeps that later block from being a surprise.
        return redirect()->route('dashboard')->with(
            'status',
            "Te enviamos un correo a {$user->email} para confirmar tu dirección. "
                .'Necesitas confirmarla antes de comprar o canjear un código.'
        );
    }
}
