import { Link } from '@inertiajs/react';
import AppLayout from './AppLayout';
import Badge, { GAME_STATUS_TONE } from '../components/ui/Badge';

const STATUS_LABELS = {
    draft: 'Sin iniciar',
    running: 'En curso',
    paused: 'Pausada',
    finished: 'Terminada',
};

function GameTab({ href, active, children }) {
    return (
        <Link
            href={href}
            aria-current={active ? 'page' : undefined}
            className={`-mb-px shrink-0 border-b-2 px-1 pb-2.5 text-sm transition-colors ${
                active
                    ? 'border-accent font-medium text-ink'
                    : 'border-transparent text-ink-muted hover:text-ink'
            }`}
        >
            {children}
        </Link>
    );
}

/**
 * The console for one game: everything AppLayout gives, plus a context bar
 * that keeps the run's identity, state and clock visible while the Game Master
 * moves between its tabs.
 *
 * `tab` picks the active tab; omit `game` for pages that are not scoped to a
 * single run (the games list).
 */
export default function GameMasterLayout({ game, tab, title, actions, children }) {
    return (
        <AppLayout
            kicker="Game Master"
            title={title || game?.name}
            actions={actions}
        >
            {game && (
                <div className="mb-6">
                    <div className="flex flex-wrap items-center gap-3">
                        <Badge tone={GAME_STATUS_TONE[game.status] || 'neutral'}>
                            {STATUS_LABELS[game.status] || game.status}
                        </Badge>

                        {game.started_at && (
                            <span className="tabular text-sm text-ink-muted">
                                Reloj: {game.elapsed_minutes} min
                            </span>
                        )}
                    </div>

                    {/* Horizontal scroll rather than wrapping: on a narrow
                        phone the tabs stay on one line and swipe. */}
                    <nav
                        aria-label="Secciones de la partida"
                        className="mt-4 flex gap-5 overflow-x-auto border-b border-line"
                    >
                        <GameTab
                            href={route('immersion.gm.game.show', game.id)}
                            active={tab === 'panel'}
                        >
                            Panel
                        </GameTab>
                        <GameTab
                            href={route('immersion.gm.game.interrogations', game.id)}
                            active={tab === 'interrogations'}
                        >
                            Interrogatorios
                        </GameTab>
                        <GameTab
                            href={route('immersion.gm.game.results', game.id)}
                            active={tab === 'results'}
                        >
                            Acusaciones
                        </GameTab>
                    </nav>
                </div>
            )}

            {children}
        </AppLayout>
    );
}
