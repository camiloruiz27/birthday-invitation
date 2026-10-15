<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\TimelineEvent;

/**
 * Which of a game's emails actually leave.
 *
 * Playing with real tables showed the email of every envelope was too much: the
 * game already delivers everything inside the platform (the inbox, and the
 * in-app notices that say something new arrived), and the mail duplicated that
 * while spending the mailbox's daily allowance. What stays is what a player
 * cannot get any other way:
 *
 * - the personal access link, which is how they get in at all (sent on demand
 *   from the console, not decided here), and
 * - the "accusations are open" email, with the button to make theirs.
 *
 * Everything else is switched off, not removed: the code that sends it is
 * intact and a deployment turns it back on from `.env`.
 *
 * Account mail (verification, password recovery) is not the game's and never
 * goes through this.
 */
class GameMailPolicy
{
    /** Only the "accusations are open" email. */
    public const UNLOCK = 'unlock';

    /** Every envelope, as before. */
    public const ALL = 'all';

    /** None of them. */
    public const OFF = 'off';

    /**
     * Should this timeline event be emailed to its recipients?
     *
     * The event is still sent: it still shows in the inbox, still opens what it
     * opens. This only decides whether an email goes with it.
     */
    public static function timelineEvent(TimelineEvent $event): bool
    {
        return match (strtolower((string) config('immersion.mail.timeline'))) {
            self::ALL => true,
            self::OFF => false,
            // Anything else — including a typo in .env — falls to the default,
            // which sends the least.
            default => $event->isUnlock(),
        };
    }

    /**
     * Do the timeline emails go out inside the process that sends the event?
     *
     * In the default mode there is a single email per game (accusations open),
     * so a queue and a worker for it cost more than the mail. In `all` mode a
     * case sends dozens of messages and the queue is what spreads them out.
     */
    public static function sendTimelineInline(): bool
    {
        return strtolower((string) config('immersion.mail.timeline')) !== self::ALL;
    }

    /** Should a player's epilogue (the message from the person they accused) be emailed? */
    public static function epilogue(): bool
    {
        return (bool) config('immersion.mail.epilogue');
    }
}
