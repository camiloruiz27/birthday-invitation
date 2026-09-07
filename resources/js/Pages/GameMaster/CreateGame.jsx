import { Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import EmptyState from '../../components/ui/EmptyState';
import NewGameForm from '../../components/game-master/NewGameForm';

export default function CreateGame({ library }) {
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
            ) : (
                <Card>
                    <p className="mb-6 text-sm text-ink-muted">
                        Al crear la partida, cada jugador recibe su propio enlace de acceso.
                        Todavía no empieza nada: el caso arranca cuando tú lo inicies desde su
                        panel.
                    </p>

                    <NewGameForm library={library} />
                </Card>
            )}
        </AppLayout>
    );
}
