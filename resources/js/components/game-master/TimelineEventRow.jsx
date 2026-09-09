import Badge from '../ui/Badge';
import RetryAudioButton from './RetryAudioButton';

const TYPE_LABELS = {
    email: 'Correo',
    audio_email: 'Audio',
    unlock: 'Desbloqueo',
};

const DELIVERY_LABELS = {
    all: 'Todos',
    random_player: 'Un jugador al azar',
    role_slug: 'Por rol',
};

export default function TimelineEventRow({ game, event }) {
    // The email went out but there is no recording attached, so the Game
    // Master can ask for it again. `pending` means one is already queued.
    const missingAudio =
        event.type === 'audio_email' && event.sent_at && !event.audio_path;
    const sent = Boolean(event.sent_at);

    return (
        <li className="flex flex-col gap-3 border-b border-line py-3 last:border-0 sm:flex-row sm:items-start sm:justify-between">
            <div className="min-w-0">
                <div className="flex items-baseline gap-2">
                    <span className="tabular shrink-0 text-sm font-semibold text-accent">
                        min {event.trigger_offset_minutes}
                    </span>
                    <span className="text-sm text-ink">{event.title}</span>
                </div>

                <div className="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-muted">
                    <span>{TYPE_LABELS[event.type] || event.type}</span>
                    <span aria-hidden="true">·</span>
                    <span>{DELIVERY_LABELS[event.delivery_mode] || event.delivery_mode}</span>

                    {event.delivery_mode === 'random_player' && event.delivered_to_player && (
                        <>
                            <span aria-hidden="true">·</span>
                            <span className="text-ink">{event.delivered_to_player.name}</span>
                        </>
                    )}
                </div>
            </div>

            <div className="flex shrink-0 flex-wrap items-center gap-2">
                {missingAudio && <RetryAudioButton game={game} event={event} />}
                <Badge tone={sent ? 'success' : 'neutral'}>
                    {sent ? 'Enviado' : 'Pendiente'}
                </Badge>
            </div>
        </li>
    );
}
