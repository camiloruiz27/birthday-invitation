<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\Player;
use App\Modules\Platform\Models\LandingCampaign;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Support\LandingCampaigns;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LandingCampaignEventController extends Controller
{
    public function store(Request $request, LandingCampaign $landingCampaign, LandingCampaigns $campaigns)
    {
        $data = $request->validate([
            'event' => ['required', Rule::in(['paid_click', 'free_click', 'post_free_click'])],
            'case_slug' => ['nullable', 'string', 'max:80'],
            'player_token' => ['nullable', 'string', 'max:64'],
        ]);

        $case = isset($data['case_slug']) ? MysteryCase::where('slug', $data['case_slug'])->first() : null;
        $player = isset($data['player_token']) ? Player::where('access_token', $data['player_token'])->first() : null;
        $campaigns->track($request, $landingCampaign, $data['event'], $case, $player);

        return response()->noContent();
    }
}
