<?php

namespace App\Modules\Platform\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Rules\CaptchaRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
        ] + CaptchaRule::rules($request->ip()));

        // Only the address; the captcha token is not part of what we look up.
        $data = ['email' => $data['email']];

        Password::sendResetLink($data);

        // Always the same confirmation, whether or not the address exists:
        // the response must not reveal which emails have accounts.
        return back()->with('status', 'Si esa dirección tiene una cuenta, te enviamos un enlace para restablecer tu contraseña.');
    }
}
