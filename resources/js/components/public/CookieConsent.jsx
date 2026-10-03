import { useEffect, useRef, useState } from 'react';
import Button from '../ui/Button';
import TextLink from '../ui/TextLink';
import { CheckboxField } from '../ui/Field';
import { OPEN_SETTINGS_EVENT, getConsent, setConsent } from '../../lib/consent';

/**
 * The cookie consent banner.
 *
 * Three levels (see lib/consent.js): anonymous cookieless measurement that is
 * always on and only disclosed here, and two opt-in choices — audience
 * analytics (Google Analytics cookies) and session recordings (Microsoft
 * Clarity), each off until the visitor says yes. Ley 1581 de 2012 asks for
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
    const dialogRef = useRef(null);

    // Decide whether to show it only after mount: the choice lives in a
    // cookie that the server cannot see, and reading it during render would
    // mismatch whatever was rendered before.
    useEffect(() => {
        const consent = getConsent();

        if (consent === null) {
            setOpen(true);
        } else {
            setAnalytics(consent.analytics);
            setRecording(consent.recording);
        }

        const reopen = () => {
            const current = getConsent();

            setAnalytics(current?.analytics ?? false);
            setRecording(current?.recording ?? false);
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
                <h2 id="cookie-consent-title" className="font-display text-base font-semibold text-ink sm:text-lg">
                    Una cosa rápida sobre las cookies
                </h2>

                {/* Kept short and friendly on purpose: the detail lives in the
                    two policies linked here. It still says the two things that
                    must be said up front — we count visits anonymously, and
                    anything more needs a yes. */}
                <p className="mt-1.5 text-[13px] leading-relaxed text-ink-muted sm:mt-2 sm:text-sm">
                    Usamos cookies para que todo funcione y, si nos dejas, para entender cómo usas
                    MisterioCode y mejorarlo. Siempre contamos las visitas de forma anónima, sin
                    cookies. Tú decides lo demás, y puedes cambiarlo cuando quieras. Si quieres el
                    detalle, está en{' '}
                    <TextLink href={route('cookies')}>Cookies</TextLink> y{' '}
                    <TextLink href={route('privacy')}>Privacidad</TextLink>.
                </p>

                {showSettings && (
                    <div className="mt-5 space-y-4 border-t border-line pt-5">
                        <div>
                            <div className="flex items-center justify-between gap-4">
                                <p className="text-sm font-medium text-ink">Lo necesario</p>
                                <span className="text-xs font-medium text-ink-muted">
                                    Siempre activo
                                </span>
                            </div>
                            <p className="mt-1 text-xs text-ink-muted">
                                Para que el sitio funcione, verificación de tu sesión y la seguridad.
                            </p>
                        </div>

                        <CheckboxField
                            id="cookie-analytics"
                            label="Saber cómo nos usas"
                            checked={analytics}
                            onChange={setAnalytics}
                            hint="Saber que tanto disfrutas resolver un caso"
                        />

                        <CheckboxField
                            id="cookie-recording"
                            label="Ayudarnos a arreglar lo confuso"
                            checked={recording}
                            onChange={setRecording}
                            hint="Nos ayudas a mejorar con tu experiencia"
                        />
                    </div>
                )}

                {/* Two equal buttons side by side on a phone, the third under
                    them: stacked full-width, the three took half the screen. */}
                <div className="mt-4 grid grid-cols-2 gap-2 sm:mt-5 sm:flex sm:flex-wrap sm:items-center sm:gap-2.5">
                    {showSettings ? (
                        <>
                            <Button variant="secondary" onClick={() => decide({ analytics, recording })}>
                                Guardar
                            </Button>
                            <Button variant="ghost" onClick={dismissSettings}>
                                {getConsent() === null ? 'Volver' : 'Cerrar'}
                            </Button>
                        </>
                    ) : (
                        <>
                            <Button variant="secondary" onClick={() => decide({ analytics: false, recording: false })}>
                                Rechazar
                            </Button>
                            <Button variant="secondary" onClick={() => decide({ analytics: true, recording: true })}>
                                Aceptar todo
                            </Button>
                            <Button
                                variant="ghost"
                                onClick={() => setShowSettings(true)}
                                className="col-span-2 sm:col-span-1"
                            >
                                Configurar
                            </Button>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
