import { useState } from 'react';
import { router } from '@inertiajs/react';
import useAxiosState from '../../hooks/useAxiosState';

export default function RetryAudioButton({ game, event }) {
    const { loading, run } = useAxiosState();
    const [message, setMessage] = useState(null);

    async function retry() {
        setMessage(null);
        try {
            const result = await run({
                method: 'post',
                url: route('immersion.gm.game.event.retry-audio', [game.id, event.id]),
            });
            setMessage(result.message);
            router.reload({ only: ['game'] });
        } catch (err) {
            setMessage(err.response?.data?.message || 'No se pudo generar el audio.');
        }
    }

    return (
        <div className="flex flex-col items-end gap-1">
            <button
                type="button"
                onClick={retry}
                disabled={loading}
                title="El correo salio sin el audio adjunto; genera el audio y reenvia el correo"
                className="border-2 border-ink px-2 py-1 text-xs font-bold uppercase disabled:cursor-wait disabled:opacity-60"
            >
                {loading ? 'Generando audio... (hasta 1 min)' : 'Reintentar audio'}
            </button>
            {message && <p className="max-w-xs text-right text-xs text-muted">{message}</p>}
        </div>
    );
}
