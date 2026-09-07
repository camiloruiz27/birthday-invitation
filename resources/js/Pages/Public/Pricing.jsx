import { Head, Link } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Card from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import EmptyState from '../../components/ui/EmptyState';
import Section from '../../components/public/Section';
import { CaseFacts } from '../../components/public/CaseCard';
import { formatPrice } from '../../lib/format';

const INCLUDED = [
    'Acceso permanente al caso',
    'Partidas ilimitadas, con grupos distintos',
    'Todas las mecánicas del caso, sin extras',
    'Los jugadores no necesitan cuenta ni pagar nada',
    'Presencial o a distancia',
];

function CasePrice({ mysteryCase }) {
    return (
        <Card as="article" className="flex flex-col">
            <h3 className="font-semibold text-ink">{mysteryCase.name}</h3>

            {mysteryCase.tagline && (
                <p className="mt-2 text-sm text-ink-muted">{mysteryCase.tagline}</p>
            )}

            <CaseFacts mysteryCase={mysteryCase} className="mt-4" />

            <p className="mt-6 text-2xl font-semibold text-ink">
                {formatPrice(mysteryCase.price_amount, mysteryCase.currency)}
            </p>
            <p className="mt-1 text-sm text-ink-muted">Pago único</p>

            <Button
                href={route('cases.show', mysteryCase.slug)}
                variant="secondary"
                fullWidth
                className="mt-6"
            >
                Ver el caso
            </Button>
        </Card>
    );
}

export default function Pricing({ cases }) {
    return (
        <PublicLayout current="pricing">
            <Head title="Precios" />

            <Section
                kicker="Precios"
                title="Pagas por caso, no por mes"
                description="Compras el misterio que quieras jugar y es tuyo. No hay suscripción, ni límite de partidas, ni cobro por jugador."
            >
                {cases.length === 0 ? (
                    <EmptyState
                        title="Todavía no hay casos publicados"
                        description="Estamos preparando los primeros. Vuelve pronto."
                    />
                ) : (
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {cases.map((item) => (
                            <CasePrice key={item.slug} mysteryCase={item} />
                        ))}
                    </div>
                )}
            </Section>

            <Section title="Qué incluye cualquier caso" width="prose" tone="sunken">
                <ul className="space-y-3">
                    {INCLUDED.map((item) => (
                        <li key={item} className="flex gap-3 text-ink-muted">
                            <span aria-hidden="true" className="text-accent">
                                —
                            </span>
                            {item}
                        </li>
                    ))}
                </ul>

                <p className="mt-8 text-sm text-ink-muted">
                    Solo quien dirige la partida necesita una cuenta y haber adquirido el caso.
                    Tus jugadores entran con un enlace.
                </p>
            </Section>

            <Section width="prose">
                <div className="rounded-card border border-dashed border-line-strong p-6">
                    <h2 className="text-sm font-semibold text-ink">
                        ¿Y una suscripción con varios casos?
                    </h2>
                    <p className="mt-2 text-sm leading-relaxed text-ink-muted">
                        Es algo que estamos considerando para más adelante, cuando el catálogo
                        sea más grande. Hoy no existe: lo único que puedes hacer es comprar
                        casos sueltos, y por eso no lo anunciamos como si estuviera disponible.
                    </p>
                    <p className="mt-3 text-sm text-ink-muted">
                        Lo que compres ahora seguirá siendo tuyo pase lo que pase con los
                        planes futuros.
                    </p>
                </div>
            </Section>

            <Section tone="sunken">
                <div className="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                    <div>
                        <h2 className="text-xl font-semibold text-ink">¿Dudas antes de comprar?</h2>
                        <p className="mt-2 text-sm text-ink-muted">
                            En{' '}
                            <Link href={route('mechanics')} className="text-accent underline">
                                Mecánicas
                            </Link>{' '}
                            está todo lo que incluye una partida, y en{' '}
                            <Link href={route('ai')} className="text-accent underline">
                                Inteligencia artificial
                            </Link>{' '}
                            explicamos dónde la usamos.
                        </p>
                    </div>
                    <Button href={route('cases.index')}>Ver los casos</Button>
                </div>
            </Section>
        </PublicLayout>
    );
}
