<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use Illuminate\Http\RedirectResponse;

/**
 * Records that a player read the privacy notice shown on their first visit.
 *
 * Players have no account, so this is the only place their authorization to
 * process their name and answers (Ley 1581 de 2012) can be captured: when, and
 * which version of the policy it referred to. It is idempotent — a second click
 * must not move the original timestamp, which is the evidence.
 */
class PrivacyNoticeController extends Controller
{
    public function accept(Player $player): RedirectResponse
    {
        if ($player->privacy_accepted_at === null) {
            $player->forceFill([
                'privacy_accepted_at' => now(),
                'privacy_version' => config('legal.privacy_version'),
            ])->save();
        }

        return back();
    }
}
