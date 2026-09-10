import { useState } from 'react';
import { router } from '@inertiajs/react';
import Button from '../ui/Button';
import Spinner from '../ui/Spinner';
import useAxiosState from '../../hooks/useAxiosState';

/**
 * Asks for a timeline event's missing audio to be generated.
 *
 * Generation happens on the queue, so this returns immediately and the event's
 * audio_status carries the outcome. The console is already polling a running
 * game; this nudges a reload so a paused or finished one updates too.
 */
export default function RetryAudioButton({ game, event }) {
    const { loading, run } = useAxiosState();
    const [message, setMessage] = useState(null);
    const [failed, setFailed] = useState(false);

    const queued = event.audio_status === 'pending';

    async function retry() {
        setMessage(null);
        setFailed(false);

        try {
            const result = await run({
                method: 'post',
                url: route('immersion.gm.game.event.retry-audio', [game.id, event.id]),
            });

            setMessage(result.message);
            router.reload({ only: ['game'] });
        } catch (error) {
            setFailed(true);
            setMessage(
                error.response?.data?.message ||
                    'No se pudo encolar la generación del audio. Intenta de nuevo.'
            );
        }
    }

    if (queued) {
        return (
            <span className="flex items-center gap-2 text-xs text-ink-muted">
                <Spinner size="sm" label={null} />
                Generando audio…
            </span>
        );
    }

    return (
        <div className="flex flex-col items-start gap-1.5 sm:items-end">
            <Button
                variant="secondary"
                size="sm"
                onClick={retry}
                loading={loading}
                title="El correo salió sin el audio adjunto: genera el audio y reenvía el correo"
            >
                Reintentar audio
            </Button>

            {message && (
                <p
                    role="status"
                    className={`max-w-xs text-xs sm:text-right ${
                        failed ? 'text-danger-strong' : 'text-ink-muted'
                    }`}
                >
                    {message}
                </p>
            )}
        </div>
    );
}
