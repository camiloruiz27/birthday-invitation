import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Button from '../../components/ui/Button';
import EmptyState from '../../components/ui/EmptyState';
import GameRow, { STATUS_LABELS } from '../../components/app/GameRow';

const FILTERS = [
    { key: 'all', label: 'Todas' },
    { key: 'active', label: 'En curso', statuses: ['running', 'paused'] },
    { key: 'draft', label: 'Sin iniciar', statuses: ['draft'] },
];

export default function Games({ games, hasLibrary }) {
    const [filter, setFilter] = useState('all');

    const active = FILTERS.find((item) => item.key === filter);
    const visible = active.statuses
        ? games.filter((game) => active.statuses.includes(game.status))
        : games;

    return (
        <AppLayout
            current="immersion.gm.games.index"
            kicker="Game Master"
            title="Partidas"
            actions={
                hasLibrary && <Button href={route('immersion.gm.games.create')}>Nueva partida</Button>
            }
        >
            <Head title="Partidas" />

            {games.length === 0 ? (
                <EmptyState
                    title="Todavía no has creado ninguna partida"
                    description={
                        hasLibrary
                            ? 'Elige un caso de tu biblioteca, agrega a tus jugadores y cada uno recibirá su propio enlace.'
                            : 'Necesitas un caso en tu biblioteca antes de poder crear partidas.'
                    }
                    action={
                        hasLibrary ? (
                            <Button href={route('immersion.gm.games.create')}>
                                Crear mi primera partida
                            </Button>
                        ) : (
                            <Button href={route('cases.index')}>Ver los casos disponibles</Button>
                        )
                    }
                />
            ) : (
                <>
                    <div
                        role="group"
                        aria-label="Filtrar partidas"
                        className="mb-5 flex flex-wrap gap-2"
                    >
                        {FILTERS.map((item) => {
                            const count = item.statuses
                                ? games.filter((game) => item.statuses.includes(game.status)).length
                                : games.length;

                            return (
                                <button
                                    key={item.key}
                                    type="button"
                                    onClick={() => setFilter(item.key)}
                                    aria-pressed={filter === item.key}
                                    className={`min-h-9 rounded-full border px-3.5 py-1.5 text-sm transition-colors ${
                                        filter === item.key
                                            ? 'border-accent bg-accent-dim text-accent-strong'
                                            : 'border-line text-ink-muted hover:text-ink'
                                    }`}
                                >
                                    {item.label}
                                    <span className="tabular ml-1.5 text-xs opacity-70">{count}</span>
                                </button>
                            );
                        })}
                    </div>

                    {visible.length === 0 ? (
                        <EmptyState
                            title={`No tienes partidas ${STATUS_LABELS[active.statuses[0]].toLowerCase()}`}
                            description="Cambia el filtro para ver el resto."
                        />
                    ) : (
                        <div className="space-y-3">
                            {visible.map((game) => (
                                <GameRow key={game.id} game={game} />
                            ))}
                        </div>
                    )}
                </>
            )}
        </AppLayout>
    );
}
