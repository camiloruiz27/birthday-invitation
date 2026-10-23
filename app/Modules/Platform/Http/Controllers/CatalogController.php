<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Support\GameQuota;
use App\Modules\Platform\Http\Resources\CaseCardData;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Support\Mechanics;
use App\Modules\Platform\Support\PublishedCases;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Catalog', [
            // Searching and filtering happen in the browser (see
            // lib/caseDiscovery.js), so the page carries the whole published
            // list plus the text a search looks through. The list is cached
            // and the visitor never asks the server again for a filter.
            'cases' => fn () => PublishedCases::all()
                ->map(fn (MysteryCase $case) => CaseCardData::summary($case) + [
                    'search' => trim($case->tagline.' '.$case->description),
                    'published_at' => $case->published_at?->toIso8601String(),
                ])
                ->values(),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        // Route model binding would also serve unpublished rows; the catalog
        // must only ever show what is actually on sale.
        $case = PublishedCases::find($slug) ?? abort(404);

        return Inertia::render('Public/CaseDetail', [
            'case' => CaseCardData::detail($case),
            'owned' => (bool) $request->user()?->ownsCase($case),
            'canPurchase' => (bool) config('platform.payments.enabled'),
            'canSimulatePurchase' => (bool) config('platform.simulated_checkout'),
            // Said on the page rather than typed into it, so the marketing
            // copy cannot drift from the quota the Game Master actually hits.
            'gamesPerCase' => app(GameQuota::class)->limit(),
        ]);
    }

    public function mechanics(): Response
    {
        return Inertia::render('Public/Mechanics', [
            'mechanics' => Mechanics::list(),
        ]);
    }

    public function ai(): Response
    {
        return Inertia::render('Public/Ai', Mechanics::partitionByAi());
    }

    public function pricing(): Response
    {
        return Inertia::render('Public/Pricing', [
            'gamesPerCase' => app(GameQuota::class)->limit(),
            'cases' => fn () => PublishedCases::all()
                ->map(fn (MysteryCase $case) => CaseCardData::summary($case))
                ->values(),
        ]);
    }
}
