<?php

namespace App\Modules\Immersion\Support;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * A ceiling on how many questions to suspects are being answered at once.
 *
 * A question is asked in the web request itself and waits for the AI gateway,
 * which can take half a minute under load — all that time it holds one of the
 * hosting plan's 60 PHP workers. A burst of questions (a table answering the same
 * envelope together, a few games reaching the interrogations at once) could take
 * every worker and leave nobody able to load ANY page, the inbox of a player who
 * asked nothing included.
 *
 * So only `max_concurrent` questions run at once (config immersion.ai.
 * max_concurrent); the next one is turned away with a clear "try again in a few
 * seconds" and its question slot handed back, which costs the player nothing and
 * keeps the rest of the platform answering. Slots are cache locks that expire on
 * their own, so a worker that dies mid-question cannot hold one forever.
 */
class AiGate
{
    /** A slot outlives the longest the gateway is waited on, with room to spare. */
    private const SLOT_SECONDS = 90;

    /**
     * Takes a free slot, or null when every one is busy. A false return means
     * "no limit configured": nothing to release.
     */
    public static function acquire(): Lock|false|null
    {
        $max = max(0, (int) config('immersion.ai.max_concurrent'));

        if ($max === 0) {
            return false;
        }

        try {
            // A different starting slot each time, so a busy gate does not make
            // every request queue up behind slot 1.
            $start = random_int(1, $max);

            for ($i = 0; $i < $max; $i++) {
                $slot = (($start + $i - 1) % $max) + 1;
                $lock = Cache::lock("immersion:ai-slot:{$slot}", self::SLOT_SECONDS);

                if ($lock->get()) {
                    return $lock;
                }
            }
        } catch (Throwable) {
            // A gate that cannot be checked must not become a reason to refuse.
            return false;
        }

        return null;
    }

    public static function release(Lock|false|null $slot): void
    {
        if ($slot instanceof Lock) {
            try {
                $slot->release();
            } catch (Throwable) {
                // It expires on its own.
            }
        }
    }
}
