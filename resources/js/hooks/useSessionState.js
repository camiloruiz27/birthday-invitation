import { useCallback, useState } from 'react';

/**
 * useState that survives a remount, backed by sessionStorage.
 *
 * Why it exists: closing the photo viewer calls history.back(), and Inertia
 * (v1) reacts to that popstate by re-rendering the whole page under a new key.
 * Everything held in component state is lost — the envelope stays open (it
 * lives in the URL hash) but its tab jumped back to "Documento". Anything a
 * reader would be annoyed to see reset belongs here.
 *
 * sessionStorage can throw (Safari private mode, blocked storage); a failed
 * read or write just means the value is not remembered, never a broken page.
 */
export default function useSessionState(key, initial) {
    const [value, setValue] = useState(() => {
        try {
            const stored = window.sessionStorage.getItem(key);

            return stored === null ? initial : JSON.parse(stored);
        } catch {
            return initial;
        }
    });

    const update = useCallback(
        (next) => {
            setValue(next);

            try {
                window.sessionStorage.setItem(key, JSON.stringify(next));
            } catch {
                /* see above */
            }
        },
        [key]
    );

    return [value, update];
}
