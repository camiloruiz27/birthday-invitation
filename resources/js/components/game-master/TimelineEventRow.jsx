import RetryAudioButton from './RetryAudioButton';

export default function TimelineEventRow({ game, event }) {
    const needsAudioRetry = event.type === 'audio_email' && event.sent_at && !event.audio_path;

    return (
        <li className="flex items-start justify-between gap-3 border-b border-dashed border-border-soft py-2 text-sm">
            <div>
                <span className="font-bold">Min {event.trigger_offset_minutes}</span>
                {' — '}
                {event.title}
                <span className="ml-2 text-xs uppercase text-muted">
                    ({event.type} / {event.delivery_mode})
                </span>
                {event.delivery_mode === 'random_player' && event.delivered_to_player && (
                    <span className="ml-2 text-xs italic">&rarr; {event.delivered_to_player.name}</span>
                )}
            </div>
            <div className="flex shrink-0 items-center gap-2">
                {needsAudioRetry && <RetryAudioButton game={game} event={event} />}
                <span className="text-xs font-bold uppercase">
                    {event.sent_at ? 'Enviado' : 'Pendiente'}
                </span>
            </div>
        </li>
    );
}
