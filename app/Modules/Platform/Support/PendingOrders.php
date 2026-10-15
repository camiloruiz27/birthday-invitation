<?php

namespace App\Modules\Platform\Support;

use Throwable;

/**
 * "Is an order waiting on Bold?", answered without the database.
 *
 * A scheduled command re-checks every pending order against Bold every five
 * minutes, in case a webhook never arrived. With no order pending it finds
 * nothing — but it still opened a connection to find out, twelve times an hour,
 * all day. This remembers whether anything is waiting: an order saved as pending
 * marks it, and a run that finds none clears it.
 *
 * Unknown (a cleared store, the first run) means "maybe", which means run: the
 * guard can only ever make the command run LESS than it would, and the next run
 * that finds nothing silences it again.
 */
class PendingOrders
{
    private const KEY = 'platform:orders:pending';

    public static function mark(): void
    {
        try {
            Ops::store()->put(self::KEY, 1, now()->addDays(3));
        } catch (Throwable) {
            // Without the note the command simply runs, as before.
        }
    }

    public static function none(): void
    {
        try {
            Ops::store()->put(self::KEY, 0, now()->addDays(3));
        } catch (Throwable) {
        }
    }

    /** False only when a run has found nothing waiting since the last order. */
    public static function mayExist(): bool
    {
        try {
            return Ops::store()->get(self::KEY) !== 0;
        } catch (Throwable) {
            return true;
        }
    }
}
