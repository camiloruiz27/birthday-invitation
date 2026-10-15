import { useEffect } from 'react';
import { router, usePage } from '@inertiajs/react';

/**
 * Keeps a page current without hammering the database.
 *
 * Every reload is a full request and every request opens a MySQL connection;
 * the shared host allows 500 of those per hour. So nothing here reloads on a
 * timer. Each tick asks a tiny endpoint ("pollState.url") for the game's change
 * stamps, which reads the cache and never touches the database, and only
 * reloads when a stamp that someone on the screen WATCHES has moved.
 *
 * One screen, one poller. The header (new-notice badges) and the page itself
 * both need to stay current, and when each of them ran its own timer every
 * change reloaded the page twice and every safety net doubled. They now
 * SUBSCRIBE to a shared hub: it asks for the stamps once, works out which
 * subscribers changed, and reloads ONCE with the union of what they asked for.
 *
 * `channels` says what a subscriber shows: 'timeline' (envelopes, game state),
 * 'interrogation' (suspects, questions) and 'accusation' (accusations, grading,
 * the ending). A question asked in an interrogation changes nothing in the
 * inbox, so the inbox watches only 'timeline'. Leaving it out watches all three.
 *
 * Brakes, all there to spare connections:
 *  - a change waits MIN_CHANGE_RELOAD_MS after the previous reload;
 *  - the safety net (a reload even when nothing moved) is only a backstop for a
 *    write that forgot to move a stamp or a lost cache, so it is slow;
 *  - failures back off exponentially instead of retrying at full speed;
 *  - a tab nobody is looking at does nothing until it is visible again.
 *
 * A background reload that fails never replaces the page with an error screen.
 *
 * Inertia v1 has no `router.poll()`/`reload({ interval })` (v2+ features,
 * unavailable while pinned to v1 for Laravel 9 support), hence the hook.
 */
export const SAFETY_RELOAD_MS = 600000;
export const MIN_CHANGE_RELOAD_MS = 30000;
const MAX_BACKOFF_MS = 60000;
const ALL_CHANNELS = ['timeline', 'interrogation', 'accusation'];

// How many poll-driven reloads are in flight right now. Inertia treats a
// response that is not an Inertia page as an error and shows it in a modal; for
// a reload the user did not ask for, a failed background refresh must stay
// invisible.
let pollReloads = 0;
let guardInstalled = false;

function installInvalidGuard() {
    if (guardInstalled) return;
    guardInstalled = true;

    router.on('invalid', (event) => {
        if (pollReloads > 0) {
            event.preventDefault();
        }
    });
}

/** True when any WATCHED channel differs from what the page on screen shows. */
export function hasChanged(stamps, seen, channels) {
    if (!stamps || !seen) return true;

    return channels.some((channel) => stamps[channel] !== seen[channel]);
}

/** @type {Map<string, Hub>} one hub per poll endpoint (per game and player) */
const hubs = new Map();

class Hub {
    constructor(key, url, rev) {
        this.key = key;
        this.url = url;
        this.seen = rev ?? null;
        this.subs = new Set();
        this.timer = null;
        this.busy = false;
        this.failures = 0;
        this.ticks = 0;
        this.lastReloadAt = Date.now();
        this.stopped = false;

        this.onVisible = () => {
            if (document.hidden || this.stopped) return;

            clearTimeout(this.timer);
            this.tick();
        };

        document.addEventListener('visibilitychange', this.onVisible);
        this.schedule();
    }

    active() {
        return [...this.subs].filter((sub) => sub.enabled);
    }

    destroy() {
        this.stopped = true;
        clearTimeout(this.timer);
        document.removeEventListener('visibilitychange', this.onVisible);
        hubs.delete(this.key);
    }

    /** The shortest interval anyone asked for; hubs without an endpoint slow down. */
    interval() {
        const subs = this.active();

        if (subs.length === 0) return 5000;

        const base = Math.min(...subs.map((sub) => sub.interval));

        // No endpoint to ask (a page without a game): every tick is a reload, so
        // wait longer each time instead of hitting the server at a fixed rate.
        if (!this.url) {
            const cap = Math.max(...subs.map((sub) => sub.maxInterval ?? sub.interval));

            return Math.min(cap, Math.round(base * 1.4 ** this.ticks));
        }

        return base;
    }

    schedule() {
        if (this.stopped) return;

        const delay = Math.min(MAX_BACKOFF_MS, this.interval() * 2 ** this.failures);
        this.timer = setTimeout(() => this.tick(), delay);
    }

    reload(only) {
        return new Promise((resolve) => {
            let ok = false;
            pollReloads += 1;

            router.reload({
                only,
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    ok = true;
                },
                onFinish: () => {
                    pollReloads -= 1;
                    resolve(ok);
                },
            });
        });
    }

    async tick() {
        if (this.stopped) return;

        const subs = this.active();

        // Nothing to refresh for a tab nobody is looking at (the visibility
        // handler catches up when it returns), or nobody subscribed right now.
        if (document.hidden || this.busy || subs.length === 0) {
            this.schedule();
            return;
        }

        this.busy = true;
        this.ticks += 1;

        try {
            let stamps = null;
            let changedSubs = subs;

            if (this.url) {
                const response = await fetch(this.url, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                });

                if (!response.ok) {
                    throw new Error(`poll ${response.status}`);
                }

                stamps = (await response.json()).r ?? null;
                changedSubs = subs.filter((sub) => hasChanged(stamps, this.seen, sub.channels));
            }

            const sinceReload = Date.now() - this.lastReloadAt;
            const stale = sinceReload >= SAFETY_RELOAD_MS;

            // A change waits its turn: a table asking questions moves the stamps
            // every few seconds, and a reload per tick per tab is exactly the
            // traffic this exists to avoid. Unknown stamps (null) do not wait:
            // something is wrong with the hint.
            const due =
                changedSubs.length > 0 && (stamps === null || sinceReload >= MIN_CHANGE_RELOAD_MS);

            if (due || stale || !this.url) {
                const targets = stale || !this.url ? subs : changedSubs;
                const only = [...new Set(targets.flatMap((sub) => sub.only))];

                const ok = await this.reload(only);

                if (!ok) {
                    this.failures += 1;
                } else {
                    this.failures = 0;
                    this.lastReloadAt = Date.now();

                    // The stamps read BEFORE the reload: anything that changed
                    // while it ran shows up on the next tick.
                    if (stamps !== null) this.seen = stamps;
                }
            } else {
                this.failures = 0;
            }
        } catch {
            this.failures += 1;
        } finally {
            this.busy = false;
            this.schedule();
        }
    }
}

function subscribe(url, rev, sub) {
    installInvalidGuard();

    const key = url ?? 'none';
    let hub = hubs.get(key);

    if (!hub) {
        hub = new Hub(key, url, rev);
        hubs.set(key, hub);
    }

    hub.subs.add(sub);

    return () => {
        hub.subs.delete(sub);

        if (hub.subs.size === 0) {
            hub.destroy();
        }
    };
}

export default function usePoll(
    only,
    { interval = 5000, maxInterval, enabled = true, channels = ALL_CHANNELS } = {}
) {
    const { pollState } = usePage().props;
    const url = pollState?.url ?? null;
    const rev = pollState?.rev ?? null;

    useEffect(() => {
        if (!enabled) return undefined;

        return subscribe(url, rev, { only, interval, maxInterval, enabled: true, channels });
        // `rev` is only the starting point of a hub that does not exist yet.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [enabled, interval, maxInterval, url, JSON.stringify(only), JSON.stringify(channels)]);
}
