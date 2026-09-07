<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Actions\GrantCaseAccess;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Stand-in for checkout while there is no payment provider.
 *
 * It takes no money and collects no payment details: it writes an entitlement
 * through the same action a real purchase will use, marked `grant` so it is
 * always distinguishable from a paid one in the data.
 *
 * Disabled outside local/dev by default (config platform.simulated_checkout),
 * so a deployed site cannot give cases away.
 */
class CheckoutController extends Controller
{
    public function store(Request $request, string $slug, GrantCaseAccess $access): RedirectResponse
    {
        abort_unless(config('platform.simulated_checkout'), 404);

        $case = MysteryCase::published()->where('slug', $slug)->firstOrFail();

        if ($request->user()->ownsCase($case)) {
            return redirect()
                ->route('cases.show', $case->slug)
                ->with('status', 'Ya tienes este caso en tu biblioteca.');
        }

        $access->grant($request->user(), $case, Entitlement::SOURCE_GRANT);

        return redirect()
            ->route('dashboard')
            ->with('status', "\"{$case->name}\" está en tu biblioteca. Ya puedes crear una partida.");
    }
}
