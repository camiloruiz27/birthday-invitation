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
            <div className="mx-auto max-h-[85dvh] w-full max-w-3xl overflow-y-auto rounded-card border border-line-strong bg-surface-raised p-3 shadow-overlay sm:p-6">
                {/* On a phone this banner used to take 40% of the screen and sat
                    on top of the page's own button. It is kept small there: a
                    one-word title with the settings link on the same line, the
                    mandatory notice and the "optional" line as ONE short
                    paragraph, and only the two answers as buttons. From sm up
                    it is the roomier layout it always was. */}
                <div className="flex items-center justify-between gap-3">
                    <h2 id="cookie-consent-title" className="font-display text-base font-semibold text-ink sm:text-lg">
                        <span className="sm:hidden">Cookies</span>
                        <span className="hidden sm:inline">Una cosa rápida sobre las cookies</span>
                    </h2>

                    {!showSettings && (
                        <div className="sm:hidden">
                            <Button variant="ghost" onClick={() => setShowSettings(true)}>
                                Configurar
                            </Button>
                        </div>
                    )}
                </div>

                {/* Two things, said plainly and up front: what is mandatory
                    (always on, no choice to make) and that everything else is
                    optional. The mandatory space is NOT a toggle — it covers
                    the necessary cookies and the anonymous, cookieless count of
                    visits, which is disclosed here rather than asked for (see
                    lib/consent.js). The detail lives in the two policies. */}
                <div className="sm:mt-4 sm:rounded-control sm:border sm:border-line sm:bg-surface-sunken sm:px-3 sm:py-2.5">
                    <div className="hidden items-center justify-between gap-4 sm:flex">
                        <p className="text-sm font-medium text-ink">Obligatorio</p>
                        <span className="text-xs font-medium text-ink-muted">Siempre activo</span>
                    </div>
                    <p className="text-[13px] leading-snug text-ink-muted sm:mt-1 sm:text-sm sm:leading-relaxed">
                        <span className="font-medium text-ink sm:hidden">Obligatorio (siempre activo): </span>
                        Es obligatorio para el correcto funcionamiento y conocimiento de la plataforma.
                        {/* Phone only; from sm up it is its own line below. */}
                        <span className="sm:hidden">
                            {' '}
                            Lo demás es opcional y lo decides tú. Más detalle en{' '}
                            <TextLink href={route('cookies')}>Cookies</TextLink> y{' '}
                            <TextLink href={route('privacy')}>Privacidad</TextLink>.
                        </span>
                    </p>
                </div>

                <p className="mt-3 hidden text-sm leading-relaxed text-ink-muted sm:block">
                    Lo demás es opcional y lo decides tú. Más detalle en{' '}
                    <TextLink href={route('cookies')}>Cookies</TextLink> y{' '}
                    <TextLink href={route('privacy')}>Privacidad</TextLink>.
                </p>

                {showSettings && (
                    <div className="mt-4 space-y-3 border-t border-line pt-4">
                        <p className="text-sm font-medium text-ink">Opcional</p>

                        <CheckboxField
                            id="cookie-analytics"
                            label="Habilita la recolección de datos para análisis de uso"
                            checked={analytics}
                            onChange={setAnalytics}
                        />

                        <CheckboxField
                            id="cookie-recording"
                            label="Habilita la recolección de datos para grabaciones de uso"
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

                {/* Two equal buttons side by side on a phone, the third under
                    them: stacked full-width, the three took half the screen. */}
                <div className="mt-3 grid grid-cols-2 gap-2 sm:mt-5 sm:flex sm:flex-wrap sm:items-center sm:gap-2.5">
                    {showSettings ? (
                        <>
                            <Button variant="secondary" onClick={() => decide({ analytics, recording, marketing })}>
                                Guardar
                            </Button>
                            <Button variant="ghost" onClick={dismissSettings}>
                                {getConsent() === null ? 'Volver' : 'Cerrar'}
                            </Button>
                        </>
                    ) : (
                        <>
                            <Button
                                variant="secondary"
                                onClick={() => decide({ analytics: false, recording: false, marketing: false })}
                            >
                                Rechazar
                            </Button>
                            <Button
                                variant="secondary"
                                onClick={() => decide({ analytics: true, recording: true, marketing: true })}
                            >
                                Aceptar todo
                            </Button>
                            {/* On a phone this lives next to the title instead. */}
                            <div className="hidden sm:block">
                                <Button variant="ghost" onClick={() => setShowSettings(true)}>
                                    Configurar
                                </Button>
                            </div>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
