import { Head } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import InboxItem from '../../components/player/InboxItem';
import usePoll from '../../hooks/usePoll';

export default function Inbox({ player, game, items, case: mysteryCase }) {
    // New envelopes arrive on the server's clock, so the inbox refreshes
    // itself while the player is reading.
    usePoll(['items'], { interval: 15000 });

    return (
        <PlayerLayout
            player={player}
            game={game}
            section="inbox"
            kicker={`Caso ${mysteryCase.code} · Confidencial`}
            title={`Bandeja de ${player.name}`}
        >
            <Head title={`Bandeja de ${player.name}`} />

            {items.length === 0 ? (
                <div className="border-2 border-dashed border-paper-line px-6 py-12 text-center">
                    <p className="case-stamp text-sm">Sin mensajes todavía</p>
                    <p className="mx-auto mt-2 max-w-sm text-sm text-paper-muted">
                        El expediente llegará por partes a medida que avance la
                        investigación. Deja esta página abierta: se actualiza sola.
                    </p>
                </div>
            ) : (
                <div className="space-y-6">
                    {items.map((item) => (
                        <InboxItem key={item.event.id} item={item} playerToken={player.access_token} />
                    ))}
                </div>
            )}
        </PlayerLayout>
    );
}
