import { Head, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import StatTile from '../../components/app/StatTile';
import EmptyState from '../../components/ui/EmptyState';
import { SelectField, TextField } from '../../components/ui/Field';
import { formatPrice } from '../../lib/format';

const STATUS = {
    pending: { label: 'Pendiente', tone: 'warning' },
    approved: { label: 'Aprobada', tone: 'success' },
    cancelled: { label: 'Cancelada', tone: 'neutral' },
    voided: { label: 'Anulada', tone: 'danger' },
};

function date(value) {
    return value ? new Intl.DateTimeFormat('es-CO', { dateStyle: 'medium' }).format(new Date(value)) : '—';
}

export default function AdminReferrals({ filters, summary, conversions, codes }) {
    function setFilter(key, value) {
        router.get(route('admin.referrals'), { ...filters, [key]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
    }

    function toggle(codeId) {
        router.patch(route('admin.referrals.toggle', codeId), {}, { preserveScroll: true });
    }

    return (
        <AppLayout current="admin.referrals" kicker="Mercadeo" title="Referidos">
            <Head title="Referidos" />
            <div className="space-y-6">
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                    <StatTile label="Conversiones" value={summary.total} />
                    <StatTile label="Aprobadas" value={summary.approved} />
                    <StatTile label="Pendientes" value={summary.pending} />
                    <StatTile label="Alertas" value={summary.alerts} />
                </div>

                {summary.sales.length > 0 && <Card as="section">
                    <CardHeader title="Ventas atribuidas" description={`Suma de órdenes aprobadas que usaron un código de referido. ${summary.rewards_issued} cupón${summary.rewards_issued === 1 ? '' : 'es'} emitido${summary.rewards_issued === 1 ? '' : 's'}.`} />
                    <div className="flex flex-wrap gap-3">
                        {summary.sales.map((sale) => <Badge key={sale.currency} tone="success">{formatPrice(sale.amount, sale.currency)}</Badge>)}
                    </div>
                </Card>}

                <Card as="section">
                    <CardHeader title="Filtrar conversiones" />
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <SelectField id="referral-status" label="Estado" value={filters.status} onChange={(value) => setFilter('status', value)} options={[{ value: '', label: 'Todos' }, ...Object.entries(STATUS).map(([value, item]) => ({ value, label: item.label }))]} />
                        <TextField id="referral-code" label="Código" value={filters.code} onChange={(value) => setFilter('code', value.toUpperCase())} />
                        <TextField id="referral-search" label="Usuario" value={filters.search} onChange={(value) => setFilter('search', value)} />
                        <TextField id="referral-from" type="date" label="Desde" value={filters.from} onChange={(value) => setFilter('from', value)} />
                        <TextField id="referral-to" type="date" label="Hasta" value={filters.to} onChange={(value) => setFilter('to', value)} />
                    </div>
                </Card>

                <Card as="section">
                    <CardHeader title="Códigos personales" description="Puedes detener nuevas conversiones sin borrar el historial ni los cupones ya emitidos." />
                    {codes.length === 0 ? <EmptyState title="Aún no hay códigos activos" description="Aparecen cuando un cliente completa su primera compra pagada de un caso." /> : (
                        <ul className="divide-y divide-line">
                            {codes.map((code) => <li key={code.id} className="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div>
                                    <code className="rounded bg-surface-sunken px-2 py-1 text-sm text-ink">{code.code}</code>
                                    <p className="mt-2 text-sm text-ink-muted">{code.user_name || 'Usuario eliminado'} · {code.conversions_count} conversión{code.conversions_count === 1 ? '' : 'es'}</p>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Badge tone={code.active ? 'success' : 'neutral'}>{code.active ? 'Activo' : 'Desactivado'}</Badge>
                                    <Button size="sm" variant="secondary" onClick={() => toggle(code.id)}>{code.active ? 'Desactivar' : 'Activar'}</Button>
                                </div>
                            </li>)}
                        </ul>
                    )}
                </Card>

                <Card as="section">
                    <CardHeader title="Actividad reciente" description="Las coincidencias de IP son alertas para revisión, no bloqueos automáticos." />
                    {conversions.length === 0 ? <EmptyState title="No hay conversiones con estos filtros" description="Las compras con códigos de referido aparecerán aquí." /> : (
                        <ul className="divide-y divide-line">
                            {conversions.map((conversion) => {
                                const status = STATUS[conversion.status] || { label: conversion.status, tone: 'neutral' };
                                return <li key={conversion.id} className="py-4 first:pt-0 last:pb-0">
                                    <div className="flex flex-wrap items-start justify-between gap-4">
                                        <div className="min-w-0">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <Badge tone={status.tone}>{status.label}</Badge>
                                                {conversion.code && <code className="rounded bg-surface-sunken px-2 py-1 text-xs text-ink">{conversion.code}</code>}
                                                {conversion.risk_flags.map((flag) => <Badge key={flag} tone="warning">Alerta: {flag === 'shared_ip' ? 'IP compartida' : flag === 'voided_after_approval' ? 'Anulada después del premio' : flag}</Badge>)}
                                            </div>
                                            <p className="mt-2 text-sm text-ink">
                                                {conversion.referrer?.name || 'Usuario eliminado'} <span className="text-ink-subtle">refirió a</span> {conversion.referred?.name || 'Usuario eliminado'}
                                            </p>
                                            <p className="mt-1 text-xs text-ink-muted">
                                                {conversion.case_name || 'Caso no disponible'} · Receptor {conversion.recipient_discount_percent}% · Referente {conversion.referrer_reward_percent}% · {date(conversion.created_at)}
                                            </p>
                                            {conversion.amount !== null && <p className="mt-1 text-xs text-ink-muted">Compra: {formatPrice(conversion.amount, conversion.currency)}</p>}
                                        </div>
                                        <div className="flex items-center gap-2">
                                            {conversion.reward && <Badge tone={conversion.reward.used ? 'neutral' : conversion.reward.active ? 'success' : 'danger'}>{conversion.reward.used ? 'Cupón usado' : conversion.reward.active ? `Cupón hasta ${date(conversion.reward.expires_at)}` : 'Cupón inactivo'}</Badge>}
                                            {conversion.code_id && <Button size="sm" variant="secondary" onClick={() => toggle(conversion.code_id)}>{conversion.code_active ? 'Desactivar enlace' : 'Activar enlace'}</Button>}
                                        </div>
                                    </div>
                                </li>;
                            })}
                        </ul>
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
