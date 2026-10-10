import { useEffect, useRef, useState } from 'react';
import Button from '../ui/Button';
import TextLink from '../ui/TextLink';
import { CheckboxField } from '../ui/Field';
import { OPEN_SETTINGS_EVENT, adsConfigured, getConsent, setConsent } from '../../lib/consent';

/**
 * The cookie consent banner.
 *
 * Four levels (see lib/consent.js): anonymous cookieless measurement that is
 * always on and only disclosed here, and three opt-in choices — audience
 * analytics (Google Analytics cookies), session recordings (Microsoft
 * Clarity) and advertising measurement (the TikTok and Meta pixels), each off
 * until the visitor says yes. Ley 1581 de 2012 asks for
 * prior and express authorization; tacit consent is not valid in Colombia.
 *
 * "Aceptar todo" and "Rechazar" are the same size and weight on purpose:
 * refusing must be exactly as easy as accepting. The same panel reopens from
 * the "Preferencias de cookies" links (footer, profile) via OPEN_SETTINGS_EVENT,
 * so withdrawing consent is never harder than giving it.
 *
 * Mounted once from app.jsx, outside the Inertia page, so it survives
 * navigation and every layout gets it without each one remembering to.
 */
function CookieIcon() {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.6"
            aria-hidden="true"
            className="h-5 w-5 shrink-0 text-accent-strong"
        >
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M21 12.4A9 9 0 1111.6 3a3.4 3.4 0 004.4 4.4 3 3 0 005 5z"
            />
            <circle cx="9" cy="11" r="1" fill="currentColor" stroke="none" />
            <circle cx="13" cy="16" r="1" fill="currentColor" stroke="none" />
            <circle cx="8.5" cy="15.5" r=".7" fill="currentColor" stroke="none" />
        </svg>
    );
}

function CheckIcon() {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            aria-hidden="true"
            className="mt-0.5 h-4 w-4 shrink-0 text-accent-strong"
        >
            <path strokeLinecap="round" strokeLinejoin="round" d="M5 12.5l4.5 4.5L19 7.5" />
        </svg>
    );
}

export default function CookieConsent() {
    const [open, setOpen] = useState(false);
    const [showSettings, setShowSettings] = useState(false);
    const [analytics, setAnalytics] = useState(false);
    const [recording, setRecording] = useState(false);
    const [marketing, setMarketing] = useState(false);
    // Only ask about advertising when a pixel is actually configured; read
    // after mount like the stored choice, since it comes from the page's own
    // <meta> tag.
    const [adsEnabled, setAdsEnabled] = useState(false);
    const dialogRef = useRef(null);

    // Decide whether to show it only after mount: the choice lives in a
    // cookie that the server cannot see, and reading it during render would
    // mismatch whatever was rendered before.
    useEffect(() => {
        setAdsEnabled(adsConfigured());

        const consent = getConsent();

        if (consent === null) {
            setOpen(true);
        } else {
            setAnalytics(consent.analytics);
            setRecording(consent.recording);
            setMarketing(consent.marketing);
        }

        const reopen = () => {
            const current = getConsent();

            setAnalytics(current?.analytics ?? false);
            setRecording(current?.recording ?? false);
            setMarketing(current?.marketing ?? false);
            setShowSettings(true);
            setOpen(true);
        };

        window.addEventListener(OPEN_SETTINGS_EVENT, reopen);

        return () => window.removeEventListener(OPEN_SETTINGS_EVENT, reopen);
    }, []);

    // Reopening from a footer link moves focus into the dialog: otherwise a
    // keyboard or screen-reader user clicks the link and nothing seems to
    // happen, the panel being at the far end of the page. Only then — on the
    // first visit the banner appears by itself and must not steal focus from
    // the page the visitor is reading.
    useEffect(() => {
        if (open && showSettings) dialogRef.current?.focus();
    }, [open, showSettings]);

    if (!open) return null;

    function decide(choice) {
        setConsent(choice);
        setAnalytics(choice.analytics);
        setRecording(choice.recording);
        setMarketing(choice.marketing);
        setShowSettings(false);
        setOpen(false);
    }

    // Closing the panel without saving only makes sense once a choice exists;
    // before that the banner must stay until the visitor answers.
    function dismissSettings() {
        setShowSettings(false);
        if (getConsent() !== null) setOpen(false);
    }

    return (
        <div
            ref={dialogRef}
            role="dialog"
            aria-modal="false"
            aria-labelledby="cookie-consent-title"
            tabIndex={-1}
            // Inline, not a class: the app's global :focus-visible ring is
            // unlayered CSS and beats Tailwind's layered outline-none, which
            // drew a full-width line across the top of the banner. This
            // wrapper is only a focus target, never a control.
            style={{ outline: 'none' }}
            className="fixed inset-x-0 bottom-0 z-50 px-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6 sm:pb-[max(1rem,env(safe-area-inset-bottom))]"
        >
            <div className="mx-auto max-h-[85dvh] w-full max-w-3xl overflow-y-auto rounded-card border border-line-strong bg-surface-raised p-4 shadow-overlay sm:p-6">
                {/* Text on the left, answers on the right from sm up; on a phone
                    everything stacks and stays short (it used to take 40% of
                    the screen and sat on top of the page's own button).

                    The mandatory notice is a line of text with a check, not a
                    box with a "Siempre activo" tag: it is not a choice, so it
                    must not look like a control. The detail lives in the two
                    policies. */}
                <div className="sm:flex sm:items-start sm:gap-8">
                    <div className="min-w-0 flex-1">
                        <div className="flex items-center justify-between gap-3">
                            <h2
                                id="cookie-consent-title"
                                className="flex items-center gap-2.5 font-display text-base font-semibold text-ink sm:text-lg"
                            >
                                <CookieIcon />
                                <span className="sm:hidden">Cookies</span>
                                <span className="hidden sm:inline">Tu privacidad, en corto</span>
                            </h2>

                            {!showSettings && (
                                <div className="sm:hidden">
                                    <Button variant="ghost" onClick={() => setShowSettings(true)}>
                                        Configurar
                                    </Button>
                                </div>
                            )}
                        </div>

                        <p className="mt-3 flex items-start gap-2 text-[13px] leading-snug text-ink-muted sm:text-sm sm:leading-relaxed">
                            <CheckIcon />
                            <span>
                                <span className="sr-only">Siempre activo: </span>
                                Es obligatorio para el correcto funcionamiento y conocimiento de la
                                plataforma.
                            </span>
                        </p>

                        <p className="mt-2 text-[13px] leading-snug text-ink-muted sm:text-sm sm:leading-relaxed">
                            Lo demás es opcional y lo decides tú. Más detalle en{' '}
                            <TextLink href={route('cookies')}>Cookies</TextLink> y{' '}
                            <TextLink href={route('privacy')}>Privacidad</TextLink>.
                        </p>

                        {showSettings && (
                            <div className="mt-4 space-y-3 border-t border-line pt-4">
                                <p className="case-stamp text-[10px] text-ink-subtle">Opcional</p>

                                <CheckboxField
                                    id="cookie-analytics"
                                    label="Habilita la recolección de datos para análisis de uso"
                                    checked={analytics}
                                    onChange={setAnalytics}
                                />

                                <CheckboxField
                                    id="cookie-recording"
                                    label="Habilita la recolección de datos de uso de la plataforma"
                                    checked={recording}
                                    onChange={setRecording}
                                />

                                {adsEnabled && (
                                    <CheckboxField
                                        id="cookie-marketing"
                                        label="Habilita la recolección de datos para publicidad"
                                        checked={marketing}
                                        onChange={setMarketing}
                                    />
                                )}
                            </div>
                        )}
                    </div>

                    {/* "Rechazar" and "Aceptar todo" are the same size and weight
                        on purpose: refusing must be as easy as accepting. Two
                        side by side on a phone, a column from sm up. */}
                    <div className="mt-4 grid grid-cols-2 gap-2 sm:mt-0 sm:flex sm:w-44 sm:shrink-0 sm:flex-col">
                        {showSettings ? (
                            <>
                                <Button
                                    variant="secondary"
                                    fullWidth
                                    onClick={() => decide({ analytics, recording, marketing })}
                                >
                                    Guardar
                                </Button>
                                <Button variant="ghost" fullWidth onClick={dismissSettings}>
                                    {getConsent() === null ? 'Volver' : 'Cerrar'}
                                </Button>
                            </>
                        ) : (
                            <>
                                <Button
                                    variant="secondary"
                                    fullWidth
                                    onClick={() =>
                                        decide({ analytics: false, recording: false, marketing: false })
                                    }
                                >
                                    Rechazar
                                </Button>
                                <Button
                                    variant="secondary"
                                    fullWidth
                                    onClick={() =>
                                        decide({ analytics: true, recording: true, marketing: true })
                                    }
                                >
                                    Aceptar todo
                                </Button>
                                {/* On a phone this lives next to the title instead. */}
                                <div className="hidden sm:block">
                                    <Button variant="ghost" fullWidth onClick={() => setShowSettings(true)}>
                                        Configurar
                                    </Button>
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
