import { Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Badge, { GAME_STATUS_TONE } from '../../components/ui/Badge';
import Alert from '../../components/ui/Alert';
import EmptyState from '../../components/ui/EmptyState';
import { STATUS_LABELS } from '../../components/app/GameRow';
import { formatDateTime, formatDuration } from '../../lib/format';

const MODE_LABELS = {
    gm_led: 'Dirigida',
    automatic: 'Automática',
};

const SOURCE_LABELS = {
    purchase: 'Compra',
    grant: 'Otorgado',
    promo: 'Promoción',
};

function Metric({ label, value, hint, tone = 'default' }) {
    return (
        <div className="rounded-card border border-line bg-surface-raised p-4">
            <p
                className={`tabular text-2xl font-semibold ${
                    tone === 'alert' && value > 0 ? 'text-danger-strong' : 'text-ink'
                }`}
            >
                {value}
            </p>
            <p className="mt-0.5 text-sm text-ink-muted">{label}</p>
            {hint && <p className="mt-1 text-xs text-ink-subtle">{hint}</p>}
        </div>
    );
}

function Breakdown({ counts, labels }) {
    const entries = Object.entries(counts);

    if (entries.length === 0) {
        return <p className="text-sm text-ink-subtle">Sin datos todavía.</p>;
    }

    const total = entries.reduce((sum, [, value]) => sum + value, 0);

    return (
        <ul className="space-y-2.5">
            {entries.map(([key, value]) => (
                <li key={key}>
                    <div className="flex items-baseline justify-between gap-3 text-sm">
                        <span className="text-ink-muted">{labels?.[key] || key}</span>
                        <span className="tabular text-ink">{value}</span>
                    </div>
                    <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-surface-sunken">
                        <div
                            className="h-full rounded-full bg-accent"
                            style={{ width: `${(value / total) * 100}%` }}
                        />
                    </div>
                </li>
            ))}
        </ul>
    );
}

export default function AdminDashboard({ metrics }) {
    const { people, catalog, usage, ai, health, topCases, recentGames } = metrics;
    const needsAttention =
        health.audio_failed + health.orphan_games + health.unplayable_cases;

    return (
        <AppLayout current="admin.dashboard" kicker="Administración" title="Métricas de la plataforma">
            <Head title="Administración" />

            {needsAttention > 0 && (
                <Alert variant="warning" title="Hay cosas que requieren atención">
                    <ul className="mt-1 list-inside list-disc">
                        {health.audio_failed > 0 && (
                            <li>
                                {health.audio_failed} evento(s) de audio sin grabación. El
                                Game Master de cada partida puede reintentarlos.
                            </li>
                        )}
                        {health.orphan_games > 0 && (
                            <li>
                                {health.orphan_games} partida(s) sin dueño, inaccesibles por
                                web. Se asignan con <code>platform:claim-games</code>.
                            </li>
                        )}
                        {health.unplayable_cases > 0 && (
                            <li>
                                {health.unplayable_cases} caso(s) del catálogo sin su contenido
                                instalado en el servidor.
                            </li>
                        )}
                    </ul>
                </Alert>
            )}

            <div className="space-y-8">
                <section>
                    <h2 className="mb-3 text-base font-semibold text-ink">Personas</h2>
                    <div className="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        <Metric
                            label="Cuentas registradas"
                            value={people.users}
                            hint={`+${people.users_recent} en 30 días`}
                        />
                        <Metric
                            label="Game Masters"
                            value={people.game_masters}
                            hint="Cuentas con al menos una partida"
                        />
                        <Metric
                            label="Jugadores invitados"
                            value={people.players}
                            hint={`${people.players_unique} correos distintos`}
                        />
                        <Metric
                            label="Jugadores nuevos"
                            value={people.players_recent}
                            hint="Últimos 30 días"
                        />
                    </div>
                    <p className="mt-3 text-xs text-ink-subtle">
                        Un jugador no tiene cuenta: es alguien a quien un Game Master invitó por
                        correo. La misma persona invitada a tres partidas cuenta como tres.
                    </p>
                </section>

                <section>
                    <h2 className="mb-3 text-base font-semibold text-ink">Uso</h2>
                    <div className="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        <Metric label="Partidas creadas" value={usage.games} />
                        <Metric
                            label="Duración media"
                            value={
                                usage.average_minutes === null
                                    ? '—'
                                    : formatDuration(usage.average_minutes)
                            }
                            hint="Solo partidas terminadas"
                        />
                        <Metric label="Jugadores por partida" value={usage.average_players} />
                        <Metric
                            label="Tasa de acusación"
                            value={
                                usage.accusation_rate === null
                                    ? '—'
                                    : `${usage.accusation_rate}%`
                            }
                            hint="Llegan al final y acusan"
                        />
                    </div>

                    <div className="mt-4 grid gap-5 sm:grid-cols-2">
                        <Card as="article">
                            <CardHeader title="Partidas por estado" />
                            <Breakdown counts={usage.games_by_status} labels={STATUS_LABELS} />
                        </Card>

                        <Card as="article">
                            <CardHeader title="Partidas por modo" />
                            <Breakdown counts={usage.games_by_mode} labels={MODE_LABELS} />
                        </Card>
                    </div>
                </section>

                <section>
                    <h2 className="mb-3 text-base font-semibold text-ink">Catálogo</h2>
                    <div className="grid gap-5 lg:grid-cols-2">
                        <Card as="article">
                            <CardHeader
                                title="Casos más jugados"
                                description={`${catalog.cases_published} de ${catalog.cases} casos publicados.`}
                            />
                            {topCases.length === 0 ? (
                                <p className="text-sm text-ink-subtle">
                                    Todavía no se ha jugado ningún caso.
                                </p>
                            ) : (
                                <ul className="space-y-3">
                                    {topCases.map((item, index) => (
                                        <li
                                            key={item.slug}
                                            className="flex items-center justify-between gap-3"
                                        >
                                            <span className="flex min-w-0 items-center gap-3">
                                                <span
                                                    aria-hidden="true"
                                                    className="tabular w-5 shrink-0 text-sm text-ink-subtle"
                                                >
                                                    {index + 1}
                                                </span>
                                                <span className="truncate text-sm text-ink">
                                                    {item.name}
                                                </span>
                                            </span>
                                            <span className="tabular shrink-0 text-sm text-ink-muted">
                                                {item.games}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </Card>

                        <Card as="article">
                            <CardHeader
                                title="Accesos otorgados"
                                description={`${catalog.entitlements} en total.`}
                            />
                            <Breakdown
                                counts={catalog.entitlements_by_source}
                                labels={SOURCE_LABELS}
                            />
                            <p className="mt-4 text-xs text-ink-subtle">
                                No se muestran ingresos: la pasarela de pagos todavía no está
                                integrada, así que cualquier cifra sería ficticia.
                            </p>
                        </Card>
                    </div>
                </section>

                <section>
                    <h2 className="mb-3 text-base font-semibold text-ink">
                        Inteligencia artificial
                    </h2>
                    <div className="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        <Metric
                            label="Preguntas hechas"
                            value={ai.questions_asked}
                            hint="Una pregunta = una llamada"
                        />
                        <Metric label="Interrogatorios" value={ai.interrogation_sessions} />
                        <Metric
                            label="Audios generados"
                            value={`${ai.audio_generated}/${ai.audio_events}`}
                        />
                        <Metric
                            label="Audios fallidos"
                            value={health.audio_failed}
                            tone="alert"
                        />
                    </div>
                </section>

                <Card as="section">
                    <CardHeader
                        title="Actividad reciente"
                        description="Nombres y contadores. El contenido de una partida es de quien la juega."
                    />

                    {recentGames.length === 0 ? (
                        <EmptyState title="Todavía no hay partidas" />
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full min-w-140 text-left text-sm">
                                <thead>
                                    <tr className="border-b border-line text-xs uppercase tracking-wide text-ink-muted">
                                        <th scope="col" className="py-2 pr-4 font-medium">Partida</th>
                                        <th scope="col" className="py-2 pr-4 font-medium">Game Master</th>
                                        <th scope="col" className="py-2 pr-4 font-medium">Caso</th>
                                        <th scope="col" className="py-2 pr-4 font-medium">Jugadores</th>
                                        <th scope="col" className="py-2 pr-4 font-medium">Estado</th>
                                        <th scope="col" className="py-2 font-medium">Creada</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-line">
                                    {recentGames.map((game) => (
                                        <tr key={game.id}>
                                            <th scope="row" className="py-2.5 pr-4 font-medium text-ink">
                                                {game.name}
                                            </th>
                                            <td className="py-2.5 pr-4 text-ink-muted">
                                                {game.owner || <span className="text-ink-subtle">sin dueño</span>}
                                            </td>
                                            <td className="py-2.5 pr-4 text-ink-muted">{game.case_slug}</td>
                                            <td className="tabular py-2.5 pr-4 text-ink-muted">
                                                {game.players_count}
                                            </td>
                                            <td className="py-2.5 pr-4">
                                                <Badge tone={GAME_STATUS_TONE[game.status] || 'neutral'}>
                                                    {STATUS_LABELS[game.status] || game.status}
                                                </Badge>
                                            </td>
                                            <td className="whitespace-nowrap py-2.5 text-ink-muted">
                                                {formatDateTime(game.created_at)}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
