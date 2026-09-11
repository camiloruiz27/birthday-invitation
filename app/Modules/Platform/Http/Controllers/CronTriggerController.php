<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * Runs the scheduler from an HTTP hit instead of the host's own cron.
 *
 * Exists because a host's panel can show `schedule:run` configured correctly
 * every minute and still never invoke it — the panel and the real crontab can
 * silently disagree (see app/Modules/Immersion/README.md, "Cron en
 * produccion"). An external ping service hitting this URL does not depend on
 * that daemon at all, so it keeps the scheduler alive regardless.
 *
 * Not behind `auth` — an external ping service is not a signed-in user — so
 * the token is this route's actual authentication, the same shape as
 * BoldWebhookController's signature check.
 */
class CronTriggerController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('platform.cron_http_trigger.secret');

        if (! $secret || ! hash_equals($secret, (string) $request->query('token'))) {
            return response('', 403);
        }

        Artisan::call('schedule:run');

        return response(Artisan::output())->header('Content-Type', 'text/plain');
    }
}
