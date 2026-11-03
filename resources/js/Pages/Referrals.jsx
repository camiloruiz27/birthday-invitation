import { Head } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '../Layouts/AppLayout';
import Card, { CardHeader } from '../components/ui/Card';
import Button from '../components/ui/Button';
import Badge from '../components/ui/Badge';
import EmptyState from '../components/ui/EmptyState';
import { copyText } from '../components/ui/InAppBrowserNotice';
import StatTile from '../components/app/StatTile';

const STATUS = {
    pending: { label: 'Pendiente de pago', tone: 'warning' },
    approved: { label: 'Compra aprobada', tone: 'success' },
    cancelled: { label: 'No completada', tone: 'neutral' },
    voided: { label: 'Anulada', tone: 'danger' },
};

function date(value) {
    return value ? new Intl.DateTimeFormat('es-CO', { dateStyle: 'medium' }).format(new Date(value)) : '—';
}

function CopyButton({ value, label = 'Copiar enlace' }) {
    const [copied, setCopied] = useState(false);

    async function copy() {
        if (await copyText(value)) {
            setCopied(true);
            window.setTimeout(() => setCopied(false), 1800);
        } else {
            setCopied(false);
        }
    }

    return <Button type="button" variant="secondary" size="sm" onClick={copy}>{copied ? 'Copiado' : label}</Button>;
}

export default function Referrals({ referral, summary, conversions }) {
    return (
        <AppLayout current="referrals" kicker="Comunidad" title="Invita y gana">
            <Head title="Referidos" />

            {!referral ? (
                <EmptyState
                    title="Tu enlace de referido está listo"
                    description="Recarga la página para generar tu enlace personal y empezar a compartir descuentos."
                />
            ) : (
                <div className="space-y-6">
                    <Card as="section" className={referral.active ? 'border-accent bg-accent-dim/20' : ''}>
                        <CardHeader
                            title={referral.active ? 'Comparte tu enlace personal' : 'Tu enlace está desactivado'}
                            description={referral.active
                                ? 'Tu amigo recibe 20% en su primera compra o 15% después. Tú ganas 30% o 25% para tu próximo caso cuando su pago sea aprobado.'
                                : 'Escríbenos si crees que esto es un error.'}
                        />
                        <div className="mt-5 grid gap-3 sm:grid-cols-[1fr_auto]">
                            <div className="min-w-0 rounded-control border border-line bg-surface-raised px-4 py-3">
                                <p className="text-xs font-medium uppercase tracking-wide text-ink-subtle">Tu enlace</p>
                                <p className="mt-1 truncate font-mono text-sm text-ink">{referral.share_url}</p>
                                <p className="mt-2 text-xs text-ink-muted">Código: <span className="font-medium text-ink">{referral.code}</span></p>
                            </div>
                            <div className="flex flex-wrap items-center gap-2 sm:flex-col sm:items-stretch sm:justify-center">
                                {referral.active && <CopyButton value={referral.share_url} />}
                                {referral.active && <CopyButton value={referral.code} label="Copiar código" />}
                            </div>
                        </div>
                    </Card>

                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
                        <StatTile label="Compras aprobadas" value={summary.approved} />
                        <StatTile label="Pendientes" value={summary.pending} />
                        <StatTile label="Cupones disponibles" value={summary.available_rewards} />
                    </div>

                    <Card as="section">
                        <CardHeader title="Tus recompensas y referidos" description="Cada recompensa sirve una vez para un caso y vence 90 días después de la compra aprobada." />
                        {conversions.length === 0 ? (
                            <EmptyState title="Aún no hay compras con tu enlace" description="Comparte tu enlace para empezar a generar recompensas." />
                        ) : (
                            <ul className="divide-y divide-line">
                                {conversions.map((conversion) => {
                                    const status = STATUS[conversion.status] || { label: conversion.status, tone: 'neutral' };
                                    return (
                                        <li key={conversion.id} className="py-4 first:pt-0 last:pb-0">
                                            <div className="flex flex-wrap items-start justify-between gap-3">
                                                <div>
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <Badge tone={status.tone}>{status.label}</Badge>
                                                        <span className="text-xs text-ink-subtle">{date(conversion.created_at)}</span>
                                                    </div>
                                                    <p className="mt-2 text-sm text-ink-muted">
                                                        Descuento enviado: {conversion.recipient_discount_percent}% · Tu recompensa: {conversion.referrer_reward_percent}%
                                                        {conversion.case_name && ` · ${conversion.case_name}`}
                                                    </p>
                                                </div>
                                                {conversion.reward && (
                                                    <div className="rounded-control border border-line bg-surface-raised px-3 py-2 text-right">
                                                        <p className="font-mono text-sm font-medium text-ink">{conversion.reward.code}</p>
                                                        <p className="mt-1 text-xs text-ink-muted">
                                                            {conversion.reward.used ? 'Usado' : conversion.reward.active ? `Vence ${date(conversion.reward.expires_at)}` : 'No disponible'}
                                                        </p>
                                                        {conversion.reward.active && <CopyButton value={conversion.reward.code} label="Copiar cupón" />}
                                                    </div>
                                                )}
                                            </div>
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </Card>
                </div>
            )}
        </AppLayout>
    );
}
