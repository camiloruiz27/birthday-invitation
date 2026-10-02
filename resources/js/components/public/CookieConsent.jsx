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
    // happen, the panel being at the far end of the page.
    useEffect(() => {
        if (open) dialogRef.current?.focus();
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
            className="fixed inset-x-0 bottom-0 z-50 px-4 pb-[max(1rem,env(safe-area-inset-bottom))] outline-none sm:px-6"
        >
            <div className="mx-auto max-h-[85dvh] w-full max-w-3xl overflow-y-auto rounded-card border border-line-strong bg-surface-raised p-5 shadow-overlay sm:p-6">
                <h2 id="cookie-consent-title" className="font-display text-lg font-semibold text-ink">
                    Tu privacidad y las cookies
                </h2>

                <p className="mt-2 text-sm leading-relaxed text-ink-muted">
                    Usamos cookies necesarias para que la plataforma funcione. Además medimos de
                    forma anónima y sin cookies cuántas personas nos visitan y desde dónde llegan
                    (Google Analytics y Microsoft Clarity). Con tu permiso también usamos cookies
                    para medir con más detalle cuánto tiempo se usa el sitio y si la gente se va
                    rápido, y para grabar sesiones de uso que nos ayuden a mejorarlo. Puedes
                    cambiar de opinión cuando quieras. Más información en la{' '}
                    <TextLink href={route('cookies')}>Política de Cookies</TextLink> y la{' '}
                    <TextLink href={route('privacy')}>Política de Privacidad</TextLink>.
                </p>

                {showSettings && (
                    <div className="mt-5 space-y-4 border-t border-line pt-5">
                        <div>
                            <div className="flex items-center justify-between gap-4">
                                <p className="text-sm font-medium text-ink">Cookies necesarias</p>
                                <span className="text-xs font-medium text-ink-muted">
                                    Siempre activas
                                </span>
                            </div>
                            <p className="mt-1 text-xs text-ink-muted">
                                Sesión, protección contra falsificación de formularios, verificación
                                de seguridad (Cloudflare Turnstile) y el registro de esta elección.
                                Sin ellas el sitio no funciona.
                            </p>
                        </div>

                        <div>
                            <div className="flex items-center justify-between gap-4">
                                <p className="text-sm font-medium text-ink">Medición básica anónima</p>
                                <span className="text-xs font-medium text-ink-muted">Sin cookies</span>
                            </div>
                            <p className="mt-1 text-xs text-ink-muted">
                                Cuenta visitas y de qué sitio llegan, sin cookies y sin seguirte entre
                                páginas.
                            </p>
                        </div>

                        <CheckboxField
                            id="cookie-analytics"
                            label="Analítica de audiencia (cookies de Google Analytics)"
                            checked={analytics}
                            onChange={setAnalytics}
                            hint="Mide cuánto tiempo estás en el sitio, cuántas páginas ves y si te vas rápido. No se usa para publicidad."
                        />

                        <CheckboxField
                            id="cookie-recording"
                            label="Grabaciones y mapas de calor (cookies de Microsoft Clarity)"
                            checked={recording}
                            onChange={setRecording}
                            hint="Graba cómo se mueve el cursor y qué se toca en la pantalla para detectar partes confusas. Los textos de las pantallas del jugador se ocultan."
                        />
                    </div>
                )}

                <div className="mt-5 flex flex-col gap-2.5 sm:flex-row sm:flex-wrap sm:items-center">
                    {showSettings ? (
                        <>
                            <Button variant="secondary" onClick={() => decide({ analytics, recording })}>
                                Guardar preferencias
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
                            <Button variant="ghost" onClick={() => setShowSettings(true)}>
                                Configurar
                            </Button>
                        </>
                    )}
                </div>
            </div>
        </div>
    );
}
