<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Support\PromoCodeMetrics;
use Inertia\Inertia;
use Inertia\Response;

class AdminPromoCodesController extends Controller
{
    public function __invoke(PromoCodeMetrics $metrics): Response
    {
        // Lazily, same reason as AdminDashboardController: an Inertia
        // partial reload should not re-run every aggregate query.
        return Inertia::render('Admin/PromoCodes', [
            'metrics' => fn () => $metrics->all(),
        ]);
    }
}
