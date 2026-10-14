import { useCallback, useEffect, useMemo, useState } from 'react';

/**
 * What is new for the player, and what they have already been told.
 *
 * The server says what exists (the `notices` prop: envelopes sent, the
 * interrogations opening, the accusations opening, the ending). What a person
 * has seen lives here, in their own browser, per game — the same place the
 * inbox already remembers which envelopes they opened. Nothing about it is
 * stored on the server, so it costs nothing to keep in step and a new device
 * simply starts from a clean slate.
 *
 * Three sets, all in localStorage:
 *  - `case-inbox:{game}:read`      envelopes opened (written by the inbox)
 *  - `case-notices:{game}:seen`    notices already announced with a toast
 *  - `case-notices:{game}:visited` sections the player has opened since
 *
 * Private browsing throws on localStorage; everything then falls back to memory
 * for the life of the page, which only means a notice can repeat, never that a
 * page fails to render.
 */

export const INBOX_READ_EVENT = 'case-inbox-read';

const readKey = (gameId) => `case-inbox:${gameId}:read`;
const seenKey = (gameId) => `case-notices:${gameId}:seen`;
const visitedKey = (gameId) => `case-notices:${gameId}:visited`;

function load(key) {
    try {
        const raw = window.localStorage.getItem(key);

        return raw === null ? null : new Set(JSON.parse(raw));
    } catch {
        return null;
    }
}

function save(key, set) {
    try {
        window.localStorage.setItem(key, JSON.stringify([...set]));
    } catch {
        /* memory only */
    }
}

/**
 * @param {{ gameId: number|undefined, notices: object|null|undefined, section: string|undefined, enabled?: boolean }} options
 * @returns {{ badges: Record<string, number>, total: number, toasts: Array<object>, dismiss: (key: string) => void }}
 */
export default function usePlayerNotices({ gameId, notices, section, enabled = true }) {
    const active = enabled && Boolean(gameId) && Boolean(notices) && typeof window !== 'undefined';

    const [read, setRead] = useState(() => (active ? (load(readKey(gameId)) ?? new Set()) : new Set()));
    const [visited, setVisited] = useState(() => (active ? (load(visitedKey(gameId)) ?? new Set()) : new Set()));
    const [seen, setSeen] = useState(null);
    const [toasts, setToasts] = useState([]);

    // The inbox writes which envelopes were opened; hear about it here.
    useEffect(() => {
        if (!active) return undefined;

        const refresh = () => setRead(load(readKey(gameId)) ?? new Set());

        window.addEventListener(INBOX_READ_EVENT, refresh);
        window.addEventListener('storage', refresh);

        return () => {
            window.removeEventListener(INBOX_READ_EVENT, refresh);
            window.removeEventListener('storage', refresh);
        };
    }, [active, gameId]);

    // Opening a section is how its notice is acknowledged.
    useEffect(() => {
        if (!active || !section || visited.has(section)) return;

        const next = new Set(visited).add(section);
        setVisited(next);
        save(visitedKey(gameId), next);
    }, [active, gameId, section, visited]);

    const items = notices?.items;

    // What to announce. The first look at a game from this browser only records
    // what already exists: opening the page for the first time must not replay
    // the whole history as a pile of toasts. After that, whatever is new is a toast.
    useEffect(() => {
        if (!active || !items) return;

        const stored = load(seenKey(gameId));
        const known = seen ?? stored;

        if (known === null) {
            const baseline = new Set(items.map((item) => item.key));
            setSeen(baseline);
            save(seenKey(gameId), baseline);

            return;
        }

        const fresh = items.filter((item) => !known.has(item.key));

        if (seen === null) {
            setSeen(known);
        }

        if (fresh.length === 0) return;

        const next = new Set(known);
        fresh.forEach((item) => next.add(item.key));

        setSeen(next);
        save(seenKey(gameId), next);
        setToasts((current) => [...current, ...fresh].slice(-3));
    }, [active, gameId, items, seen]);

    const dismiss = useCallback((key) => setToasts((current) => current.filter((toast) => toast.key !== key)), []);

    const badges = useMemo(() => {
        const counts = { inbox: 0, interrogation: 0, accusation: 0, solution: 0 };

        if (!active || !items) return counts;

        counts.inbox = items.filter((item) => item.kind === 'envelope' && !read.has(item.event_id)).length;

        // A dot, not a count: these are places to go, not things to read.
        counts.interrogation = items.some((item) => item.kind === 'interrogation') && !visited.has('interrogation') ? 1 : 0;

        counts.accusation = notices.accusations_open && !notices.accused && !visited.has('accusation') ? 1 : 0;

        counts.solution =
            (notices.ending_revealed || items.some((item) => item.kind === 'epilogue')) && !visited.has('solution')
                ? 1
                : 0;

        // The page they are on is read by definition (the inbox counts what
        // was opened, not what was visited).
        if (section && section !== 'inbox' && counts[section] !== undefined) {
            counts[section] = 0;
        }

        return counts;
    }, [active, items, notices, read, visited, section]);

    const total = badges.inbox + badges.interrogation + badges.accusation + badges.solution;

    return { badges, total, toasts, dismiss };
}
