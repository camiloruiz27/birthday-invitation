import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Badge, { GAME_STATUS_TONE } from '../../components/ui/Badge';
import EmptyState from '../../components/ui/EmptyState';
import NewGameForm from '../../components/game-master/NewGameForm';
import { formatDateTime } from '../../lib/format';

const STATUS_LABELS = {
    draft: 'Sin iniciar',
    running: 'En curso',
    paused: 'Pausada',
    finished: 'Terminada',
};

function GameRow({ game }) {
    return (
        <Link
            href={route('immersion.gm.game.show', game.id)}
            className="block rounded-card border border-line bg-surface-raised p-4 transition-colors hover:border-line-strong hover:bg-surface-overlay"
        >
            <div className="flex flex-wrap items-center justify-between gap-3">
                <span className="min-w-0 truncate font-medium text-ink">{game.name}</span>
                <Badge tone={GAME_STATUS_TONE[game.status] || 'neutral'}>
                    {STATUS_LABELS[game.status] || game.status}
                </Badge>
            </div>

            <p className="mt-1.5 text-xs text-ink-muted">
                {game.started_at
                    ? `Iniciada ${formatDateTime(game.started_at)}`
                    : 'Todavía no se ha iniciado'}
            </p>
        </Link>
    );
}

export default function Dashboard({ games, library }) {
    return (
        <AppLayout kicker="Game Master" title="Tus partidas">
            <Head title="Panel del Game Master" />

            <div className="space-y-8">
                <section>
                    {games.length === 0 ? (
                        <EmptyState
                            title="Todavía no has creado ninguna partida"
                            description={
                                library.length > 0
                                    ? 'Crea una abajo, invita a tus jugadores y dirige el caso.'
                                    : 'Cuando tengas un caso en tu biblioteca podrás crear tu primera partida.'
                            }
                        />
                    ) : (
                        <div className="space-y-3">
                            {games.map((game) => (
                                <GameRow key={game.id} game={game} />
                            ))}
                        </div>
                    )}
                </section>

                <Card as="section">
                    <CardHeader
                        title="Nueva partida"
                        description="Elige el caso, agrega a tus jugadores y cada uno recibirá su propio enlace."
                    />

                    {library.length === 0 ? (
                        <EmptyState
                            title="Tu biblioteca está vacía"
                            description="Necesitas al menos un caso para crear partidas. Los casos que adquieras aparecerán aquí."
                        />
                    ) : (
                        <NewGameForm library={library} />
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
