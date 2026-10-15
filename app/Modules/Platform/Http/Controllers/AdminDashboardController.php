<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Support\MailBudget;
use App\Modules\Platform\Support\ConnectionBudget;
use App\Modules\Platform\Support\ConnectionReuseProbe;
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

            // The two allowances the host enforces and nothing else warns about:
            // database connections per hour and mailbox messages per 24 hours.
            // Both are read from the cache, so looking costs no connection.
            'limits' => fn () => [
                'connections' => ConnectionBudget::summary() + [
                    'reuse' => [
                        'this_hour' => ConnectionReuseProbe::summary(),
                        'last_hour' => ConnectionReuseProbe::summary(now()->subHour()),
                    ],
                ],
                'mail' => MailBudget::summary(),
            ],
        ]);
    }
}
