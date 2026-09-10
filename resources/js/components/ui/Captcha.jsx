import { useEffect, useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';

const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';

/**
 * Loaded once for the whole session, not once per form.
 *
 * Turnstile registers a single global (window.turnstile), so a second <script>
 * tag would either be ignored or clobber the first. The promise is what lets
 * several forms mount at once and all wait on the same load.
 */
let scriptPromise = null;

function loadTurnstile() {
    if (window.turnstile) {
        return Promise.resolve(window.turnstile);
    }

    if (!scriptPromise) {
        scriptPromise = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = SCRIPT_URL;
            script.async = true;
            script.defer = true;
            script.onload = () => resolve(window.turnstile);
            // Cleared so a later mount can retry: a failed load is usually a
            // dropped connection, not a permanent condition.
            script.onerror = () => {
                scriptPromise = null;
                reject(new Error('turnstile-script-failed'));
            };
            document.head.appendChild(script);
        });
    }

    return scriptPromise;
}

/**
 * The "confirm you're not a robot" widget, for the four forms an automated
 * script can point at the platform from outside: creating an account, signing
 * in, asking for a password reset, and redeeming a code.
 *
 * Renders nothing at all when no site key is configured, which is how local
 * development and the test suite run without a Cloudflare account. The server
 * makes the same call independently (see CaptchaRule) — this component never
 * decides whether the check happens, only whether there is a box to draw.
 *
 * `resetKey` re-issues the challenge. A token is single-use, so after any
 * rejected submission the one held in form state is already spent: without a
 * reset the visitor's second attempt fails the captcha no matter what they
 * typed, which reads as "the site is broken".
 */
export default function Captcha({ onToken, error, resetKey = 0 }) {
    const { props } = usePage();
    const siteKey = props.captchaSiteKey;
    const container = useRef(null);
    const [failed, setFailed] = useState(false);

    useEffect(() => {
        if (!siteKey || !container.current) {
            return undefined;
        }

        let widgetId = null;
        let cancelled = false;

        loadTurnstile()
            .then((turnstile) => {
                if (cancelled || !container.current) {
                    return;
                }

                widgetId = turnstile.render(container.current, {
                    sitekey: siteKey,
                    theme: 'auto',
                    callback: (token) => onToken(token),
                    // A token that expires before the form is submitted is
                    // worse than no token, so it is cleared rather than left
                    // to be rejected by the server later.
                    'expired-callback': () => onToken(''),
                    'error-callback': () => {
                        onToken('');
                        setFailed(true);
                    },
                });
            })
            .catch(() => {
                if (!cancelled) {
                    setFailed(true);
                }
            });

        return () => {
            cancelled = true;

            if (widgetId !== null && window.turnstile) {
                window.turnstile.remove(widgetId);
            }
        };
        // onToken is a fresh closure on every render; depending on it would
        // tear down and re-render the widget on every keystroke in the form.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [siteKey, resetKey]);

    if (!siteKey) {
        return null;
    }

    return (
        <div>
            <div ref={container} />

            {failed && (
                <p className="mt-1.5 text-xs text-ink-muted">
                    No pudimos cargar la verificación anti-robots. Revisa tu conexión y
                    recarga la página.
                </p>
            )}

            {error && <p className="mt-1.5 text-xs font-medium text-danger">{error}</p>}
        </div>
    );
}
