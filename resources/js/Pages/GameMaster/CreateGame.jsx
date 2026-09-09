import { Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import EmptyState from '../../components/ui/EmptyState';
import NewGameForm from '../../components/game-master/NewGameForm';

export default function CreateGame({ library, credits }) {
    // The quota is per case, so this page only blocks when every case is full;
    // otherwise the form lets the Game Master pick one that still has room.
    const allFull = library.length > 0 && library.every((item) => item.quota.full);

    return (
        <AppLayout
            current="immersion.gm.games.index"
            kicker="Nueva partida"
            title="Crear una partida"
            width="prose"
            actions={
                <Button href={route('immersion.gm.games.index')} variant="ghost">
                    Cancelar
                </Button>
            }
        >
            <Head title="Crear una partida" />

            {library.length === 0 ? (
                <EmptyState
                    title="Tu biblioteca está vacía"
                    description="Necesitas al menos un caso para crear partidas."
                    action={<Button href={route('cases.index')}>Ver los casos disponibles</Button>}
                />
            ) : allFull ? (
                // Told here rather than after filling in the whole form.
                <EmptyState
                    title="Todos tus casos llegaron a su máximo"
                    description={`Puedes tener hasta ${library[0].quota.limit} partidas de cada caso. Elimina una partida del caso que quieras volver a jugar.`}
                    action={
                        <Button href={route('immersion.gm.games.index')}>
                            Ver mis partidas
                        </Button>
                    }
                />
            ) : (
                <Card>
                    <p className="mb-6 text-sm text-ink-muted">
                        Al crear la partida, cada jugador recibe su propio enlace de acceso.
                        Todavía no empieza nada: el caso arranca cuando tú lo inicies desde su
                        panel.
                    </p>

                    <NewGameForm library={library} credits={credits} />
                </Card>
            )}
        </AppLayout>
    );
}
