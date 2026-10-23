import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import axios from '../lib/axios';
import { trackEvent } from '../lib/analytics';
import { deriveClock } from '../lib/express';

/**
 * Live state of one Express room, for one player.
 *
 * The server owns the truth and the clock. This hook keeps a copy of the last
 * payload and does three things with it:
 *
 *  - Polls `express.state` (a JSON endpoint that costs the host no database
 *    connection). ~3 s with jitter, paused while the tab is hidden, an
 *    immediate refetch when it comes back, and exponential backoff up to 15 s
 *    on errors. Failures are silent: a phone in a crowded fair loses signal.
 *  - Keeps `offset = server_now_ms - Date.now()` so the countdown follows the
 *    SERVER's clock, not the phone's (which can be minutes off), and ticks it
 *    locally every 250 ms. A one-shot refetch is scheduled for the next
 *    boundary (unlock, accusations open, window end) so a reveal or a new
 *    clue shows up the moment it is due and not up to 3 s later.
 *  - Sends actions (`act`) and adopts the `state` that comes back. Payloads
 *    older than the one already on screen (`rev`) are ignored: a slow poll
 *    must never undo what an action just showed.
 *
 * Analytics gets only {case_slug, event_slug}: no nickname, no room code.
 */

const POLL_MS = 3000;
const POLL_JITTER_MS = 500;
const POLL_REVEAL_MS = 6000;
const MAX_BACKOFF_MS = 15000;
const TICK_MS = 250;
// A hair after the boundary, so the server's own rounding has already moved.
const BOUNDARY_SLACK_MS = 400;

const ACTION_EVENTS = {
    'express.share': 'express_evidence_shared',
    'express.ask': 'express_interrogation_used',
    'express.accuse': 'express_accusation_submitted',
};

function secondsSinceStart(state, offset) {
    const startedAt = state?.room?.started_at_ms;

    if (!startedAt) return null;

    return Math.max(0, Math.floor((Date.now() + offset - startedAt) / 1000));
}

function errorResult(error) {
    const status = error?.response?.status;
    const data = error?.response?.data;

    if (data && typeof data === 'object' && data.code) {
        return {
            ok: false,
            code: data.code,
            message: data.message || 'No se pudo completar la acción.',
            data,
        };
    }

    if (status === 422 && data?.errors) {
        const first = Object.values(data.errors).flat()[0];

        return { ok: false, code: 'invalid', message: first || 'Revisa lo que enviaste.', data };
    }

    // A 404 without a refusal code is the router saying the token is unknown.
    if (status === 404) {
        return { ok: false, code: 'gone', message: 'Este enlace ya no es válido.', data };
    }

    if (status === 419) {
        return {
            ok: false,
            code: 'expired',
            message: 'La sesión caducó. Recarga la página para seguir jugando.',
            data,
        };
    }

    if (status === 429) {
        return { ok: false, code: 'rate', message: 'Vas muy rápido. Espera un momento y reintenta.', data };
    }

    if (!error?.response) {
        return {
            ok: false,
            code: 'network',
            message: 'Parece que no hay conexión. Revisa tu señal e intenta de nuevo.',
            data: null,
        };
    }

    return { ok: false, code: 'error', message: 'Algo salió mal. Intenta de nuevo.', data };
}

export default function useExpressRoom(initialState, token) {
    const [state, setState] = useState(initialState);
    const stateRef = useRef(initialState);
    const offsetRef = useRef((initialState?.server_now_ms ?? Date.now()) - Date.now());
    const [elapsedS, setElapsedS] = useState(() => secondsSinceStart(initialState, offsetRef.current));
    // The token is unknown to the server (room pruned, link mistyped): the
    // page shows "enlace no válido" and every request stops.
    const [gone, setGone] = useState(false);
    const goneRef = useRef(false);
    const refetchRef = useRef(() => {});

    const markGone = useCallback(() => {
        goneRef.current = true;
        setGone(true);
    }, []);

    const trackParams = useCallback(() => {
        const current = stateRef.current?.room;

        return { case_slug: current?.case?.slug ?? '', event_slug: current?.event?.slug ?? '' };
    }, []);

    /** Adopts a payload unless it is older than the one on screen. */
    const apply = useCallback((next) => {
        if (!next || typeof next !== 'object' || !next.room) return false;

        const current = stateRef.current;

        if (typeof next.rev === 'number' && typeof current?.rev === 'number' && next.rev < current.rev) {
            return false;
        }

        // An EQUAL rev is still adopted: what changes with the clock alone
        // (a clue unlocking, a phase flipping) does not necessarily move it.
        if (typeof next.server_now_ms === 'number') {
            offsetRef.current = next.server_now_ms - Date.now();
        }

        stateRef.current = next;
        setState(next);
        setElapsedS(secondsSinceStart(next, offsetRef.current));

        return true;
    }, []);

    const fetchState = useCallback(async () => {
        try {
            const response = await axios.get(route('express.state', token), {
                headers: { Accept: 'application/json' },
                timeout: 8000,
            });
            const data = response.data;

            apply(data?.state ?? data);

            return true;
        } catch (error) {
            // 404: nobody has this token, stop for good. 429 and anything
            // else is transient: the caller backs off and tries again.
            if (error?.response?.status === 404) markGone();

            return false;
        }
    }, [apply, markGone, token]);

    // ---- polling ---------------------------------------------------------
    useEffect(() => {
        let stopped = false;
        let timer = null;
        let failures = 0;
        let inflight = false;

        function schedule() {
            clearTimeout(timer);

            if (stopped || document.hidden || goneRef.current) return;

            // An expired room never changes again: no reason to keep asking.
            if (stateRef.current?.room?.status === 'expired') return;

            const base = stateRef.current?.room?.status === 'reveal' ? POLL_REVEAL_MS : POLL_MS;
            const jitter = (Math.random() * 2 - 1) * POLL_JITTER_MS;
            const delay = Math.min(MAX_BACKOFF_MS, base * 2 ** failures) + jitter;

            timer = setTimeout(tick, Math.max(500, delay));
        }

        async function tick() {
            if (stopped || document.hidden || goneRef.current) return;

            if (inflight) {
                schedule();

                return;
            }

            inflight = true;
            const ok = await fetchState();
            inflight = false;

            failures = ok ? 0 : Math.min(failures + 1, 6);
            schedule();
        }

        function resume() {
            clearTimeout(timer);

            if (document.hidden || goneRef.current) return;

            tick();
        }

        refetchRef.current = () => {
            if (!stopped && !document.hidden && !goneRef.current) tick();
        };

        document.addEventListener('visibilitychange', resume);
        window.addEventListener('online', resume);
        schedule();

        return () => {
            stopped = true;
            clearTimeout(timer);
            document.removeEventListener('visibilitychange', resume);
            window.removeEventListener('online', resume);
        };
    }, [fetchState]);

    // ---- local clock -----------------------------------------------------
    const status = state?.room?.status;
    const startedAt = state?.room?.started_at_ms;

    useEffect(() => {
        if (!startedAt || (status !== 'playing' && status !== 'accusing')) return undefined;

        // setState with the same whole second is a no-op, so the page only
        // re-renders once per second even though this ticks four times.
        const interval = setInterval(() => {
            setElapsedS(secondsSinceStart(stateRef.current, offsetRef.current));
        }, TICK_MS);

        return () => clearInterval(interval);
    }, [startedAt, status]);

    // ---- refetch exactly at the next boundary ----------------------------
    useEffect(() => {
        const room = state?.room;

        if (!room?.started_at_ms || (room.status !== 'playing' && room.status !== 'accusing')) {
            return undefined;
        }

        const schedule = room.schedule ?? {};
        const points = [
            state.next_unlock?.at_s,
            schedule.interrogation_opens_s,
            schedule.accusations_open_s,
            schedule.window_end_s,
            schedule.hard_close_s,
        ];

        if (typeof state.accusation?.closes_in_s === 'number' && state.server_now_ms) {
            points.push((state.server_now_ms - room.started_at_ms) / 1000 + state.accusation.closes_in_s);
        }

        const elapsedMs = Date.now() + offsetRef.current - room.started_at_ms;
        const next = points
            .filter((value) => typeof value === 'number' && Number.isFinite(value))
            .map((value) => value * 1000)
            .filter((value) => value > elapsedMs)
            .sort((a, b) => a - b)[0];

        if (next === undefined) return undefined;

        const timer = setTimeout(() => refetchRef.current(), next - elapsedMs + BOUNDARY_SLACK_MS);

        return () => clearTimeout(timer);
    }, [state]);

    // ---- analytics -------------------------------------------------------
    const joinedTracked = useRef(false);
    const completedTracked = useRef(false);

    useEffect(() => {
        if (joinedTracked.current) return;

        joinedTracked.current = true;
        trackEvent('express_joined', trackParams());
    }, [trackParams]);

    useEffect(() => {
        if (status !== 'reveal' || completedTracked.current) return;

        completedTracked.current = true;

        // Once per room per browser, not once per reload of the reveal page.
        const key = `express_completed_${state?.room?.code ?? ''}`;

        try {
            if (window.sessionStorage.getItem(key)) return;
            window.sessionStorage.setItem(key, '1');
        } catch {
            // Storage blocked: reporting twice beats not reporting.
        }

        trackEvent('express_completed', trackParams());
    }, [status, state?.room?.code, trackParams]);

    // ---- actions ---------------------------------------------------------
    const act = useCallback(
        async (routeName, body = {}) => {
            try {
                const response = await axios.post(route(routeName, token), body, {
                    headers: { Accept: 'application/json' },
                });
                const data = response.data ?? {};

                if (data.state) apply(data.state);

                const ok = data.ok !== false;

                if (ok && ACTION_EVENTS[routeName]) {
                    trackEvent(ACTION_EVENTS[routeName], trackParams());
                }

                return { ok, code: data.code ?? null, message: data.message ?? null, data };
            } catch (error) {
                const result = errorResult(error);

                if (result.code === 'gone') markGone();

                // The clock moved under us (round closed, clue not unlocked
                // yet): look again instead of leaving a stale screen.
                if (['closed', 'not_started', 'too_early', 'join_closed'].includes(result.code)) {
                    refetchRef.current();
                }

                return result;
            }
        },
        [apply, markGone, token, trackParams]
    );

    const clock = useMemo(() => deriveClock(state, elapsedS), [state, elapsedS]);

    return { state, elapsedS, clock, act, gone, refresh: fetchState };
}
