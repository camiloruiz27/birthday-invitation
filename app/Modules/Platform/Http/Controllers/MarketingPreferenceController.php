<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Platform\Support\MarketingConsent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Whether a person receives promotional email, from their side: the one-click
 * link in the mail and the switch in their profile. Both go through
 * MarketingConsent, which is the only writer.
 */
class MarketingPreferenceController extends Controller
{
    /**
     * The link in the footer of a promotional mail. `signed` middleware has
     * already proved the URL is ours, so no session is needed.
     *
     * A GET that changes state is normally wrong; here it is deliberate, and
     * harmless: a mail scanner that opens the link only ever unsubscribes
     * someone, which is the safe direction, and the profile switch undoes it.
     */
    public function unsubscribe(User $user): Response
    {
        MarketingConsent::revoke($user);

        return Inertia::render('Marketing/Unsubscribed');
    }

    /** The switch in the profile. */
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['subscribed' => ['required', 'boolean']]);

        $user = $request->user();

        if ($data['subscribed']) {
            MarketingConsent::grant($user, MarketingConsent::SOURCE_PROFILE);

            return back()->with('status', 'Listo: te enviaremos ofertas y beneficios por correo.');
        }

        MarketingConsent::revoke($user);

        return back()->with('status', 'Listo: no te enviaremos más ofertas por correo.');
    }
}
