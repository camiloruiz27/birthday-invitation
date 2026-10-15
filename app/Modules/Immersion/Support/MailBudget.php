<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Platform\Support\Ops;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * How much of the mailbox's daily allowance is spent.
 *
 * Hostinger limits a mailbox per rolling 24 hours (see config/immersion.php,
 * `mail`). Nothing tells the application it is close: past the limit messages
 * simply fail, and the one that fails is whichever envelope happens to be
 * next. So every message that leaves is counted — all of them, the account mail
 * (verification, receipts) included, since it leaves through the same mailbox —
 * and the jobs that send game mail ask first.
 *
 * Counted in hourly buckets in the cache (no database, so counting costs no
 * connection) and summed over the last 24, which is as close to a rolling
 * window as an hourly resolution gets. A cleared cache forgets the day, which
 * errs toward sending; the provider's own limit is still the backstop.
 */
class MailBudget
{
    /** An envelope: a live table is waiting on it. */
    public const ENVELOPE = 'envelope';

    /** An access link or an epilogue: useful, but it can wait for tomorrow. */
    public const OPTIONAL = 'optional';

    private const BUCKET_TTL_HOURS = 26;

    public static function record(): void
    {
        try {
            $key = self::bucket(now());

            Ops::store()->add($key, 0, now()->addHours(self::BUCKET_TTL_HOURS));
            Ops::store()->increment($key);

            self::warnWhenLow(self::used());
        } catch (Throwable) {
            // Counting must never stop a message that already left.
        }
    }

    /** Messages sent over the last 24 hours. */
    public static function used(): int
    {
        try {
            $total = 0;

            for ($hour = 0; $hour < 24; $hour++) {
                $total += (int) Ops::store()->get(self::bucket(now()->subHours($hour)), 0);
            }

            return $total;
        } catch (Throwable) {
            return 0;
        }
    }

    public static function limit(): int
    {
        return max(0, (int) config('immersion.mail.daily_limit'));
    }

    /** Messages left in the day for an envelope; null when the guard is off. */
    public static function remaining(): ?int
    {
        $limit = self::limit();

        return $limit === 0 ? null : max(0, $limit - self::used());
    }

    /**
     * Is there room for one more message of this kind?
     *
     * Envelopes may use the whole allowance; everything else stops at
     * `reserve_share` of it, which is what keeps room for envelopes.
     */
    public static function canSend(string $kind = self::ENVELOPE): bool
    {
        $limit = self::limit();

        if ($limit === 0) {
            return true;
        }

        $ceiling = $kind === self::ENVELOPE
            ? $limit
            : (int) floor($limit * min(1.0, max(0.0, (float) config('immersion.mail.reserve_share'))));

        return self::used() < $ceiling;
    }

    /** @return array{used: int, limit: int, remaining: int|null} */
    public static function summary(): array
    {
        return ['used' => self::used(), 'limit' => self::limit(), 'remaining' => self::remaining()];
    }

    /** Once per hour, when 80% of the day is gone, so it is seen before it is felt. */
    private static function warnWhenLow(int $used): void
    {
        $limit = self::limit();

        if ($limit === 0 || $used < (int) ceil($limit * 0.8)) {
            return;
        }

        if (Ops::store()->add('immersion:mail:low-warned:'.now()->format('YmdH'), true, now()->addHours(2))) {
            Log::warning('mail_daily_budget_low', [
                'sent_last_24h' => $used,
                'daily_limit' => $limit,
                'hint' => 'Envelopes keep going until the limit; access links and epilogues wait. Raise the mailbox plan or IMMERSION_MAIL_DAILY_LIMIT.',
            ]);
        }
    }

    private static function bucket(\DateTimeInterface $at): string
    {
        return 'immersion:mail:sent:'.$at->format('YmdH');
    }
}
