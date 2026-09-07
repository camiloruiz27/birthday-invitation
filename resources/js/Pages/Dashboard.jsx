import { Head, Link, usePage } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Card, { CardHeader } from '../components/ui/Card';
import Button from '../components/ui/Button';
import Alert from '../components/ui/Alert';
import EmptyState from '../components/ui/EmptyState';
import GameRow from '../components/app/GameRow';

function Stat({ label, value }) {
    return (
        <div className="rounded-card border border-line bg-surface-raised p-4">
            <p className="tabular text-2xl font-semibold text-ink">{value}</p>
            <p className="mt-0.5 text-sm text-ink-muted">{label}</p>
        </div>
    );
}

export default function Dashboard({ stats, activeGames, draftGames, library, canCreate }) {
    const { auth } = usePage().props;
    const firstName = auth.user.name.split(' ')[0];
    const hasLibrary = library.length > 0;

    return (
        <AppLayout
            current="dashboard"
            kicker="Central de operaciones"
            title={`Hola, ${firstName}`}
            actions={
                hasLibrary &&
                (canCreate ? (
                    <Button href={route('immersion.gm.games.create')}>Nueva partida</Button>
                ) : (
                    <Button disabled title="Todos tus casos llegaron a su máximo de partidas">
                        Nueva partida
                    </Button>
                ))
            }
        >
            <Head title="Panel" />

            {!hasLibrary ? (
                <EmptyState
                    title="Tu biblioteca está vacía"
                    description="Los casos que adquieras aparecerán aquí y podrás dirigir partidas de cada uno, las veces que quieras."
                    action={<Button href={route('cases.index')}>Ver los casos disponibles</Button>}
                />
            ) : (
                <div className="space-y-8">
                    <div className="grid grid-cols-3 gap-3 sm:gap-4">
                        <Stat label={stats.cases === 1 ? 'Caso' : 'Casos'} value={stats.cases} />
                        <Stat label={stats.games === 1 ? 'Partida' : 'Partidas'} value={stats.games} />
                        <Stat label="En curso" value={stats.running} />
                    </div>

                    {!canCreate && (
                        <Alert variant="warning" title="Todos tus casos llegaron a su máximo">
                            El cupo de partidas es por caso. Elimina una partida del caso que
                            quieras volver a jugar; en tu biblioteca ves el detalle de cada uno.
                        </Alert>
                    )}

                    {/* A running game is why you opened this page, so it goes
                        first and never gets folded into a generic list. */}
                    {activeGames.length > 0 && (
                        <section>
                            <h2 className="text-base font-semibold text-ink">Partidas en curso</h2>
                            <div className="mt-3 space-y-3">
                                {activeGames.map((game) => (
                                    <GameRow key={game.id} game={game} />
                                ))}
                            </div>
                        </section>
                    )}

                    {draftGames.length > 0 && (
                        <section>
                            <h2 className="text-base font-semibold text-ink">Listas para iniciar</h2>
                            <div className="mt-3 space-y-3">
                                {draftGames.map((game) => (
                                    <GameRow key={game.id} game={game} />
                                ))}
                            </div>
                        </section>
                    )}

                    {stats.games === 0 && (
                        <EmptyState
                            title="Todavía no has creado ninguna partida"
                            description="Elige un caso de tu biblioteca, agrega a tus jugadores y cada uno recibirá su propio enlace."
                            action={
                                canCreate && (
                                    <Button href={route('immersion.gm.games.create')}>
                                        Crear mi primera partida
                                    </Button>
                                )
                            }
                        />
                    )}

                    <Card as="section">
                        <CardHeader
                            title="Tu biblioteca"
                            description={`${library.length} ${library.length === 1 ? 'caso' : 'casos'} a tu nombre.`}
                            actions={
                                <Button href={route('library')} variant="secondary" size="sm">
                                    Ver biblioteca
                                </Button>
                            }
                        />

                        <ul className="space-y-3">
                            {library.map((item) => (
                                <li key={item.slug}>
                                    <Link
                                        href={route('cases.show', item.slug)}
                                        className="flex items-center gap-3 text-sm text-ink-muted hover:text-ink"
                                    >
                                        {item.cover_url && (
                                            <img
                                                src={item.cover_url}
                                                alt=""
                                                loading="lazy"
                                                className="h-10 w-10 shrink-0 rounded object-cover"
                                            />
                                        )}
                                        <span className="min-w-0 truncate">{item.name}</span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </Card>

                    {stats.games > activeGames.length + draftGames.length && (
                        <div>
                            <Button href={route('immersion.gm.games.index')} variant="secondary">
                                Ver todas las partidas
                            </Button>
                        </div>
                    )}
                </div>
            )}
        </AppLayout>
    );
}
