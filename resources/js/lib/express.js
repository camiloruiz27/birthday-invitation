/**
 * Small pure helpers for the Express fair demo (Pages/Express, components/express).
 * No React in here: everything is testable on its own.
 */

/** 75 -> "1:15". Negative and non-numeric values read as 0:00. */
export function formatClock(totalSeconds) {
    const safe = Number.isFinite(totalSeconds) ? Math.max(0, Math.ceil(totalSeconds)) : 0;
    const minutes = Math.floor(safe / 60);
    const seconds = safe % 60;

    return `${minutes}:${String(seconds).padStart(2, '0')}`;
}

/** "Nora Leal" -> "NL", "ana" -> "A". Never empty. */
export function initials(name) {
    const parts = String(name ?? '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (parts.length === 0) return '?';

    return parts
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
}

/**
 * Evidence lines come as "10:58 | Alma P. entra a Sala B.". A line without the
 * separator is just text.
 */
export function parseLine(line) {
    const text = String(line ?? '');
    const index = text.indexOf(' | ');

    if (index === -1) {
        return { label: null, value: text.trim() };
    }

    return { label: text.slice(0, index).trim(), value: text.slice(index + 3).trim() };
}

/** Only the lines that are real strings with something in them. */
export function cleanLines(lines) {
    return (Array.isArray(lines) ? lines : []).filter(
        (line) => typeof line === 'string' && line.trim() !== ''
    );
}

/**
 * What the countdown shows, derived from the server clock.
 *
 * `elapsedS` is whole seconds since the round started (null in the lobby). The
 * server's own `closes_in_s` is anchored to the moment the payload was made
 * (`server_now_ms`), so it is turned into an absolute point on the round's
 * clock; that way it keeps counting between polls instead of freezing.
 */
export function deriveClock(state, elapsedS) {
    const room = state?.room ?? {};
    const schedule = room.schedule ?? {};
    const status = room.status;

    if (elapsedS == null || (status !== 'playing' && status !== 'accusing')) {
        return null;
    }

    const startedAt = room.started_at_ms;
    const closesIn = state?.accusation?.closes_in_s;

    let closesAtS = null;
    if (typeof closesIn === 'number' && startedAt && state?.server_now_ms) {
        closesAtS = (state.server_now_ms - startedAt) / 1000 + closesIn;
    } else if (typeof schedule.window_end_s === 'number') {
        closesAtS = schedule.window_end_s;
    }

    const accusationsOpenIn =
        typeof schedule.accusations_open_s === 'number' ? schedule.accusations_open_s - elapsedS : null;
    const interrogationOpenIn =
        typeof schedule.interrogation_opens_s === 'number'
            ? schedule.interrogation_opens_s - elapsedS
            : null;
    const nextUnlockIn =
        typeof state?.next_unlock?.at_s === 'number' ? state.next_unlock.at_s - elapsedS : null;
    const closesInLeft = closesAtS == null ? null : closesAtS - elapsedS;
    const hardCloseIn =
        typeof schedule.hard_close_s === 'number' ? schedule.hard_close_s - elapsedS : null;

    if (status === 'playing') {
        return {
            stage: 'playing',
            label: 'Investiguen',
            caption: 'para acusar',
            leftS: accusationsOpenIn,
            totalS: schedule.accusations_open_s ?? null,
            accusationsOpenIn,
            interrogationOpenIn,
            nextUnlockIn,
            closesInLeft,
            hardCloseIn,
        };
    }

    return {
        stage: 'accusing',
        label: 'Acusaciones abiertas',
        caption: 'para el desenlace',
        leftS: closesInLeft ?? hardCloseIn,
        totalS: null,
        accusationsOpenIn: 0,
        interrogationOpenIn,
        nextUnlockIn,
        closesInLeft,
        hardCloseIn,
    };
}

/** Whole seconds, never negative; null stays null. */
export function secondsLeft(value) {
    return typeof value === 'number' && Number.isFinite(value) ? Math.max(0, Math.ceil(value)) : null;
}
