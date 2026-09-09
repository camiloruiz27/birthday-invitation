import { Head } from '@inertiajs/react';
import GameMasterLayout from '../../Layouts/GameMasterLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import Badge from '../../components/ui/Badge';
import Alert from '../../components/ui/Alert';
import { formatDateTimeShort } from '../../lib/format';

function Verdict({ correct }) {
    if (correct === null) {
        return <Badge tone="neutral">Sin acusar</Badge>;
    }

    return (
        <Badge tone={correct ? 'success' : 'danger'}>{correct ? 'Acertó' : 'Falló'}</Badge>
    );
}

export default function Results({ game, scoreboard, solution, reveal }) {
    const submitted = scoreboard.filter((row) => row.suspect_name);
    const correct = scoreboard.filter((row) => row.correct === true).length;

    return (
        // Reaching this page means the policy already allowed spoilers.
        <GameMasterLayout
            game={game}
            tab="results"
            title={game.name}
            can={{ viewSpoilers: true }}
        >
            <Head title={`Acusaciones — ${game.name}`} />

            {solution && (
                <Card as="section" className="mb-6">
                    <CardHeader
                        title="La solución"
                        description={solution.headline}
                        actions={
                            reveal.revealed_at ? (
                                <Badge tone="success">Revelada al equipo</Badge>
                            ) : (
                                <Badge tone="warning">Todavía no revelada</Badge>
                            )
                        }
                    />

                    <dl className="grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt className="text-xs uppercase tracking-wide text-ink-muted">Culpable</dt>
                            <dd className="mt-1 font-medium text-ink">{solution.culprit_name}</dd>
                        </div>
                        <div>
                            <dt className="text-xs uppercase tracking-wide text-ink-muted">Con qué</dt>
                            <dd className="mt-1 text-sm text-ink">{solution.weapon}</dd>
                        </div>
                        <div>
                            <dt className="text-xs uppercase tracking-wide text-ink-muted">Por qué</dt>
                            <dd className="mt-1 text-sm text-ink">{solution.motive}</dd>
                        </div>
                    </dl>

                    {!reveal.revealed_at && (
                        <Alert variant="warning" className="mt-5 mb-0">
                            Los jugadores todavía no ven esto. Se revela solo cuando acusen
                            todos, o puedes revelarlo desde el panel de la partida.
                        </Alert>
                    )}
                </Card>
            )}

            <Card as="section" padded={false}>
                <div className="p-5 sm:p-6">
                    <CardHeader
                        title="Acusaciones"
                        description={
                            solution
                                ? `${submitted.length} de ${scoreboard.length} enviadas · ${correct} acertaron.`
                                : `${submitted.length} de ${scoreboard.length} jugador(es) han enviado la suya.`
                        }
                        className="mb-0"
                    />
                </div>

                {scoreboard.length === 0 ? (
                    <div className="p-5 pt-0 sm:p-6 sm:pt-0">
                        <EmptyState title="Esta partida no tiene jugadores" />
                    </div>
                ) : (
                    <>
                        {/* Cards on a phone, table from sm up: an accusation has
                            four fields including a free-text motive, which does
                            not survive a 320px-wide table. */}
                        <ul className="divide-y divide-line border-t border-line sm:hidden">
                            {scoreboard.map((row) => (
                                <li key={row.player_id} className="p-4">
                                    <div className="flex items-center justify-between gap-3">
                                        <span className="font-medium text-ink">{row.player_name}</span>
                                        {solution ? (
                                            <Verdict correct={row.correct} />
                                        ) : (
                                            <Badge tone={row.suspect_name ? 'success' : 'neutral'}>
                                                {row.suspect_name ? 'Enviada' : 'Pendiente'}
                                            </Badge>
                                        )}
                                    </div>

                                    {row.suspect_name && (
                                        <dl className="mt-3 space-y-2 text-sm">
                                            <div>
                                                <dt className="text-xs text-ink-muted">Sospechoso</dt>
                                                <dd className="text-ink">{row.suspect_name}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-ink-muted">Arma o método</dt>
                                                <dd className="text-ink">{row.weapon}</dd>
                                            </div>
                                            <div>
                                                <dt className="text-xs text-ink-muted">Motivo</dt>
                                                <dd className="text-ink">{row.motive}</dd>
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
                                        <th scope="col" className="px-5 py-3 font-medium">
                                            {solution ? '¿Acertó?' : 'Estado'}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-line">
                                    {scoreboard.map((row) => (
                                        <tr key={row.player_id} className="align-top">
                                            <th scope="row" className="px-5 py-3 font-medium text-ink">
                                                {row.player_name}
                                            </th>
                                            {row.suspect_name ? (
                                                <>
                                                    <td className="px-5 py-3 text-ink">{row.suspect_name}</td>
                                                    <td className="px-5 py-3 text-ink">{row.weapon}</td>
                                                    <td className="max-w-sm px-5 py-3 text-ink-muted">
                                                        {row.motive}
                                                    </td>
                                                </>
                                            ) : (
                                                <td className="px-5 py-3 text-ink-subtle" colSpan={3}>
                                                    Sin acusación todavía
                                                </td>
                                            )}
                                            <td className="whitespace-nowrap px-5 py-3">
                                                {solution ? (
                                                    <Verdict correct={row.correct} />
                                                ) : (
                                                    <Badge tone={row.suspect_name ? 'success' : 'neutral'}>
                                                        {row.suspect_name ? 'Enviada' : 'Pendiente'}
                                                    </Badge>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </>
                )}
            </Card>

            {reveal.revealed_at && (
                <p className="mt-4 text-sm text-ink-muted">
                    Revelada {formatDateTimeShort(reveal.revealed_at)}
                    {reveal.by === 'auto'
                        ? ', automáticamente al acusar todos.'
                        : ', por ti desde el panel.'}
                </p>
            )}
        </GameMasterLayout>
    );
}
