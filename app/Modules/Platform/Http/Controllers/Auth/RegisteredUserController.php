<?php

namespace App\Modules\Platform\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Platform\Ads\AdEvents;
use App\Modules\Platform\Rules\CaptchaRule;
use App\Modules\Platform\Support\Attribution;
use App\Modules\Platform\Support\PurchaseIntent;
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
    /**
     * `?case={slug}` is how a case page (or an ad pointing at one) says "this
     * visitor is registering in order to buy that" — see PurchaseIntent, which
     * login shares so the two cannot drift. The slug is passed on to the page
     * so its link to sign in keeps it.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Register', [
            'case' => PurchaseIntent::remember($request),
        ]);
    }

    public function store(Request $request, AdEvents $ads): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            // Express, prior authorization (Ley 1581 de 2012): the box starts
            // unticked and nothing is created without it.
            'accept_terms' => ['accepted'],
        ] + CaptchaRule::rules($request->ip()));

        // Where the visitor meant to go once registered (see create()), kept
        // on the account too: the confirmation mail is often opened on
        // another device, where this session does not exist.
        $intended = $request->session()->pull('url.intended');
        $intended = is_string($intended) && str_starts_with($intended, url('/')) ? $intended : null;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'terms_accepted_at' => now(),
            'terms_version' => config('legal.terms_version'),
            'privacy_accepted_at' => now(),
            'privacy_version' => config('legal.privacy_version'),
            'attribution' => Attribution::forNewUser($request, $intended),
        ]);

        // Registering grants no case access: access comes from an Entitlement,
        // never from the account itself. The event is also what sends the
        // verification mail, now that User implements MustVerifyEmail.
        event(new Registered($user));

        $ads->completeRegistration($user, $request);

        Auth::login($user);
        $request->session()->regenerate();

        // Straight on to what they came for, not onto a "confirm your email"
        // wall. Buying does not need a confirmed address (see the routes), so
        // the visitor who clicked an ad goes to pay in the same sitting. The
        // confirmation is asked for later, where it matters: creating a game
        // and mailing players. Being told here is what keeps that from being
        // a surprise.
        $status = "Te enviamos un correo a {$user->email}. Confírmalo cuando puedas: "
            .'lo necesitas para crear partidas y enviar los enlaces a tus jugadores.';

        if ($intended) {
            return redirect($intended)->with('status', $status);
        }

        return redirect()->route('dashboard')->with('status', $status);
    }
}
