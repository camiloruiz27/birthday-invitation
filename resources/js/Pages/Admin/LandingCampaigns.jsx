import { Head, router, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Card, { CardHeader } from '../../components/ui/Card';
import EmptyState from '../../components/ui/EmptyState';
import { CheckboxField, SelectField, TextField } from '../../components/ui/Field';
import StatTile from '../../components/app/StatTile';

const EMPTY = {
    promo_code_id: '', name: '', active: true, eyebrow: '', headline: '', description: '',
    paid_case_ids: [], free_case_ids: [], recommended_case_id: '',
};

function toggle(items, id, max) {
    const value = Number(id);
    if (items.includes(value)) return items.filter((item) => item !== value);
    return items.length >= max ? items : [...items, value];
}

function CampaignForm({ campaign, promoCodes, cases }) {
    const form = useForm(campaign ? {
        promo_code_id: String(campaign.promo_code_id), name: campaign.name, active: campaign.active,
        eyebrow: campaign.eyebrow || '', headline: campaign.headline || '', description: campaign.description || '',
        paid_case_ids: campaign.paid_case_ids.map(Number), free_case_ids: campaign.free_case_ids.map(Number),
        recommended_case_id: String(campaign.recommended_case_id || ''),
    } : EMPTY);
    const paid = cases.filter((item) => item.price_amount > 0);
    const free = cases.filter((item) => item.price_amount === 0);
    const codes = promoCodes.filter((code) => code.active && (!code.expires_at || new Date(code.expires_at) > new Date()) && (!code.max_redemptions || code.redemptions_count < code.max_redemptions));

    function submit(event) {
        event.preventDefault();
        if (campaign) form.put(route('admin.landing-campaigns.update', campaign.id), { preserveScroll: true });
        else form.post(route('admin.landing-campaigns.store'), { preserveScroll: true, onSuccess: () => form.reset() });
    }

    return <Card as="section">
        <CardHeader title={campaign ? campaign.name : 'Nueva campaña de landing'} description="El código decide el descuento; aquí eliges qué casos vender y el mensaje que verá el anuncio." />
        <form onSubmit={submit} className="space-y-6">
            <div className="grid gap-4 lg:grid-cols-2">
                <TextField id={`campaign-name-${campaign?.id || 'new'}`} label="Nombre interno" value={form.data.name} onChange={(value) => form.setData('name', value)} error={form.errors.name} placeholder="TikTok noviembre" required />
                <SelectField id={`campaign-code-${campaign?.id || 'new'}`} label="Código promocional" value={form.data.promo_code_id} onChange={(value) => form.setData('promo_code_id', value)} error={form.errors.promo_code_id} options={[{ value: '', label: 'Elige un código activo' }, ...codes.map((code) => ({ value: String(code.id), label: `${code.code} — ${code.discount_type === 'percent' ? `${code.discount_value}%` : 'descuento fijo'}` }))]} />
                <TextField id={`campaign-eyebrow-${campaign?.id || 'new'}`} label="Texto corto del evento" value={form.data.eyebrow} onChange={(value) => form.setData('eyebrow', value)} error={form.errors.eyebrow} placeholder="EXCLUSIVO PARA TU EQUIPO" />
                <TextField id={`campaign-headline-${campaign?.id || 'new'}`} label="Título de la landing" value={form.data.headline} onChange={(value) => form.setData('headline', value)} error={form.errors.headline} placeholder="Un caso sin resolver. Tu equipo. El reloj corriendo." />
                <div className="lg:col-span-2"><TextField id={`campaign-description-${campaign?.id || 'new'}`} label="Descripción breve" value={form.data.description} onChange={(value) => form.setData('description', value)} error={form.errors.description} placeholder="Qué recibe quien compra y por qué vale la pena jugarlo." /></div>
            </div>

            <section className="border-t border-line pt-5">
                <h3 className="font-semibold text-ink">Los 3 casos pagos de la campaña</h3>
                <p className="mt-1 text-sm text-ink-muted">El primero recomendado ocupa el hero; los otros dos aparecen justo después. Selecciona exactamente tres.</p>
                {form.errors.paid_case_ids && <p className="mt-2 text-sm text-danger-strong">{form.errors.paid_case_ids}</p>}
                <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    {paid.map((item) => <label key={item.id} className={`rounded-control border p-3 text-sm ${form.data.paid_case_ids.includes(item.id) ? 'border-accent bg-accent-dim' : 'border-line'}`}>
                        <input type="checkbox" className="mr-2" checked={form.data.paid_case_ids.includes(item.id)} onChange={() => form.setData('paid_case_ids', toggle(form.data.paid_case_ids, item.id, 3))} />
                        <span className="font-medium text-ink">{item.name}</span><span className="mt-1 block text-xs text-ink-muted">{item.duration_minutes} min · {item.min_players}–{item.max_players} jugadores</span>
                    </label>)}
                </div>
                <SelectField id={`campaign-recommended-${campaign?.id || 'new'}`} label="Caso recomendado para empezar" value={form.data.recommended_case_id} onChange={(value) => form.setData('recommended_case_id', value)} error={form.errors.recommended_case_id} options={[{ value: '', label: 'Elige uno de los tres casos marcados' }, ...paid.filter((item) => form.data.paid_case_ids.includes(item.id)).map((item) => ({ value: String(item.id), label: item.name }))]} />
            </section>

            <section className="border-t border-line pt-5">
                <h3 className="font-semibold text-ink">Alternativa gratuita opcional</h3>
                <p className="mt-1 text-sm text-ink-muted">Aparece al final de la landing para quienes aún no quieren comprar. Máximo dos.</p>
                <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    {free.map((item) => <label key={item.id} className={`rounded-control border p-3 text-sm ${form.data.free_case_ids.includes(item.id) ? 'border-accent bg-accent-dim' : 'border-line'}`}>
                        <input type="checkbox" className="mr-2" checked={form.data.free_case_ids.includes(item.id)} onChange={() => form.setData('free_case_ids', toggle(form.data.free_case_ids, item.id, 2))} />
                        <span className="font-medium text-ink">{item.name}</span><span className="mt-1 block text-xs text-ink-muted">Gratis · {item.duration_minutes} min</span>
                    </label>)}
                </div>
            </section>

            <div className="flex flex-wrap items-center justify-between gap-3 border-t border-line pt-5">
                <CheckboxField id={`campaign-active-${campaign?.id || 'new'}`} label="Campaña activa" checked={form.data.active} onChange={(value) => form.setData('active', value)} hint="Solo las campañas activas reemplazan la landing genérica del código." />
                <Button type="submit" loading={form.processing}>{campaign ? 'Guardar campaña' : 'Crear campaña'}</Button>
            </div>
        </form>
    </Card>;
}

function CampaignMetrics({ metrics }) {
    return <div className="grid grid-cols-2 gap-3 lg:grid-cols-5"><StatTile label="Vistas" value={metrics.views} /><StatTile label="Clics a pago" value={metrics.paid_clicks} /><StatTile label="Registros" value={metrics.registrations} /><StatTile label="Checkouts" value={metrics.checkout_starts} /><StatTile label="Ventas con código" value={metrics.sales} /><StatTile label="Casos gratis" value={metrics.free_claims} /><StatTile label="Partidas iniciadas" value={metrics.free_started} /><StatTile label="Partidas terminadas" value={metrics.free_finished} /><StatTile label="Clics tras jugar" value={metrics.post_free_clicks} /><StatTile label="Gratis → compra (30d)" value={metrics.free_to_paid} /></div>;
}

export default function LandingCampaigns({ campaigns, promoCodes, cases }) {
    return <AppLayout current="admin.landing-campaigns" kicker="Mercadeo" title="Campañas de landing">
        <Head title="Campañas de landing" />
        <div className="space-y-6">
            <p className="max-w-3xl text-sm leading-relaxed text-ink-muted">Configura la página que ve una persona al abrir <code className="text-ink">/jugar?promo_code=…</code>. La oferta real siempre se valida nuevamente al llegar al checkout.</p>
            <CampaignForm promoCodes={promoCodes} cases={cases} />
            {campaigns.length === 0 ? <EmptyState title="Todavía no hay campañas" description="Crea una campaña para transformar un código de anuncios en una landing orientada a compra." /> : campaigns.map((campaign) => <div key={campaign.id} className="space-y-3"><div className="flex flex-wrap items-center justify-between gap-3"><div><h2 className="font-display text-2xl font-semibold text-ink">{campaign.name}</h2><p className="text-sm text-ink-muted">Código {campaign.promo_code}</p></div><div className="flex items-center gap-2"><Badge tone={campaign.active ? 'success' : 'neutral'}>{campaign.active ? 'Activa' : 'Pausada'}</Badge><Button variant="ghost" size="sm" onClick={() => router.patch(route('admin.landing-campaigns.toggle', campaign.id), {}, { preserveScroll: true })}>{campaign.active ? 'Pausar' : 'Activar'}</Button>{!campaign.active && <Button variant="ghost" size="sm" className="text-danger-strong" onClick={() => { if (window.confirm(`¿Eliminar la campaña ${campaign.name}? Sus métricas de campaña se borrarán.`)) router.delete(route('admin.landing-campaigns.destroy', campaign.id), {}, { preserveScroll: true }); }}>Eliminar</Button>}</div></div><CampaignMetrics metrics={campaign.metrics} /><CampaignForm campaign={campaign} promoCodes={promoCodes} cases={cases} /></div>)}
        </div>
    </AppLayout>;
}
