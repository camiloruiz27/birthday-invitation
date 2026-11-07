import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Card, { CardHeader } from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import { SelectField, TextField } from '../../components/ui/Field';
import StatTile from '../../components/app/StatTile';

function number(value) {
    return new Intl.NumberFormat('es-CO').format(value ?? 0);
}

function decimal(value, suffix = '') {
    return value == null ? '—' : `${Number(value).toFixed(1).replace('.', ',')}${suffix}`;
}

function Statistics({ statistics, filters, options }) {
    const [values, setValues] = useState({
        event: filters.event || '',
        promo_code: filters.promo_code || '',
        from: filters.from || '',
        until: filters.until || '',
    });

    function query(input) {
        return Object.fromEntries(Object.entries(input).filter(([, value]) => value !== '' && value != null));
    }

    function apply(event) {
        event.preventDefault();
        router.get(route('admin.express'), query(values), { preserveState: true, replace: true, preserveScroll: true });
    }

    function clear() {
        const empty = { event: '', promo_code: '', from: '', until: '' };
        setValues(empty);
        router.get(route('admin.express'), {}, { preserveState: true, replace: true, preserveScroll: true });
    }

    const summary = statistics.summary;

    return (
        <section className="space-y-5" aria-labelledby="express-statistics-title">
            <div>
                <p className="case-stamp text-[10px] text-accent">Resultados</p>
                <h2 id="express-statistics-title" className="mt-1 font-display text-2xl font-semibold text-ink">Estadísticas de Express</h2>
                <p className="mt-1 text-sm text-ink-muted">Participación anónima. Compara ferias, promociones y periodos sin mostrar nombres ni contactos.</p>
            </div>

            <Card as="section">
                <CardHeader title="Filtrar resultados" description="El código corresponde al que quedó guardado cuando se creó cada sala." />
                <form onSubmit={apply} className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <SelectField id="express-stats-event" label="Evento" value={values.event} onChange={(value) => setValues((current) => ({ ...current, event: value }))} options={[{ value: '', label: 'Todos los eventos' }, ...options.events]} />
                    <SelectField id="express-stats-promo" label="Código promocional" value={values.promo_code} onChange={(value) => setValues((current) => ({ ...current, promo_code: value }))} options={[{ value: '', label: 'Todos los códigos' }, { value: '__none__', label: 'Sin código o histórico' }, ...options.promo_codes.map((code) => ({ value: code, label: code }))]} />
                    <TextField id="express-stats-from" label="Desde" type="date" value={values.from} onChange={(value) => setValues((current) => ({ ...current, from: value }))} />
                    <TextField id="express-stats-until" label="Hasta" type="date" value={values.until} onChange={(value) => setValues((current) => ({ ...current, until: value }))} />
                    <div className="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-4"><Button type="submit" variant="secondary">Aplicar filtros</Button><Button type="button" variant="ghost" onClick={clear}>Limpiar</Button></div>
                </form>
            </Card>

            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <StatTile label="Salas creadas" value={number(summary.rooms)} />
                <StatTile label="Salas iniciadas" value={number(summary.started)} />
                <StatTile label="Salas terminadas" value={number(summary.finished)} />
                <StatTile label="Finalización" value={decimal(summary.completion_rate, '%')} />
                <StatTile label="Jugadores" value={number(summary.players)} />
                <StatTile label="Preguntas" value={number(summary.questions)} />
                <StatTile label="Acusaciones" value={number(summary.accusations)} />
                <StatTile label="Puntaje promedio" value={decimal(summary.average_score)} />
                <StatTile label="Contactos voluntarios" value={number(summary.leads)} />
                <StatTile label="Clics al catálogo" value={number(summary.cta_clicks)} />
            </div>
            <p className="text-xs text-ink-subtle">Puntajes perfectos: {number(summary.perfect_scores)} · Tasa de clic al catálogo: {decimal(summary.cta_rate, '%')}.</p>

            <div className="grid gap-5 xl:grid-cols-2">
                <Card as="section">
                    <CardHeader title="Por evento y código" description="Permite comparar cada feria o promoción aunque reutilices los mismos casos." />
                    {statistics.campaigns.length === 0 ? <EmptyState title="No hay partidas con estos filtros" description="Cambia los filtros o juega una ronda Express." /> : (
                        <div className="overflow-x-auto"><table className="w-full min-w-150 text-left text-sm"><thead><tr className="border-b border-line text-xs uppercase tracking-wide text-ink-muted"><th className="py-2 pr-3 font-medium">Evento / código</th><th className="py-2 pr-3 text-right font-medium">Salas</th><th className="py-2 pr-3 text-right font-medium">Terminadas</th><th className="py-2 pr-3 text-right font-medium">Jugadores</th><th className="py-2 text-right font-medium">Clics</th></tr></thead><tbody className="divide-y divide-line">{statistics.campaigns.map((row) => <tr key={`${row.event_slug}-${row.promo_code || 'none'}-${row.legacy}`}><td className="py-3 pr-3"><p className="font-medium text-ink">{row.event_name}</p><p className="mt-0.5 text-xs text-ink-muted">{row.legacy ? 'Histórico sin código registrado' : (row.promo_code || 'Sin código')}{row.utm_campaign ? ` · ${row.utm_campaign}` : ''}</p></td><td className="py-3 pr-3 text-right text-ink">{number(row.rooms)}</td><td className="py-3 pr-3 text-right text-ink">{number(row.finished)}</td><td className="py-3 pr-3 text-right text-ink">{number(row.players)}</td><td className="py-3 text-right text-ink">{number(row.cta_clicks)}</td></tr>)}</tbody></table></div>
                    )}
                </Card>

                <Card as="section">
                    <CardHeader title="Rendimiento por caso" description="Qué casos atraen más partidas y cómo les va a los equipos." />
                    {statistics.cases.length === 0 ? <EmptyState title="No hay casos con estos filtros" description="Aún no hay rondas para comparar." /> : (
                        <div className="overflow-x-auto"><table className="w-full min-w-125 text-left text-sm"><thead><tr className="border-b border-line text-xs uppercase tracking-wide text-ink-muted"><th className="py-2 pr-3 font-medium">Caso</th><th className="py-2 pr-3 text-right font-medium">Salas</th><th className="py-2 pr-3 text-right font-medium">Jugadores</th><th className="py-2 text-right font-medium">Promedio</th></tr></thead><tbody className="divide-y divide-line">{statistics.cases.map((row) => <tr key={row.slug}><td className="py-3 pr-3"><p className="font-medium text-ink">{row.name}</p><p className="mt-0.5 text-xs text-ink-muted">{number(row.finished)} terminadas</p></td><td className="py-3 pr-3 text-right text-ink">{number(row.rooms)}</td><td className="py-3 pr-3 text-right text-ink">{number(row.players)}</td><td className="py-3 text-right text-ink">{decimal(row.average_score)}</td></tr>)}</tbody></table></div>
                    )}
                </Card>
            </div>
        </section>
    );
}

function Toggle({ id, checked, onChange, label, hint }) {
    return (
        <div>
            <label htmlFor={id} className="flex cursor-pointer items-start gap-3 rounded-control border border-line p-3 text-sm text-ink">
                <input id={id} type="checkbox" checked={checked} onChange={(event) => onChange(event.target.checked)} className="mt-0.5 h-4 w-4 rounded border-line-strong bg-surface-sunken" />
                <span><span className="block font-medium">{label}</span>{hint && <span className="mt-0.5 block text-xs leading-relaxed text-ink-muted">{hint}</span>}</span>
            </label>
        </div>
    );
}

function EventSettings({ event, codes }) {
    const currentIsAvailable = codes.some((code) => code.code === event.promo_code);
    const codeOptions = [
        { value: '', label: 'No mostrar código promocional' },
        ...(event.promo_code && !currentIsAvailable
            ? [{ value: event.promo_code, label: `${event.promo_code} — ya no está disponible; selecciona otro` }]
            : []),
        ...codes.map((code) => ({ value: code.code, label: `${code.code} — ${code.description}` })),
    ];

    const form = useForm({
        promo_code: event.promo_code || '',
        display_name: event.name || '',
        landing_title: event.landing_title || '',
        landing_emphasis: event.landing_emphasis || '',
        tagline: event.tagline || '',
        active: Boolean(event.active),
        ends_at_enabled: Boolean(event.has_end_date),
        ends_at: event.ends_at || '',
        utm_medium: event.utm_medium || '',
        utm_campaign: event.utm_campaign || '',
        closed_title: event.closed_title || '',
        closed_reason: event.closed_reason || '',
    });

    function submit(eventSubmit) {
        eventSubmit.preventDefault();
        form.patch(route('admin.express.update', event.slug), { preserveScroll: true });
    }

    return (
        <Card as="section">
            <CardHeader title={event.name || 'Evento Express'} description="Configura lo que ven los participantes al abrir y al terminar la experiencia." />
            <form onSubmit={submit} className="space-y-7">
                <section className="grid gap-4 lg:grid-cols-2">
                    <TextField id={`${event.slug}-name`} label="Nombre del evento" value={form.data.display_name} onChange={(value) => form.setData('display_name', value)} error={form.errors.display_name} />
                    <SelectField id={`${event.slug}-promo`} label="Código mostrado al terminar" value={form.data.promo_code} onChange={(value) => form.setData('promo_code', value)} options={codeOptions} error={form.errors.promo_code} hint="Solo aparecen códigos públicos, activos y válidos para comprar casos." />
                    <TextField id={`${event.slug}-title`} label="Título principal" value={form.data.landing_title} onChange={(value) => form.setData('landing_title', value)} error={form.errors.landing_title} />
                    <TextField id={`${event.slug}-emphasis`} label="Texto destacado del título" value={form.data.landing_emphasis} onChange={(value) => form.setData('landing_emphasis', value)} error={form.errors.landing_emphasis} />
                    <div className="lg:col-span-2"><TextField id={`${event.slug}-tagline`} label="Descripción" value={form.data.tagline} onChange={(value) => form.setData('tagline', value)} error={form.errors.tagline} /></div>
                </section>

                <section className="border-t border-line pt-6">
                    <h3 className="font-semibold text-ink">Disponibilidad</h3>
                    <p className="mt-1 text-sm text-ink-muted">Activa la feria para crear salas. Puedes quitar la fecha de cierre temporalmente mientras haces pruebas.</p>
                    <div className="mt-4 grid gap-3 lg:grid-cols-2">
                        <Toggle id={`${event.slug}-active`} checked={form.data.active} onChange={(value) => form.setData('active', value)} label="Permitir partidas Express" hint="Si se desactiva, la página mostrará el mensaje de cierre." />
                        <Toggle id={`${event.slug}-end-enabled`} checked={form.data.ends_at_enabled} onChange={(value) => form.setData('ends_at_enabled', value)} label="Cerrar automáticamente en una fecha" hint="Desactívalo para poder ensayar sin que el evento cierre por fecha." />
                        {form.data.ends_at_enabled && <TextField id={`${event.slug}-ends-at`} label="Disponible hasta" type="datetime-local" value={form.data.ends_at} onChange={(value) => form.setData('ends_at', value)} error={form.errors.ends_at} className="lg:col-span-2" />}
                    </div>
                </section>

                <section className="border-t border-line pt-6">
                    <h3 className="font-semibold text-ink">Atribución del enlace final</h3>
                    <p className="mt-1 text-sm text-ink-muted">El enlace siempre conserva <code className="text-ink">utm_source=express</code>. Deja vacío cualquiera de estos campos para quitarlo de la URL final.</p>
                    <div className="mt-4 grid gap-4 lg:grid-cols-2">
                        <TextField id={`${event.slug}-utm-medium`} label="UTM medium" value={form.data.utm_medium} onChange={(value) => form.setData('utm_medium', value)} error={form.errors.utm_medium} placeholder="Por ejemplo: feria-cali" />
                        <TextField id={`${event.slug}-utm-campaign`} label="UTM campaign" value={form.data.utm_campaign} onChange={(value) => form.setData('utm_campaign', value)} error={form.errors.utm_campaign} placeholder="Por ejemplo: feria-usb-2026" />
                    </div>
                </section>

                <section className="border-t border-line pt-6">
                    <h3 className="font-semibold text-ink">Mensaje cuando no se puede jugar</h3>
                    <p className="mt-1 text-sm text-ink-muted">Se muestra cuando la feria está desactivada o ya superó su fecha de cierre.</p>
                    <div className="mt-4 grid gap-4 lg:grid-cols-2">
                        <TextField id={`${event.slug}-closed-title`} label="Título de cierre" value={form.data.closed_title} onChange={(value) => form.setData('closed_title', value)} error={form.errors.closed_title} />
                        <TextField id={`${event.slug}-closed-reason`} label="Mensaje de cierre" value={form.data.closed_reason} onChange={(value) => form.setData('closed_reason', value)} error={form.errors.closed_reason} hint="Déjalo vacío para usar el mensaje automático según el motivo del cierre." />
                    </div>
                </section>

                <div className="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                    <div>{event.has_override ? <Badge tone="success">Configuración administrativa activa</Badge> : <Badge tone="neutral">Configuración original</Badge>}</div>
                    <Button type="submit" disabled={form.processing}>{form.processing ? 'Guardando…' : 'Guardar cambios'}</Button>
                </div>
            </form>
        </Card>
    );
}

export default function AdminExpress({ events, codes, statistics, statFilters, statOptions }) {
    return (
        <AppLayout current="admin.express" kicker="Administración" title="Casos Express">
            <Head title="Casos Express" />
            <div className="space-y-6">
                <Statistics statistics={statistics} filters={statFilters} options={statOptions} />
                <div className="border-t border-line pt-7">
                <p className="max-w-3xl text-sm leading-relaxed text-ink-muted">
                    Personaliza la experiencia de cada feria y controla cuándo se puede jugar. Los cambios se aplican a la landing pública de Express y a la promoción final.
                </p>
                </div>
                {events.map((event) => <EventSettings key={event.slug} event={event} codes={codes} />)}
            </div>
        </AppLayout>
    );
}
