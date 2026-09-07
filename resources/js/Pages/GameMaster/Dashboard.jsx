import { Link, router } from '@inertiajs/react';
import ImmersionLayout from '../../Layouts/ImmersionLayout';
import Card from '../../components/ui/Card';
import NewGameForm from '../../components/game-master/NewGameForm';
import { formatDateTime } from '../../lib/format';

export default function Dashboard({ games }) {
    function logout() {
        router.post(route('immersion.gm.logout'));
    }

    return (
        <ImmersionLayout
            title="Panel del Game Master"
            headerActions={
                <button
                    onClick={logout}
                    className="border-2 border-paper px-3 py-1 text-xs uppercase tracking-wide hover:bg-paper hover:text-ink"
                >
                    Salir
                </button>
            }
        >
            <section className="mb-10">
                <h2 className="immersion-stamp text-sm uppercase tracking-[0.2em] text-muted">Partidas</h2>

                {games.length === 0 ? (
                    <p className="mt-3 text-sm text-muted">Todavía no hay ninguna partida creada.</p>
                ) : (
                    <div className="mt-3 space-y-3">
                        {games.map((game) => (
                            <Link
                                key={game.id}
                                href={route('immersion.gm.game.show', game.id)}
                                className="block border-2 border-ink bg-paper-card px-4 py-3 hover:bg-[#efe6ce]"
                            >
                                <div className="flex items-center justify-between">
                                    <span className="font-bold">{game.name}</span>
                                    <span className="text-xs uppercase tracking-wide">{game.status}</span>
                                </div>
                                {game.started_at && (
                                    <p className="mt-1 text-xs text-muted">Iniciada: {formatDateTime(game.started_at)}</p>
                                )}
                            </Link>
                        ))}
                    </div>
                )}
            </section>

            <Card>
                <h2 className="immersion-stamp text-sm uppercase tracking-[0.2em] text-muted">Nueva partida</h2>
                <NewGameForm />
            </Card>
        </ImmersionLayout>
    );
}
