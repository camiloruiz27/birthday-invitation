import { useState } from 'react';
import { router } from '@inertiajs/react';
import Button from '../ui/Button';

/**
 * "Ya leí toda la información": for tables with fast readers.
 *
 * Shown above the inbox while there is a next envelope that can be brought
 * forward. Each player confirms once they finished reading what has arrived;
 * when the whole table has, the next envelope comes without waiting for its
 * minute (see EarlyRelease). A new envelope turns everybody back to "not ready",
 * so the bar simply starts over.
 *
 * The count of who is ready follows the page's own reloads, so it can trail the
 * others by a few seconds; the envelope itself arrives the moment the last
 * player confirms.
 */
export default function ReadyBar({ player, readiness }) {
    const [sending, setSending] = useState(false);

    if (!readiness?.available) {
        return null;
    }

    const { confirmed, ready, total } = readiness;
    const waiting = Math.max(0, total - ready);

    function confirm() {
        router.post(
            route('immersion.player.envelope.ready', player.access_token),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onStart: () => setSending(true),
                onFinish: () => setSending(false),
            }
        );
    }

    return (
        <section
            data-tour="inbox-ready"
            aria-label="Confirmar lectura"
            className="mb-6 flex flex-col gap-3 rounded-card border border-line bg-surface-raised p-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div className="text-sm">
                {confirmed ? (
                    <>
                        <p className="font-semibold text-ink" aria-live="polite">
                            Listo, ya avisaste que terminaste de leer.
                        </p>
                        <p className="mt-0.5 text-ink-muted">
                            {waiting > 0 ? (
                                <>
                                    Esperando a que termine el resto del equipo (
                                    <span className="tabular">{ready}</span> de{' '}
                                    <span className="tabular">{total}</span>). Cuando todos terminen,
                                    llega el siguiente sobre sin esperar el reloj.
                                </>
                            ) : (
                                'Todo el equipo terminó. El siguiente sobre está por llegar.'
                            )}
                        </p>
                    </>
                ) : (
                    <>
                        <p className="font-semibold text-ink">¿Ya leíste todo lo que ha llegado?</p>
                        <p className="mt-0.5 text-ink-muted">
                            Si todo el equipo lo confirma (
                            <span className="tabular">{ready}</span> de{' '}
                            <span className="tabular">{total}</span> hasta ahora), el siguiente sobre llega
                            al instante, sin esperar el reloj.
                        </p>
                    </>
                )}
            </div>

            {!confirmed && (
                <Button onClick={confirm} loading={sending} className="shrink-0">
                    Ya leí toda la información
                </Button>
            )}
        </section>
    );
}
