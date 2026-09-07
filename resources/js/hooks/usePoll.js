import { useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';

/**
 * Manual polling for Inertia v1, which has no `router.poll()`/`reload({ interval })`
 * (those are Inertia v2+ features, unavailable while pinned to v1 for Laravel 9 support).
 */
export default function usePoll(only, { interval = 5000, enabled = true } = {}) {
    const inFlight = useRef(false);

    useEffect(() => {
        if (!enabled) return undefined;

        const id = setInterval(() => {
            if (inFlight.current || document.hidden) return;

            inFlight.current = true;
            router.reload({
                only,
                preserveScroll: true,
                preserveState: true,
                onFinish: () => {
                    inFlight.current = false;
                },
            });
        }, interval);

        return () => clearInterval(id);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [enabled, interval, JSON.stringify(only)]);
}
