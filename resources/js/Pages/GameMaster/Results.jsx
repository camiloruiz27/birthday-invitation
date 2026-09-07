import { Link } from '@inertiajs/react';
import ImmersionLayout from '../../Layouts/ImmersionLayout';
import { formatDateTimeShort } from '../../lib/format';

export default function Results({ game }) {
    return (
        <ImmersionLayout
            title="Acusaciones registradas"
            headerActions={
                <Link
                    href={route('immersion.gm.game.show', game.id)}
                    className="border-2 border-paper px-3 py-1 text-xs uppercase tracking-wide hover:bg-paper hover:text-ink"
                >
                    &larr; {game.name}
                </Link>
            }
        >
            <h2 className="immersion-stamp text-sm uppercase tracking-[0.2em] text-muted">Acusaciones registradas</h2>

            <div className="mt-4 overflow-x-auto border-2 border-ink bg-paper-card">
                <table className="w-full min-w-[640px] text-left text-sm">
                    <thead>
                        <tr className="border-b-2 border-ink text-xs uppercase">
                            <th className="px-3 py-2">Jugador</th>
                            <th className="px-3 py-2">Sospechoso</th>
                            <th className="px-3 py-2">Motivo</th>
                            <th className="px-3 py-2">Arma</th>
                            <th className="px-3 py-2">Enviada</th>
                        </tr>
                    </thead>
                    <tbody>
                        {game.players.length === 0 && (
                            <tr>
                                <td className="px-3 py-2" colSpan={5}>No hay jugadores en esta partida.</td>
                            </tr>
                        )}
                        {game.players.map((player) => (
                            <tr key={player.id} className="border-b border-dashed border-border-soft align-top">
                                <td className="px-3 py-2 font-bold">{player.name}</td>
                                {player.accusation ? (
                                    <>
                                        <td className="px-3 py-2">{player.accusation.suspect_name}</td>
                                        <td className="px-3 py-2">{player.accusation.motive}</td>
                                        <td className="px-3 py-2">{player.accusation.weapon}</td>
                                        <td className="px-3 py-2">{formatDateTimeShort(player.accusation.submitted_at)}</td>
                                    </>
                                ) : (
                                    <td className="px-3 py-2 italic text-muted" colSpan={4}>Sin acusacion todavia.</td>
                                )}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </ImmersionLayout>
    );
}
