import { useState } from 'react';
import { router } from '@inertiajs/react';
import Button from '../ui/Button';
import { ConfirmModal } from '../ui/Modal';

/**
 * Deletes a game, which is how a slot is freed against the quota.
 *
 * Irreversible and it cuts off players mid-game, so the confirmation spells
 * out exactly what disappears rather than asking a vague "are you sure?".
 */
export default function DeleteGameButton({ game, size = 'md', variant = 'danger' }) {
    const [open, setOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const started = Boolean(game.started_at);

    function destroy() {
        setProcessing(true);
        router.delete(route('immersion.gm.game.destroy', game.id), {
            onFinish: () => {
                setProcessing(false);
                setOpen(false);
            },
        });
    }

    return (
        <>
            <Button variant={variant} size={size} onClick={() => setOpen(true)}>
                Eliminar
            </Button>

            <ConfirmModal
                open={open}
                onClose={() => setOpen(false)}
                onConfirm={destroy}
                processing={processing}
                title={`¿Eliminar "${game.name}"?`}
                description="Esto libera un cupo para crear otra partida. No se puede deshacer."
                confirmLabel="Eliminar partida"
            >
                <ul className="space-y-1.5 text-sm text-ink-muted">
                    <li>Los enlaces de sus jugadores dejarán de funcionar.</li>
                    <li>Se borran sus interrogatorios y acusaciones.</li>
                    {started && (
                        <li className="text-danger">
                            Esta partida ya está en marcha: quienes estén jugando la perderán.
                        </li>
                    )}
                </ul>
            </ConfirmModal>
        </>
    );
}
