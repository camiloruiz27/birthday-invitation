<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Support\ReferralProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferralLinkController extends Controller
{
    /** A quiet landing: invalid links simply behave like a normal catalog visit. */
    public function __invoke(Request $request, string $code, ReferralProgram $referrals): RedirectResponse
    {
        $referrals->remember($request, $code);

        return redirect()->route('cases.index');
    }
}
