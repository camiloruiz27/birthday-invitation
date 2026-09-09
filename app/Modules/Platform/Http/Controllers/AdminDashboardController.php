<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Support\PlatformMetrics;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(PlatformMetrics $metrics): Response
    {
        // Lazily: the dashboard is a dozen aggregate queries, and Inertia
        // partial reloads should not re-run all of them.
        return Inertia::render('Admin/Dashboard', [
            'metrics' => fn () => $metrics->all(),
        ]);
    }
}
