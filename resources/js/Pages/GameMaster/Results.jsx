import { Head } from '@inertiajs/react';
import GameMasterLayout from '../../Layouts/GameMasterLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import Badge from '../../components/ui/Badge';
import { formatDateTimeShort } from '../../lib/format';

export default function Results({ game }) {
    const submitted = game.players.filter((player) => player.accusation);

    return (
        <GameMasterLayout game={game} tab="results" title={game.name}>
            <Head title={`Acusaciones — ${game.name}`} />

            <Card as="section" padded={false}>
                <div className="p-5 sm:p-6">
                    <CardHeader
                        title="Acusaciones"
                        description={`${submitted.length} de ${game.players.length} jugador(es) han enviado la suya.`}
                        className="mb-0"
                    />
                </div>

                {game.players.length === 0 ? (
                    <div className="p-5 pt-0 sm:p-6 sm:pt-0">
                        <EmptyState title="Esta partida no tiene jugadores" />
                    </div>
                ) : (
                    <>
                        {/* Cards on a phone, table from sm up: an accusation has
                            four fields including a free-text motive, which does
                            not survive a 320px-wide table. */}
                        <ul className="divide-y divide-line border-t border-line sm:hidden">
                            {game.players.map((player) => (
                                <li key={player.id} className="p-4">
                                    <div className="flex items-center justify-between gap-3">
                                        <span className="font-medium text-ink">{player.name}</span>
                                        <Badge tone={player.accusation ? 'success' : 'neutral'}>
                                            {player.accusation ? 'Enviada' : 'Pendiente'}
                                        </Badge>
                                    </div>

                                    {player.accusation && (
                                        <dl className="mt-3 space-y-2 text-sm">
                                            <div>
                                                <dt className="text-xs text-ink-muted">Sospechoso</dt>
                                                <dd className="text-ink">{player.accusation.suspect_name}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-ink-muted">Arma o método</dt>
                                                <dd className="text-ink">{player.accusation.weapon}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-ink-muted">Motivo</dt>
                                                <dd className="text-ink">{player.accusation.motive}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-ink-muted">Enviada</dt>
                                                <dd className="text-ink-muted">
                                                    {formatDateTimeShort(player.accusation.submitted_at)}
                                                </dd>
                                            </div>
                                        </dl>
                                    )}
                                </li>
                            ))}
                        </ul>

                        <div className="hidden overflow-x-auto border-t border-line sm:block">
                            <table className="w-full min-w-180 text-left text-sm">
                                <thead>
                                    <tr className="border-b border-line text-xs uppercase tracking-wide text-ink-muted">
                                        <th scope="col" className="px-5 py-3 font-medium">Jugador</th>
                                        <th scope="col" className="px-5 py-3 font-medium">Sospechoso</th>
                                        <th scope="col" className="px-5 py-3 font-medium">Arma</th>
                                        <th scope="col" className="px-5 py-3 font-medium">Motivo</th>
                                        <th scope="col" className="px-5 py-3 font-medium">Enviada</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-line">
                                    {game.players.map((player) => (
                                        <tr key={player.id} className="align-top">
                                            <th scope="row" className="px-5 py-3 font-medium text-ink">
                                                {player.name}
                                            </th>
                                            {player.accusation ? (
                                                <>
                                                    <td className="px-5 py-3 text-ink">
                                                        {player.accusation.suspect_name}
                                                    </td>
                                                    <td className="px-5 py-3 text-ink">
                                                        {player.accusation.weapon}
                                                    </td>
                                                    <td className="max-w-sm px-5 py-3 text-ink-muted">
                                                        {player.accusation.motive}
                                                    </td>
                                                    <td className="whitespace-nowrap px-5 py-3 text-ink-muted">
                                                        {formatDateTimeShort(player.accusation.submitted_at)}
                                                    </td>
                                                </>
                                            ) : (
                                                <td className="px-5 py-3 text-ink-subtle" colSpan={4}>
                                                    Sin acusación todavía
                                                </td>
                                            )}
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
            </Card>
        </GameMasterLayout>
    );
}
