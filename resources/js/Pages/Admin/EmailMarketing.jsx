import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../../Layouts/AppLayout';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Card, { CardHeader } from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import { SelectField, TextField } from '../../components/ui/Field';
import SectionHeader from '../../components/ui/SectionHeader';
import StatTile from '../../components/app/StatTile';
import { formatDateTime } from '../../lib/format';

function queryFrom(filters) {
    return Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== 'all' && value !== null));
}

function ContactStatus({ contact }) {
    if (contact.excluded) return <Badge tone="danger">No contactar</Badge>;
    if (!contact.email_valid) return <Badge tone="warning">Email inválido</Badge>;
    if (!contact.verified) return <Badge tone="neutral">Sin verificar</Badge>;

    return <Badge tone="success">Incluido</Badge>;
}

export default function EmailMarketing({ summary, filters, contacts, recentCases, lastExport }) {
    const [values, setValues] = useState(filters);
    const query = queryFrom(values);
    const setValue = (field, value) => setValues((current) => ({ ...current, [field]: value }));

    function applyFilters(event) {
        event.preventDefault();
        router.get(route('admin.email-marketing'), query, { preserveState: true, replace: true });
    }

    function resetFilters() {
        const reset = { search: '', verification: 'all', marketing: 'all', email: 'all', from: '', until: '' };
        setValues(reset);
        router.get(route('admin.email-marketing'), {}, { preserveState: true, replace: true });
    }

    return (
        <AppLayout current="admin.email-marketing" kicker="Administración" title="Email marketing" actions={<Button href={route('admin.email-marketing.export', query)} external>Descargar CSV para Brevo</Button>}>
            <Head title="Email marketing" />
            <div className="space-y-8">
                <section>
                    <SectionHeader title="Audiencia disponible" />
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                        <StatTile label="Clientes activos" value={summary.active_clients} />
                        <StatTile label="Emails válidos" value={summary.valid_emails} />
                        <StatTile label="Sin email válido" value={summary.invalid_or_missing_emails} tone={summary.invalid_or_missing_emails ? 'alert' : 'default'} />
                        <StatTile label="Excluidos" value={summary.excluded_contacts} tone={summary.excluded_contacts ? 'alert' : 'default'} />
                    </div>
                    <p className="mt-3 text-sm text-ink-muted">El CSV excluye automáticamente direcciones inválidas y contactos marcados como “No contactar”. El envío se gestiona en Brevo.</p>
                    {lastExport && <p className="mt-2 text-xs text-ink-subtle">Última exportación: {lastExport.contacts_count} contacto(s) el {formatDateTime(lastExport.exported_at)}{lastExport.exported_by ? ` por ${lastExport.exported_by}` : ''}.</p>}
                </section>

                <Card as="section">
                    <CardHeader title="Contactos" description="Filtra la audiencia antes de preparar la importación en Brevo." />
                    <form onSubmit={applyFilters} className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <TextField id="marketing-search" label="Buscar" value={values.search} onChange={(value) => setValue('search', value)} placeholder="Nombre o email" />
                        <SelectField id="marketing-verification" label="Verificación" value={values.verification} onChange={(value) => setValue('verification', value)} options={[{ value: 'all', label: 'Todas' }, { value: 'verified', label: 'Email verificado' }, { value: 'unverified', label: 'Sin verificar' }]} />
                        <SelectField id="marketing-status" label="Marketing" value={values.marketing} onChange={(value) => setValue('marketing', value)} options={[{ value: 'all', label: 'Todos' }, { value: 'included', label: 'Incluidos' }, { value: 'excluded', label: 'No contactar' }]} />
                        <SelectField id="marketing-email" label="Email" value={values.email} onChange={(value) => setValue('email', value)} options={[{ value: 'all', label: 'Todos' }, { value: 'valid', label: 'Válido' }, { value: 'invalid', label: 'Inválido o vacío' }]} />
                        <TextField id="marketing-from" label="Registrado desde" type="date" value={values.from || ''} onChange={(value) => setValue('from', value)} />
                        <TextField id="marketing-until" label="Registrado hasta" type="date" value={values.until || ''} onChange={(value) => setValue('until', value)} />
                        <div className="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-5"><Button type="submit" variant="secondary">Aplicar filtros</Button><Button type="button" variant="ghost" onClick={resetFilters}>Limpiar</Button></div>
                    </form>
                    <div className="mt-6">
                        {contacts.length === 0 ? <EmptyState title="No hay contactos con estos filtros" description="Prueba con otros filtros o limpia la búsqueda." /> : <div className="overflow-x-auto"><table className="w-full min-w-175 text-left text-sm"><thead><tr className="border-b border-line text-xs uppercase tracking-wide text-ink-muted"><th className="py-2 pr-4 font-medium">Cliente</th><th className="py-2 pr-4 font-medium">Email</th><th className="py-2 pr-4 font-medium">Estado</th><th className="py-2 pr-4 font-medium">Registro</th><th className="py-2 font-medium">Acción</th></tr></thead><tbody className="divide-y divide-line">{contacts.map((contact) => <tr key={contact.id}><th scope="row" className="py-3 pr-4 font-medium text-ink">{contact.name}</th><td className="py-3 pr-4 text-ink-muted">{contact.email || '—'}</td><td className="py-3 pr-4"><ContactStatus contact={contact} /></td><td className="whitespace-nowrap py-3 pr-4 text-ink-muted">{formatDateTime(contact.registered_at)}</td><td className="py-3"><Button size="sm" variant={contact.excluded ? 'secondary' : 'ghost'} onClick={() => router.patch(route('admin.email-marketing.exclusion', contact.id), {}, { preserveScroll: true })}>{contact.excluded ? 'Volver a incluir' : 'No contactar'}</Button></td></tr>)}</tbody></table></div>}
                    </div>
                </Card>

                <Card as="section">
                    <CardHeader title="Novedades para comunicar" description="Los últimos casos publicados que puedes usar como base para una campaña en Brevo." />
                    {recentCases.length === 0 ? <EmptyState title="Aún no hay casos publicados" description="Los casos aparecerán aquí cuando se publiquen en el catálogo." /> : <ul className="divide-y divide-line">{recentCases.map((mysteryCase) => <li key={mysteryCase.slug} className="flex flex-wrap items-start justify-between gap-3 py-4"><div className="min-w-0"><div className="flex flex-wrap items-center gap-2"><h2 className="font-medium text-ink">{mysteryCase.name}</h2><Badge tone="success">{mysteryCase.status}</Badge></div>{mysteryCase.summary && <p className="mt-1 max-w-3xl text-sm text-ink-muted">{mysteryCase.summary}</p>}<p className="mt-2 text-xs text-ink-subtle">Publicado: {formatDateTime(mysteryCase.published_at)}</p></div>{mysteryCase.url && <Link href={mysteryCase.url} className="text-sm font-medium text-accent hover:underline">Ver caso</Link>}</li>)}</ul>}
                </Card>
            </div>
        </AppLayout>
    );
}
