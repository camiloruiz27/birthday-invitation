<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Http\Resources\CaseCardData;
use App\Modules\Platform\Models\MysteryCase;
use App\Modules\Platform\Support\Mechanics;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Public/Catalog', [
            'cases' => fn () => MysteryCase::published()
                ->ordered()
                ->get()
                ->map(fn (MysteryCase $case) => CaseCardData::summary($case))
                ->values(),
        ]);
    }

    public function show(Request $request, string $slug): Response
    {
        // Route model binding would also serve unpublished rows; the catalog
        // must only ever show what is actually on sale.
        $case = MysteryCase::published()->where('slug', $slug)->firstOrFail();

        return Inertia::render('Public/CaseDetail', [
            'case' => CaseCardData::detail($case),
            'owned' => (bool) $request->user()?->ownsCase($case),
            'canPurchase' => (bool) config('platform.payments.enabled'),
            'canSimulatePurchase' => (bool) config('platform.simulated_checkout'),
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
            'cases' => fn () => MysteryCase::published()
                ->ordered()
                ->get()
                ->map(fn (MysteryCase $case) => CaseCardData::summary($case))
                ->values(),
        ]);
    }
}
