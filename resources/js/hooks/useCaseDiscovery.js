import { usePage } from '@inertiajs/react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import {
    applyFilters,
    emptyFilters,
    indexCases,
    isActive,
    optionsFor,
    parseFilters,
    priceRangeError,
    toQuery,
} from '../lib/caseDiscovery';

/**
 * Search and filter a list of cases without asking the server.
 *
 * The address bar still follows the filters (so a filtered view can be shared
 * or reloaded), but it is rewritten in place with the History API: no Inertia
 * visit, so no request and no database connection.
 */
export default function useCaseDiscovery(items, area) {
    const { url } = usePage();
    const options = useMemo(() => optionsFor(items), [items]);
    const entries = useMemo(() => indexCases(items), [items]);

    const [filters, setFilters] = useState(() => parseFilters(
        url.includes('?') ? url.slice(url.indexOf('?') + 1) : '',
        area,
        {
            mechanics: options.mechanics.map((mechanic) => mechanic.slug),
            maxPlayers: Math.max(1, ...options.players),
        }
    ));

    const query = toQuery(filters, area);
    const firstRun = useRef(true);

    // Typing a search must not rewrite the address on every keystroke (Safari
    // throws after about a hundred history writes in half a minute).
    useEffect(() => {
        if (firstRun.current) {
            firstRun.current = false;
            return undefined;
        }

        const timer = setTimeout(() => {
            try {
                const next = window.location.pathname + (query ? `?${query}` : '') + window.location.hash;
                window.history.replaceState(window.history.state, '', next);
            } catch {
                // A browser that refuses the write only loses the shareable address.
            }
        }, 400);

        return () => clearTimeout(timer);
    }, [query]);

    const update = useCallback((change) => setFilters((current) => ({ ...current, ...change })), []);
    const reset = useCallback(() => setFilters((current) => ({ ...emptyFilters(area), sort: current.sort })), [area]);

    const results = useMemo(() => applyFilters(entries, filters), [entries, filters]);

    return {
        area,
        filters,
        update,
        reset,
        options,
        results,
        active: isActive(filters),
        priceError: priceRangeError(filters),
    };
}
