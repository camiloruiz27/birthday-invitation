<?php

namespace App\Modules\Platform\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Confirming that the address on an account is really the buyer's.
 *
 * Reached in one of two ways: by following the signed link in the mail, or by
 * being turned away from a purchase (the `verified` middleware redirects
 * here). Both land on the same screen, which is why it explains the reason
 * rather than assuming the visitor just registered.
 */
class EmailVerificationController extends Controller
{
    public function notice(Request $request): RedirectResponse|Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/VerifyEmail', [
            'email' => $request->user()->email,
            // Set by the resend action below, and read here rather than as a
            // generic flash so the page can word it as "sent again".
            'sent' => $request->session()->get('status') === 'verification-link-sent',
        ]);
    }

    /**
     * The link in the mail.
     *
     * EmailVerificationRequest does the authorization itself: the URL is
     * signed (so it cannot be forged or edited), and it checks that the id
     * and the email hash in the link match the signed-in user — which is what
     * stops someone forwarding their link to have another account verified.
     */
    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        // On to what they came to do — this session's own memory of it, or
        // what was saved on the account when the link is opened on another
        // device — and only the panel when there is none.
        return redirect()
            ->intended($this->savedIntent($request) ?? route('dashboard'))
            ->with('status', 'Tu correo quedó confirmado. Ya puedes crear partidas y enviar los enlaces a tus jugadores.');
    }

    /**
     * The address stored at registration, if it is still one of ours.
     */
    private function savedIntent(Request $request): ?string
    {
        $intended = ((array) $request->user()->attribution)['intended'] ?? null;

        return is_string($intended) && str_starts_with($intended, url('/')) ? $intended : null;
    }

    /**
     * Sends the mail again.
     *
     * Throttled at the route, because this is a button that makes our server
     * send mail to an address of the requester's choosing — the same reason
     * password recovery is throttled.
     */
    public function resend(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
