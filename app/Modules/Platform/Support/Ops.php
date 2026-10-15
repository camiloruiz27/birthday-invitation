<?php

namespace App\Modules\Platform\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

/**
 * The cache for what has to outlive a deploy.
 *
 * Every deploy runs `php artisan optimize:clear`, which empties the default
 * cache. For a page cache that is right (the code that builds it may have
 * changed), but a few things are not a cache at all — they are the platform's
 * short-term memory:
 *
 *  - the change stamps that tell open pages to reload. Wipe them and every tab
 *    of every running game reloads on its next tick, all at once, right after
 *    the deploy: a burst of connections at the worst moment;
 *  - the per-hour connection counter and the 24-hour mail counter. Wipe them
 *    and the log's warning at 80% of the host's limit never fires, because the
 *    count restarted at zero;
 *  - the plan of the next timeline event, and the cron heartbeat.
 *
 * Those live in this store, a separate directory that `cache:clear` leaves
 * alone. Same driver and same API as any cache; only the directory differs.
 */
class Ops
{
    public static function store(): Repository
    {
        return Cache::store((string) config('cache.ops_store'));
    }
}
