import { useState } from 'react';
import { router } from '@inertiajs/react';
import Button from '../ui/Button';
import useAxiosState from '../../hooks/useAxiosState';

/**
 * Regenerates a timeline event's missing audio and resends its email.
 *
 * The call goes through the AI gateway's text-to-speech, which can take most
 * of a minute, so the button says so while it waits rather than looking stuck.
 */
export default function RetryAudioButton({ game, event }) {
    const { loading, run } = useAxiosState();
    const [message, setMessage] = useState(null);
    const [failed, setFailed] = useState(false);

    async function retry() {
        setMessage(null);
        setFailed(false);

        try {
            const result = await run({
                method: 'post',
                url: route('immersion.gm.game.event.retry-audio', [game.id, event.id]),
            });

            // The endpoint answers 200 with status:error when the gateway is
            // reachable but could not synthesise — that is still a failure to
            // report, not a success.
            setFailed(result.status === 'error');
            setMessage(result.message);
            router.reload({ only: ['game'] });
        } catch (error) {
            setFailed(true);
            setMessage(
                error.response?.data?.message ||
                    'No se pudo generar el audio. Revisa el servicio de IA e intenta de nuevo.'
            );
        }
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
                {loading ? 'Generando audio…' : 'Reintentar audio'}
            </Button>

            {loading && (
                <p className="text-xs text-ink-subtle">Puede tardar hasta un minuto.</p>
            )}

            {message && (
                <p
                    role="status"
                    className={`max-w-xs text-xs sm:text-right ${
                        failed ? 'text-danger' : 'text-ink-muted'
                    }`}
                >
                    {message}
                </p>
            )}
        </div>
    );
}
