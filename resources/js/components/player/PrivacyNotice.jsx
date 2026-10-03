import { useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';
import Button from '../ui/Button';

/**
 * The first thing a player sees: how their data is handled, and one button to
 * say they understood.
 *
 * Players have no account, so there is no registration checkbox to carry the
 * authorization Ley 1581 de 2012 asks for (prior, express, informed). This is
 * that checkbox: the page behind it is dimmed and unusable until the player
 * answers, and the answer is recorded with a timestamp and the policy
 * version (see PrivacyNoticeController).
 *
 * Sits at z-30: above the player's header and bottom bar (z-20) but below the
 * cookie banner (z-50), which has to stay reachable.
 */
export default function PrivacyNotice({ player }) {
    const dialogRef = useRef(null);

    useEffect(() => {
        dialogRef.current?.focus();
    }, []);

    function accept() {
        router.post(
            route('immersion.player.privacy.accept', player.access_token),
            {},
            { preserveScroll: true, preserveState: true }
        );
    }

    return (
        <div className="fixed inset-0 z-30 flex items-center justify-center bg-surface-sunken/90 px-4 py-6 backdrop-blur-sm">
            <div
                ref={dialogRef}
                role="dialog"
                aria-modal="true"
                aria-labelledby="player-privacy-title"
                tabIndex={-1}
                // See CookieConsent: the global focus ring beats outline-none.
                style={{ outline: 'none' }}
                className="max-h-full w-full max-w-lg overflow-y-auto rounded-card border border-line-strong bg-surface-raised p-6 shadow-overlay"
            >
                <h2 id="player-privacy-title" className="font-display text-xl font-semibold text-ink">
                    Antes de empezar
                </h2>

                <div className="mt-3 space-y-3 text-sm leading-relaxed text-ink-muted">
                    <p>
                        {player.name ? `${player.name}, tu` : 'Tu'} Game Master te inscribió en esta
                        partida de MisterioCode con tu nombre y tu correo electrónico.
                    </p>
                    <p>
                        Usaremos esos datos, y lo que escribas durante la partida (preguntas a los
                        sospechosos y tu acusación), solo para hacerla funcionar: entregarte tu enlace
                        y los correos del caso y generar con inteligencia artificial las respuestas
                        de los sospechosos. Para eso enviamos ese texto, sin tu correo, a un servicio
                        de IA. No vendemos tus datos.
                    </p>
                    <p>
                        Tienes derecho a conocer, corregir y pedir que eliminemos tus datos, y a
                        revocar esta autorización, escribiendo al contacto de la{' '}
                        <a
                            href={route('privacy')}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="font-medium text-accent underline underline-offset-2 hover:text-accent-strong"
                        >
                            Política de Privacidad
                        </a>
                        . La plataforma es para mayores de 18 años.
                    </p>
                </div>

                <Button onClick={accept} fullWidth className="mt-6">
                    Entiendo y autorizo el tratamiento de mis datos
                </Button>

                <p className="mt-3 text-center text-xs text-ink-subtle">
                    Si no estás de acuerdo, cierra esta página y avísale a tu Game Master.
                </p>
            </div>
        </div>
    );
}
