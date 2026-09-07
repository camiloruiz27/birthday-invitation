import { Link } from '@inertiajs/react';
import Badge, { GAME_STATUS_TONE } from '../ui/Badge';
import { formatDateTime } from '../../lib/format';

export const STATUS_LABELS = {
    draft: 'Sin iniciar',
    running: 'En curso',
    paused: 'Pausada',
    finished: 'Terminada',
};

/**
 * One game in a listing. Shared by the dashboard and the games section so a
 * game never reads two different ways depending on where you saw it.
 */
export default function GameRow({ game, className = '' }) {
    return (
        <Link
            href={route('immersion.gm.game.show', game.id)}
            className={`block min-w-0 rounded-card border border-line bg-surface-raised p-4 transition-colors hover:border-line-strong hover:bg-surface-overlay ${className}`}
        >
            <div className="flex flex-wrap items-center justify-between gap-3">
                <span className="min-w-0 truncate font-medium text-ink">{game.name}</span>
                <Badge tone={GAME_STATUS_TONE[game.status] || 'neutral'}>
                    {STATUS_LABELS[game.status] || game.status}
                </Badge>
            </div>

            <div className="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-muted">
                <span>
                    {game.players_count} {game.players_count === 1 ? 'jugador' : 'jugadores'}
                </span>

                {game.started_at ? (
                    <>
                        <span aria-hidden="true">·</span>
                        <span className="tabular">{game.elapsed_minutes ?? 0} min de partida</span>
                        <span aria-hidden="true">·</span>
                        <span>Iniciada {formatDateTime(game.started_at)}</span>
                    </>
                ) : (
                    <>
                        <span aria-hidden="true">·</span>
                        <span>Lista para iniciar</span>
                    </>
                )}
            </div>
        </Link>
    );
}
