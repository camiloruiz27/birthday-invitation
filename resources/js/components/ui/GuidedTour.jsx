import { useCallback, useEffect, useLayoutEffect, useRef, useState } from 'react';
import Button from './Button';
import { CONSENT_EVENT, getConsent } from '../../lib/consent';
import { START_TOUR_EVENT, hasSeenTour, markTourSeen } from '../../lib/tour';

/**
 * A short guided tour: a dimmed screen, a ring around the thing being
 * explained and a card that says what it is for.
 *
 * Built by hand instead of pulling a tour library: the player screens are
 * used on phones and the bundle is already a concern, and what is needed is
 * small — point at an element, say something, move on.
 *
 * Each step is `{ target, title, body }`. `target` is a CSS selector (the app
 * uses `[data-tour="..."]`); a step without one, or whose element is not on
 * screen, is shown as a centered card, and a step that is `optional` is
 * dropped instead. The same element can exist twice with one hidden (the
 * player navigation is both a header and a bottom bar), so the first VISIBLE
 * match wins.
 *
 * It opens by itself the first time (see lib/tour.js for how "first" is
 * remembered for a year), only once `enabled`, and never on top of the cookie
 * banner: it waits for that to be answered. `startTour(id)` reopens it on
 * demand.
 */

const RING_PADDING = 6;

function visibleTarget(selector) {
    if (!selector) return null;

    return (
        [...document.querySelectorAll(selector)].find(
            (element) => element.getClientRects().length > 0
        ) ?? null
    );
}

export default function GuidedTour({ id, steps, enabled = true }) {
    const [running, setRunning] = useState(false);
    const [index, setIndex] = useState(0);
    const [rect, setRect] = useState(null);
    const [active, setActive] = useState([]);
    const [consentOpen, setConsentOpen] = useState(false);
    const cardRef = useRef(null);

    // The consent banner is answered once per browser; until then it owns the
    // bottom of the screen, and two overlays at once is how people close both.
    useEffect(() => {
        const sync = () => setConsentOpen(getConsent() === null);

        sync();
        window.addEventListener(CONSENT_EVENT, sync);

        return () => window.removeEventListener(CONSENT_EVENT, sync);
    }, []);

    const begin = useCallback(() => {
        const usable = steps.filter((step) => !step.optional || visibleTarget(step.target));

        if (usable.length === 0) return;

        setActive(usable);
        setIndex(0);
        setRunning(true);
    }, [steps]);

    // First time: open on its own once nothing else is in the way.
    useEffect(() => {
        if (!enabled || consentOpen || running || hasSeenTour(id)) return undefined;

        // A beat after the page settles, so the target exists and the person
        // sees the screen before it is dimmed.
        const timer = window.setTimeout(begin, 900);

        return () => window.clearTimeout(timer);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [enabled, consentOpen, id]);

    // "Ver el tutorial" opens it again, seen or not.
    useEffect(() => {
        const onStart = (event) => {
            if (event.detail?.id === id) begin();
        };

        window.addEventListener(START_TOUR_EVENT, onStart);

        return () => window.removeEventListener(START_TOUR_EVENT, onStart);
    }, [id, begin]);

    const step = running ? active[index] : null;

    const close = useCallback(() => {
        markTourSeen(id);
        setRunning(false);
        setRect(null);
    }, [id]);

    // Bring the element into view and follow it while the page moves.
    useLayoutEffect(() => {
        if (!step) return undefined;

        const element = visibleTarget(step.target);

        if (!element) {
            setRect(null);

            return undefined;
        }

        element.scrollIntoView({ block: 'center', behavior: 'instant' });

        const measure = () => {
            const box = element.getBoundingClientRect();

            setRect({
                top: box.top - RING_PADDING,
                left: box.left - RING_PADDING,
                width: box.width + RING_PADDING * 2,
                height: box.height + RING_PADDING * 2,
            });
        };

        measure();
        window.addEventListener('resize', measure);
        window.addEventListener('scroll', measure, true);

        return () => {
            window.removeEventListener('resize', measure);
            window.removeEventListener('scroll', measure, true);
        };
    }, [step]);

    // Escape skips; focus moves to the card so keyboard users are inside it.
    useEffect(() => {
        if (!running) return undefined;

        const onKey = (event) => {
            if (event.key === 'Escape') close();
        };

        window.addEventListener('keydown', onKey);
        cardRef.current?.focus();

        return () => window.removeEventListener('keydown', onKey);
    }, [running, index, close]);

    if (!step) return null;

    const last = index === active.length - 1;

    // The card goes where there is more free room around the target, so it
    // does not cover what it is describing — on a phone the target is often
    // the bottom navigation, and a tall one (a list) must not push the card
    // on top of itself.
    const spaceAbove = rect ? Math.max(0, rect.top) : 0;
    const spaceBelow = rect ? Math.max(0, window.innerHeight - (rect.top + rect.height)) : 0;
    const position = rect ? (spaceBelow > spaceAbove ? 'bottom' : 'top') : 'center';

    const cardPlacement = {
        top: 'top-4 sm:top-20',
        bottom: 'bottom-4 sm:bottom-6',
        center: 'top-1/2 -translate-y-1/2',
    }[position];

    return (
        <div className="fixed inset-0 z-40" role="presentation">
            {/* Swallows taps while the tour is up, so a stray one does not
                navigate away mid-explanation. */}
            <div className="absolute inset-0" onClick={(event) => event.stopPropagation()} />

            {rect ? (
                <div
                    aria-hidden="true"
                    className="pointer-events-none absolute rounded-card transition-all duration-200 motion-reduce:transition-none"
                    style={{
                        top: rect.top,
                        left: rect.left,
                        width: rect.width,
                        height: rect.height,
                        // The ring, then the dimming of everything else.
                        boxShadow:
                            '0 0 0 2px var(--color-accent), 0 0 0 9999px rgba(0, 0, 0, 0.72)',
                    }}
                />
            ) : (
                <div aria-hidden="true" className="absolute inset-0 bg-black/70" />
            )}

            <div
                ref={cardRef}
                tabIndex={-1}
                style={{ outline: 'none' }}
                role="dialog"
                aria-label={step.title}
                className={`absolute inset-x-4 mx-auto max-w-md rounded-card border border-line-strong bg-surface-raised p-5 shadow-overlay outline-none ${cardPlacement}`}
            >
                <p className="case-stamp text-[10px] text-accent">
                    Paso {index + 1} de {active.length}
                </p>
                <h2 className="mt-1 font-display text-lg font-semibold text-ink">{step.title}</h2>
                <p className="mt-2 text-sm leading-relaxed text-ink-muted">{step.body}</p>

                <div className="mt-5 flex items-center justify-between gap-2">
                    <Button variant="ghost" size="sm" onClick={close}>
                        {last ? 'Cerrar' : 'Saltar'}
                    </Button>

                    <div className="flex gap-2">
                        {index > 0 && (
                            <Button
                                variant="secondary"
                                size="sm"
                                onClick={() => setIndex((current) => current - 1)}
                            >
                                Anterior
                            </Button>
                        )}
                        <Button
                            size="sm"
                            onClick={() => (last ? close() : setIndex((current) => current + 1))}
                        >
                            {last ? 'Entendido' : 'Siguiente'}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    );
}
