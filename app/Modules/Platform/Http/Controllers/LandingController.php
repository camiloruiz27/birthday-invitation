<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Http\Resources\CaseCardData;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Support\Mechanics;
use App\Modules\Platform\Support\PublishedCases;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Public/Landing', [
            // Only a handful: the landing page points at the catalog, it is
            // not the catalog.
            //
            // The rows are cached for a minute (PublishedCases), the cards are
            // not: a card carries the price in the visitor's own market.
            'featured' => fn () => PublishedCases::all(3)
                ->map(fn (MysteryCase $case) => CaseCardData::summary($case))
                ->values(),
            'mechanics' => Mechanics::list(),
        ]);
    }
}
