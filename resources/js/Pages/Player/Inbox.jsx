import { Head, Link } from '@inertiajs/react';
import ImmersionLayout from '../../Layouts/ImmersionLayout';
import InboxItem from '../../components/player/InboxItem';
import usePoll from '../../hooks/usePoll';

export default function Inbox({ player, game, items, case: mysteryCase }) {
    usePoll(['items'], { interval: 15000 });

    return (
        <ImmersionLayout
            title={`Bandeja de ${player.name}`}
            headerActions={
                <>
                    {game.interrogation_enabled && (
                        <Link
                            href={route('immersion.player.interrogation.index', player.access_token)}
                            className="border-2 border-paper px-3 py-1 text-xs uppercase tracking-wide hover:bg-paper hover:text-ink"
                        >
                            Interrogatorio
                        </Link>
                    )}
                    <Link
                        href={route('immersion.player.accusation', player.access_token)}
                        className="border-2 border-paper px-3 py-1 text-xs uppercase tracking-wide hover:bg-paper hover:text-ink"
                    >
                        Formulario de acusación
                    </Link>
                </>
            }
        >
            <Head title={`Bandeja de ${player.name}`} />

            <p className="mb-6 text-sm text-muted">
                Hola {player.name}. Estos son los mensajes que has recibido sobre el caso {mysteryCase.code}.
            </p>

            {items.length === 0 && (
                <p className="border-2 border-dashed border-border-soft p-6 text-center text-sm text-muted">
                    Todavía no ha llegado ningún correo. El Game Master iniciará el caso pronto.
                </p>
            )}

            <div className="space-y-6">
                {items.map((item) => (
                    <InboxItem key={item.event.id} item={item} playerToken={player.access_token} />
                ))}
            </div>
        </ImmersionLayout>
    );
}
