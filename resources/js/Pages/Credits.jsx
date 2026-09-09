import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Card, { CardHeader } from '../components/ui/Card';
import Button from '../components/ui/Button';
import Badge from '../components/ui/Badge';
import Alert from '../components/ui/Alert';
import EmptyState from '../components/ui/EmptyState';

const ENDING_LABELS = {
    classic: 'Final clásico',
    epilogue: 'Epílogo personalizado',
    confession_audio: 'Audio de confesión',
};

/**
 * How a ledger entry reads to a person. The reason is the engine's vocabulary;
 * this is the only place it gets translated, so a new reason shows up as its
 * raw slug rather than silently rendering as blank.
 */
const REASONS = {
    grant: { label: 'Incluido con un caso', tone: 'success' },
    topup: { label: 'Recarga', tone: 'success' },
    reserve: { label: 'Reservado', tone: 'warning' },
    spend: { label: 'Consumo', tone: 'neutral' },
    release: { label: 'Devolución', tone: 'accent' },
    adjust: { label: 'Ajuste', tone: 'accent' },
};

function formatPrice(amount, currency) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency,
        maximumFractionDigits: 0,
    }).format(amount);
}

function Figure({ label, value, hint, emphasis = false }) {
    return (
        <div>
            <p className="text-xs uppercase tracking-widest text-ink-subtle">{label}</p>
            <p
                className={`mt-1 tabular font-semibold ${
                    emphasis ? 'text-3xl text-ink' : 'text-2xl text-ink-muted'
                }`}
            >
                {value}
            </p>
            {hint && <p className="mt-1 text-xs text-ink-subtle">{hint}</p>}
        </div>
    );
}

function PackageCard({ pack, canPurchase, simulated }) {
    const { post, processing } = useForm({ package: pack.id });

    function purchase() {
        post(route('credits.purchase'), canPurchase ? {} : { preserveScroll: true });
    }

    return (
        <Card
            as="article"
            className={`flex flex-col ${pack.highlight ? 'border-accent' : ''}`}
        >
            <div className="flex flex-wrap items-start justify-between gap-2">
                <h3 className="font-semibold text-ink">{pack.name}</h3>
                {pack.highlight && <Badge tone="accent">Más elegido</Badge>}
            </div>

            <p className="mt-3 tabular text-2xl font-semibold text-ink">
                {pack.credits}
                <span className="ml-1.5 text-sm font-normal text-ink-muted">créditos</span>
            </p>

            <p className="mt-1 text-sm text-ink-muted">
                {formatPrice(pack.price_amount, pack.currency)}
            </p>

            <p className="mt-3 flex-1 text-sm text-ink-muted">{pack.summary}</p>

            <div className="mt-5">
                {canPurchase ? (
                    <Button onClick={purchase} loading={processing} size="sm" className="w-full">
                        {processing ? 'Redirigiendo…' : 'Pagar con tarjeta'}
                    </Button>
                ) : simulated ? (
                    <Button onClick={purchase} loading={processing} size="sm" className="w-full">
                        Recargar (simulado)
                    </Button>
                ) : (
                    <Button size="sm" className="w-full" disabled title="Pagos no disponibles todavía">
                        Próximamente
                    </Button>
                )}
            </div>
        </Card>
    );
}

function LedgerRow({ entry }) {
    const reason = REASONS[entry.reason] || { label: entry.reason, tone: 'neutral' };
    const moved = entry.delta !== 0;

    return (
        <li className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 py-3">
            <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                    <Badge tone={reason.tone}>{reason.label}</Badge>
                    {entry.game && (
                        <span className="truncate text-sm text-ink-muted">{entry.game}</span>
                    )}
                </div>
                {entry.note && (
                    <p className="mt-1 text-xs text-ink-subtle">{entry.note}</p>
                )}
            </div>

            <div className="text-right">
                <p
                    className={`tabular text-sm font-semibold ${
                        entry.delta > 0
                            ? 'text-success'
                            : entry.delta < 0
                              ? 'text-danger'
                              : 'text-ink-subtle'
                    }`}
                >
                    {moved ? `${entry.delta > 0 ? '+' : ''}${entry.delta}` : '—'}
                </p>
                <p className="tabular text-xs text-ink-subtle">
                    {entry.balance_after} disp.
                </p>
            </div>
        </li>
    );
}

export default function Credits({ wallet, costs, packages, simulated, canPurchase, ledger, holds }) {
    return (
        <AppLayout
            current="credits"
            kicker="Inteligencia artificial"
            title="Créditos"
            actions={
                <Button href={route('immersion.gm.games.create')} variant="secondary">
                    Crear partida
                </Button>
            }
        >
            <Head title="Créditos de IA" />

            {/* The balance, split the way it actually behaves. */}
            <Card as="section">
                <div className="grid gap-6 sm:grid-cols-3">
                    <Figure
                        label="Disponible"
                        value={wallet.available}
                        hint="Para iniciar partidas nuevas"
                        emphasis
                    />
                    <Figure
                        label="Reservado"
                        value={wallet.reserved}
                        hint="Congelado por partidas en curso"
                    />
                    <Figure label="Total" value={wallet.total} hint="Disponible + reservado" />
                </div>

                {wallet.reserved > 0 && (
                    <Alert variant="info" className="mt-5 mb-0">
                        Al iniciar una partida se congela todo lo que podría llegar a gastar. Lo
                        que no se use vuelve a tu saldo cuando cierres el caso.
                    </Alert>
                )}
            </Card>

            {/* What a credit buys. Without this the balance is just a number. */}
            <Card as="section" className="mt-5">
                <CardHeader
                    title="En qué se gastan"
                    description="Solo lo que llama a un modelo de verdad consume créditos."
                />

                <ul className="divide-y divide-line text-sm">
                    <li className="flex items-baseline justify-between gap-4 py-2.5">
                        <span className="text-ink-muted">Pregunta a un sospechoso</span>
                        <span className="tabular font-medium text-ink">{costs.question}</span>
                    </li>
                    {Object.entries(costs.endings).map(([type, price]) => (
                        <li key={type} className="flex items-baseline justify-between gap-4 py-2.5">
                            <span className="text-ink-muted">
                                {ENDING_LABELS[type] || type}
                            </span>
                            <span className="tabular font-medium text-ink">
                                {price === 0 ? 'Gratis' : price}
                            </span>
                        </li>
                    ))}
                    <li className="flex items-baseline justify-between gap-4 py-2.5">
                        <span className="text-ink-muted">
                            Audios de la línea de tiempo
                        </span>
                        <span className="tabular font-medium text-ink">Gratis</span>
                    </li>
                </ul>
            </Card>

            {/* Where the frozen credits are, so "no puedo iniciar" is answerable. */}
            {holds.length > 0 && (
                <Card as="section" className="mt-5">
                    <CardHeader
                        title="Partidas que tienen créditos reservados"
                        description="Cierra un caso para recuperar lo que no gastó."
                    />

                    <ul className="divide-y divide-line">
                        {holds.map((hold) => (
                            <li
                                key={hold.game_id}
                                className="flex flex-wrap items-center justify-between gap-3 py-3"
                            >
                                <Link
                                    href={route('immersion.gm.game.show', hold.game_id)}
                                    className="min-w-0 text-sm font-medium text-ink hover:text-accent"
                                >
                                    {hold.name}
                                </Link>
                                <span className="tabular text-sm text-ink-muted">
                                    {hold.spent} usados · {hold.remaining} por devolver
                                </span>
                            </li>
                        ))}
                    </ul>
                </Card>
            )}

            {/* Top-up. */}
            <section className="mt-8">
                <h2 className="text-base font-semibold text-ink">Recargar</h2>
                <p className="mt-1 text-sm text-ink-muted">
                    Los créditos no caducan y se comparten entre todos tus casos.
                </p>

                {!canPurchase && !simulated && (
                    <Alert variant="info" className="mt-4">
                        La pasarela de pagos todavía no está conectada. Mientras tanto, escríbenos
                        y te recargamos la cuenta a mano.
                    </Alert>
                )}

                <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {packages.map((pack) => (
                        <PackageCard
                            key={pack.id}
                            pack={pack}
                            canPurchase={canPurchase}
                            simulated={simulated}
                        />
                    ))}
                </div>
            </section>

            {/* History. */}
            <Card as="section" className="mt-8">
                <CardHeader title="Movimientos" description="Los 50 más recientes." />

                {ledger.length === 0 ? (
                    <EmptyState
                        title="Todavía no hay movimientos"
                        description="Aquí aparecerá cada recarga, cada reserva al iniciar una partida y cada devolución al cerrarla."
                    />
                ) : (
                    <ul className="divide-y divide-line">
                        {ledger.map((entry) => (
                            <LedgerRow key={entry.id} entry={entry} />
                        ))}
                    </ul>
                )}
            </Card>
        </AppLayout>
    );
}
