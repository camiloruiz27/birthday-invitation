import { useEffect, useRef } from 'react';
import { router, usePage } from '@inertiajs/react';

/**
 * Keeps a page current without hammering the database.
 *
 * It used to call `router.reload()` on a timer. Every reload is a full request
 * and every request opens a MySQL connection, and the shared host allows 500
 * of those per hour: three players plus the Game Master polling every few
 * seconds used them all in minutes and every page went down with a 500.
 *
 * Now each tick only asks a tiny endpoint ("pollState.url") for the game's
 * change stamps. That endpoint reads the cache and never touches the database.
 * The real reload happens only when a stamp this page WATCHES moved, when it is
 * unknown (null: a cleared cache, a token it has not seen) or as a safety net
 * every SAFETY_RELOAD_MS for anything the stamps cannot see (time passing).
 *
 * `channels` says what the page shows: 'timeline' (envelopes, game state),
 * 'interrogation' (suspects, questions) and 'accusation' (accusations, grading,
 * the ending). A question asked in an interrogation changes nothing in the
 * inbox, so the inbox watches only 'timeline' and does not reload because of
 * it. Leaving it out watches all three.
 *
 * Two brakes keep a busy table from flooding the server anyway: a change-driven
 * reload waits at least MIN_CHANGE_RELOAD_MS after the previous reload, and
 * failures back off exponentially instead of retrying at full speed. A
 * background reload that fails never replaces the page with an error screen.
 *
 * Inertia v1 has no `router.poll()`/`reload({ interval })` (v2+ features,
 * unavailable while pinned to v1 for Laravel 9 support), hence the hook.
 */
const SAFETY_RELOAD_MS = 120000;
const MIN_CHANGE_RELOAD_MS = 20000;
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
function hasChanged(stamps, seen, channels) {
    if (!stamps || !seen) return true;

    return channels.some((channel) => stamps[channel] !== seen[channel]);
}

export default function usePoll(only, { interval = 5000, enabled = true, channels = ALL_CHANNELS } = {}) {
    const { pollState } = usePage().props;
    const url = pollState?.url ?? null;

    // The stamps the page on screen corresponds to. A full page render brings
    // its own; the hook keeps them up to date after each background reload.
    const seen = useRef(pollState?.rev ?? null);
    const lastRev = pollState?.rev ?? null;

    useEffect(() => {
        seen.current = lastRev;
    }, [lastRev]);

    useEffect(() => {
        if (!enabled) return undefined;

        installInvalidGuard();

        let stopped = false;
        let timer = null;
        let busy = false;
        let failures = 0;
        let lastReloadAt = Date.now();

        const schedule = () => {
            if (stopped) return;

            const delay = Math.min(MAX_BACKOFF_MS, interval * 2 ** failures);
            timer = setTimeout(tick, delay);
        };

        const reload = () =>
            new Promise((resolve) => {
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

        const tick = async () => {
            if (stopped) return;

            // Nothing to refresh for a tab nobody is looking at; the
            // visibilitychange handler catches up the moment it returns.
            if (document.hidden || busy) {
                schedule();
                return;
            }

            busy = true;

            try {
                let stamps = null;
                let changed = true;

                if (url) {
                    const response = await fetch(url, {
                        headers: { Accept: 'application/json' },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });

                    if (!response.ok) {
                        throw new Error(`poll ${response.status}`);
                    }

                    stamps = (await response.json()).r ?? null;
                    changed = hasChanged(stamps, seen.current, channels);
                }

                const sinceReload = Date.now() - lastReloadAt;
                const stale = sinceReload >= SAFETY_RELOAD_MS;

                // A change waits its turn: a table asking questions moves the
                // stamps every few seconds, and a reload per tick per tab is
                // exactly the traffic this exists to avoid. Unknown stamps
                // (null) do not wait: something is wrong with the hint.
                const due = changed && (stamps === null || sinceReload >= MIN_CHANGE_RELOAD_MS);

                if (due || stale || !url) {
                    const ok = await reload();

                    if (!ok) {
                        failures += 1;
                    } else {
                        failures = 0;
                        lastReloadAt = Date.now();

                        // The stamps read BEFORE the reload: anything that
                        // changed while it ran shows up on the next tick.
                        if (stamps !== null) seen.current = stamps;
                    }
                } else {
                    failures = 0;
                }
            } catch {
                failures += 1;
            } finally {
                busy = false;
                schedule();
            }
        };

        const onVisible = () => {
            if (document.hidden || stopped) return;

            clearTimeout(timer);
            tick();
        };

        document.addEventListener('visibilitychange', onVisible);
        schedule();

        return () => {
            stopped = true;
            clearTimeout(timer);
            document.removeEventListener('visibilitychange', onVisible);
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [enabled, interval, url, JSON.stringify(only), JSON.stringify(channels)]);
}
