import { Link, router } from '@inertiajs/react';
import ImmersionLayout from '../../Layouts/ImmersionLayout';
import TimelineEventRow from '../../components/game-master/TimelineEventRow';
import usePoll from '../../hooks/usePoll';

function post(url) {
    router.post(url, {}, { preserveScroll: true });
}

export default function Game({ game }) {
    usePoll(['game'], { interval: 5000, enabled: game.status === 'running' });

    return (
        <ImmersionLayout
            title={game.name}
            headerActions={
                <Link
                    href={route('immersion.gm.dashboard')}
                    className="border-2 border-paper px-3 py-1 text-xs uppercase tracking-wide hover:bg-paper hover:text-ink"
                >
                    &larr; Partidas
                </Link>
            }
        >
            <div className="mb-6 flex flex-wrap items-center justify-between gap-4 border-2 border-ink bg-paper-card p-4">
                <div>
                    <p className="immersion-stamp text-xs uppercase tracking-[0.2em] text-muted">{game.status}</p>
                    <h2 className="text-lg font-bold">{game.name}</h2>
                    {game.started_at && (
                        <p className="mt-1 text-sm">Reloj de la partida: {game.elapsed_minutes} min</p>
                    )}
                </div>

                <div className="flex flex-wrap gap-2">
                    {game.status === 'draft' && (
                        <button
                            onClick={() => post(route('immersion.gm.game.start', game.id))}
                            className="border-2 border-ink bg-ink px-3 py-2 text-xs font-bold uppercase text-paper"
                        >
                            Iniciar caso
                        </button>
                    )}

                    {game.status === 'running' && (
                        <button
                            onClick={() => post(route('immersion.gm.game.pause', game.id))}
                            className="border-2 border-ink px-3 py-2 text-xs font-bold uppercase"
                        >
                            Pausar
                        </button>
                    )}

                    {game.status === 'paused' && (
                        <button
                            onClick={() => post(route('immersion.gm.game.resume', game.id))}
                            className="border-2 border-ink px-3 py-2 text-xs font-bold uppercase"
                        >
                            Reanudar
                        </button>
                    )}

                    {(game.status === 'running' || game.status === 'paused') && (
                        <button
                            onClick={() => post(route('immersion.gm.game.force-next', game.id))}
                            className="border-2 border-ink px-3 py-2 text-xs font-bold uppercase"
                        >
                            Forzar siguiente evento
                        </button>
                    )}

                    <Link
                        href={route('immersion.gm.game.results', game.id)}
                        className="border-2 border-ink px-3 py-2 text-xs font-bold uppercase"
                    >
                        Ver acusaciones
                    </Link>

                    <button
                        onClick={() => post(route('immersion.gm.game.toggle-interrogation', game.id))}
                        className="border-2 border-ink px-3 py-2 text-xs font-bold uppercase"
                    >
                        {game.interrogation_enabled ? 'Deshabilitar' : 'Habilitar'} interrogatorio (Mec. 7)
                    </button>

                    <Link
                        href={route('immersion.gm.game.interrogations', game.id)}
                        className="border-2 border-ink px-3 py-2 text-xs font-bold uppercase"
                    >
                        Ver interrogatorios
                    </Link>
                </div>
            </div>

            <div className="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
                <section className="border-2 border-ink bg-paper-card p-4">
                    <h3 className="immersion-stamp text-xs uppercase tracking-[0.2em] text-muted">Línea de tiempo</h3>

                    {game.timeline_events.length === 0 && (
                        <div className="mt-3 border-2 border-dashed border-red-800 bg-red-50 p-4 text-sm text-red-900">
                            <p className="font-bold">Esta partida no tiene linea de tiempo cargada.</p>
                            <p className="mt-1">
                                Esto pasa con partidas creadas antes de este arreglo. &quot;Forzar siguiente evento&quot; no hara nada hasta que cargues la linea de tiempo.
                            </p>
                            <button
                                onClick={() => post(route('immersion.gm.game.load-default-timeline', game.id))}
                                className="mt-3 border-2 border-red-900 bg-red-900 px-3 py-2 text-xs font-bold uppercase text-white"
                            >
                                Cargar linea de tiempo por defecto
                            </button>
                        </div>
                    )}

                    <ul className="mt-3 space-y-2">
                        {game.timeline_events.map((event) => (
                            <TimelineEventRow key={event.id} game={game} event={event} />
                        ))}
                    </ul>
                </section>

                <section className="border-2 border-ink bg-paper-card p-4">
                    <h3 className="immersion-stamp text-xs uppercase tracking-[0.2em] text-muted">Jugadores</h3>
                    <ul className="mt-3 space-y-3 text-sm">
                        {game.players.map((player) => (
                            <li key={player.id} className="border-b border-dashed border-border-soft pb-2">
                                <p className="font-bold">{player.name}</p>
                                <p className="text-xs text-muted">{player.email}</p>
                                <p className="mt-1 break-all text-xs">
                                    {route('immersion.player.inbox', player.access_token)}
                                </p>
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </ImmersionLayout>
    );
}
