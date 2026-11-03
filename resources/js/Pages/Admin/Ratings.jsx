import { Head, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import { SelectField } from '../../components/ui/Field';
import Badge from '../../components/ui/Badge';

function average(value) {
    return value === null ? '—' : Number(value).toFixed(1).replace('.', ',');
}

export default function Ratings({ cases, ratings, filters }) {
    function setFilter(key, value) {
        router.get(route('admin.ratings'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });
    }

    return (
        <AppLayout current="admin.ratings" kicker="Administración" title="Valoraciones de casos">
            <Head title="Valoraciones de casos" />
            <div className="space-y-6">
                <Card as="section">
                    <CardHeader title="Opiniones de participantes" description="Cada respuesta proviene de un jugador de una partida terminada. No se muestran datos personales." />
                    <div className="grid gap-4 sm:grid-cols-2">
                        <SelectField id="ratings-case" label="Caso" value={filters.case || ''} onChange={(value) => setFilter('case', value)} options={[{ value: '', label: 'Todos los casos' }, ...cases.map((item) => ({ value: item.slug, label: item.name }))]} />
                        <SelectField id="ratings-sort" label="Ordenar por" value={filters.sort} onChange={(value) => setFilter('sort', value)} options={[{ value: 'count', label: 'Más valoraciones' }, { value: 'average', label: 'Mejor promedio' }, { value: 'name', label: 'Nombre del caso' }]} />
                    </div>
                </Card>

                {ratings.length === 0 ? <EmptyState title="No hay casos con este filtro" description="Prueba seleccionando otro caso." /> : ratings.map((item) => (
                    <Card key={item.slug} as="section">
                        <CardHeader title={item.name} description={item.count ? `${item.count} valoración${item.count === 1 ? '' : 'es'} recibida${item.count === 1 ? '' : 's'}.` : 'Aún no tiene valoraciones.'} actions={item.count ? <Badge tone="accent">★ {average(item.average)} / 5</Badge> : null} />
                        {item.count > 0 && <>
                            <div className="grid grid-cols-3 gap-2 sm:grid-cols-5">
                                {item.distribution.filter((entry) => entry.count > 0).map((entry) => <div key={entry.half_steps} className="rounded-control bg-surface-sunken px-3 py-2 text-center"><p className="text-xs text-ink-subtle">★ {entry.label}</p><p className="tabular mt-1 font-semibold text-ink">{entry.count}</p></div>)}
                            </div>
                            {item.tags.length > 0 && <div className="mt-5 border-t border-line pt-4"><p className="text-xs font-medium uppercase tracking-wide text-ink-muted">Lo más destacado</p><div className="mt-3 flex flex-wrap gap-2">{item.tags.map((tag) => <Badge key={tag.label} tone="neutral">{tag.label} · {tag.count}</Badge>)}</div></div>}
                        </>}
                    </Card>
                ))}
            </div>
        </AppLayout>
    );
}
