import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Card, { CardHeader } from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import { formatDateTime, formatPrice } from '../../lib/format';

function saleStatus(mysteryCase) {
    return mysteryCase.published
        ? { label: 'Publicado', tone: 'success' }
        : { label: 'Borrador', tone: 'neutral' };
}

function availabilityStatus(mysteryCase) {
    return mysteryCase.playable
        ? { label: 'Contenido instalado', tone: 'accent' }
        : { label: 'Contenido no instalado', tone: 'warning' };
}

function CaseRow({ mysteryCase, updating, onToggle }) {
    const sale = saleStatus(mysteryCase);
    const availability = availabilityStatus(mysteryCase);
    const canPublish = mysteryCase.playable || mysteryCase.published;

    return (
        <article className="grid gap-4 border-b border-line py-5 last:border-b-0 sm:grid-cols-[5rem_minmax(0,1fr)_auto] sm:items-center">
            <div className="h-20 overflow-hidden rounded-control bg-surface-sunken sm:w-20">
                {mysteryCase.cover_url ? (
                    <img
                        src={mysteryCase.cover_url}
                        alt={`Portada de ${mysteryCase.name}`}
                        className="h-full w-full object-cover"
                    />
                ) : (
                    <span className="flex h-full items-center justify-center px-2 text-center text-xs text-ink-subtle">
                        Sin portada
                    </span>
                )}
            </div>

            <div className="min-w-0">
                <div className="flex flex-wrap items-center gap-2">
                    <h2 className="truncate text-base font-semibold text-ink">{mysteryCase.name}</h2>
                    <Badge tone={sale.tone}>{sale.label}</Badge>
                    <Badge tone={availability.tone}>{availability.label}</Badge>
                </div>
                {mysteryCase.tagline && <p className="mt-1 text-sm text-ink-muted">{mysteryCase.tagline}</p>}
                <dl className="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-ink-subtle">
                    <div>
                        <dt className="sr-only">Precio</dt>
                        <dd>{formatPrice(mysteryCase.price_amount, mysteryCase.currency)}</dd>
                    </div>
                    <div>
                        <dt className="sr-only">Orden</dt>
                        <dd>Orden {mysteryCase.sort_order}</dd>
                    </div>
                    <div>
                        <dt className="sr-only">Fecha de publicación</dt>
                        <dd>
                            {mysteryCase.published_at
                                ? `Publicado: ${formatDateTime(mysteryCase.published_at)}`
                                : 'Aún no publicado'}
                        </dd>
                    </div>
                </dl>
            </div>

            <div className="flex sm:justify-end">
                <Button
                    variant={mysteryCase.published ? 'danger' : 'primary'}
                    size="sm"
                    loading={updating}
                    disabled={!canPublish}
                    title={!canPublish ? 'El manifiesto del caso no está instalado en este servidor.' : undefined}
                    onClick={() => onToggle(mysteryCase)}
                >
                    {mysteryCase.published ? 'Retirar de venta' : 'Publicar'}
                </Button>
            </div>
        </article>
    );
}

export default function AdminCases({ cases }) {
    const [updating, setUpdating] = useState(null);

    function toggle(mysteryCase) {
        setUpdating(mysteryCase.slug);

        router.patch(route('admin.cases.toggle', mysteryCase.slug), {}, {
            preserveScroll: true,
            onFinish: () => setUpdating(null),
        });
    }

    return (
        <AppLayout current="admin.cases" kicker="Administración" title="Publicación de casos">
            <Head title="Publicación de casos" />

            <Card as="section">
                <CardHeader
                    title="Casos instalados"
                    description="Publicar los muestra en el catálogo, anuncios y checkout. Retirarlos solo bloquea nuevas compras; las bibliotecas y partidas existentes permanecen disponibles."
                />

                {cases.length === 0 ? (
                    <EmptyState
                        title="Todavía no hay casos sincronizados"
                        description="El próximo despliegue registrará los manifiestos instalados como borradores."
                    />
                ) : (
                    <div>
                        {cases.map((mysteryCase) => (
                            <CaseRow
                                key={mysteryCase.slug}
                                mysteryCase={mysteryCase}
                                updating={updating === mysteryCase.slug}
                                onToggle={toggle}
                            />
                        ))}
                    </div>
                )}
            </Card>
        </AppLayout>
    );
}
