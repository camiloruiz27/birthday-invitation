import { Head, router, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Accordion from '../../components/ui/Accordion';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import EmptyState from '../../components/ui/EmptyState';
import Meter from '../../components/ui/Meter';
import SectionHeader from '../../components/ui/SectionHeader';
import { CheckboxField, SelectField, TextField } from '../../components/ui/Field';
import StatTile from '../../components/app/StatTile';
import { formatDateTime, formatPrice } from '../../lib/format';

const TYPE_LABELS = {
    gift: 'Regalo',
    discount: 'Descuento',
};

const STATUS_LABELS = {
    active: 'Activo',
    inactive: 'Inactivo',
    exhausted: 'Agotado',
    expired: 'Vencido',
};

const STATUS_TONE = {
    active: 'success',
    inactive: 'neutral',
    exhausted: 'danger',
    expired: 'danger',
};

const APPLIES_LABELS = {
    case: 'Solo casos',
    credits: 'Solo créditos',
};

function codeStatus(promo) {
    if (!promo.active) return 'inactive';
    if (promo.expired) return 'expired';
    if (promo.exhausted) return 'exhausted';

    return 'active';
}

/** What a code is limited to; gifts are never limited by purchase type. */
function appliesLabel(promo) {
    if (promo.type !== 'discount') return null;

    return APPLIES_LABELS[promo.applies_to] ?? 'Casos y créditos';
}

function formatDate(value) {
    return value ? formatDateTime(value) : null;
}

/** Only the alert colouring is specific to this page; the tile is shared. */
function Metric({ label, value, hint, tone = 'default' }) {
    return (
        <StatTile
            label={label}
            value={value}
            hint={hint}
            tone={tone === 'alert' && value > 0 ? 'alert' : 'default'}
        />
    );
}

function toggleCode(promo) {
    router.patch(route('admin.codes.toggle', promo.id), {}, { preserveScroll: true });
}

function ToggleButton({ promo }) {
    return (
        <Button variant="ghost" size="sm" onClick={() => toggleCode(promo)}>
            {promo.active ? 'Desactivar' : 'Activar'}
        </Button>
    );
}

const INITIAL = {
    mode: 'single',
    code: '',
    prefix: '',
    count: '10',
    kind: 'discount',
    discount_type: 'percent',
    discount_value: '',
    applies_to: '',
    grants_case_slug: '',
    grants_credits: '',
    expires_at: '',
    max_redemptions: '',
    max_per_user: '1',
    note: '',
};

/** Marks a gift that lets the redeemer pick the case. */
const ANY_CASE = '__any';

function CreateCodeForm({ cases }) {
    const form = useForm(INITIAL);
    const { data, setData, errors, processing } = form;
    const isBatch = data.mode === 'batch';
    const isDiscount = data.kind === 'discount';

    function submit(event) {
        event.preventDefault();

        form.transform((current) => {
            const payload = { ...current };

            // The select carries "fixed case", "any case" or nothing in one
            // value; the server wants them as two fields.
            payload.grants_any_case = current.grants_case_slug === ANY_CASE;
            if (payload.grants_any_case) payload.grants_case_slug = '';

            return payload;
        });

        form.post(route('admin.codes.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset('code', 'prefix', 'discount_value', 'grants_credits', 'note'),
        });
    }

    return (
        <Card as="section">
            <CardHeader
                title="Crear códigos"
                description="Un código compartido o un lote de un solo uso. Un código es un descuento o un regalo, nunca los dos."
            />

            <form onSubmit={submit} className="space-y-5">
                <div className="grid gap-4 sm:grid-cols-2">
                    <SelectField
                        id="code-mode"
                        label="Qué crear"
                        value={data.mode}
                        onChange={(value) => setData('mode', value)}
                        options={[
                            { value: 'single', label: 'Un código (lo comparten varias personas)' },
                            { value: 'batch', label: 'Un lote (uno por persona, de un solo uso)' },
                        ]}
                        error={errors.mode}
                    />

                    <SelectField
                        id="code-kind"
                        label="Tipo"
                        value={data.kind}
                        onChange={(value) => setData('kind', value)}
                        options={[
                            { value: 'discount', label: 'Descuento sobre una compra' },
                            { value: 'gift', label: 'Regalo (caso y/o créditos gratis)' },
                        ]}
                        error={errors.kind}
                    />
                </div>

                {isBatch ? (
                    <div className="grid gap-4 sm:grid-cols-2">
                        <TextField
                            id="code-prefix"
                            label="Prefijo"
                            value={data.prefix}
                            onChange={(value) => setData('prefix', value.toUpperCase())}
                            error={errors.prefix}
                            hint="Cada código sale como PREFIJO-XXXXXX."
                            placeholder="TIKTOK"
                            required
                        />
                        <TextField
                            id="code-count"
                            label="Cuántos"
                            type="number"
                            min="1"
                            max="500"
                            value={data.count}
                            onChange={(value) => setData('count', value)}
                            error={errors.count}
                            hint="Hasta 500."
                            required
                        />
                    </div>
                ) : (
                    <TextField
                        id="code-code"
                        label="Código"
                        value={data.code}
                        onChange={(value) => setData('code', value.toUpperCase())}
                        error={errors.code}
                        hint="Letras, números, guion y guion bajo. Es lo que escribe la persona, o va en el enlace del anuncio como ?promo_code=."
                        placeholder="TIKTOK20"
                        required
                    />
                )}

                {isDiscount ? (
                    <div className="grid gap-4 sm:grid-cols-3">
                        <SelectField
                            id="code-discount-type"
                            label="Descuento"
                            value={data.discount_type}
                            onChange={(value) => setData('discount_type', value)}
                            options={[
                                { value: 'percent', label: 'Porcentaje (%)' },
                                { value: 'fixed', label: 'Monto fijo (COP)' },
                            ]}
                            error={errors.discount_type}
                        />
                        <TextField
                            id="code-discount-value"
                            label={data.discount_type === 'percent' ? 'Porcentaje' : 'Monto en COP'}
                            type="number"
                            min="1"
                            max={data.discount_type === 'percent' ? '100' : undefined}
                            value={data.discount_value}
                            onChange={(value) => setData('discount_value', value)}
                            error={errors.discount_value}
                            hint={data.discount_type === 'percent' ? '100 deja la compra gratis.' : undefined}
                            required
                        />
                        <SelectField
                            id="code-applies-to"
                            label="Sirve para"
                            value={data.applies_to}
                            onChange={(value) => setData('applies_to', value)}
                            options={[
                                { value: '', label: 'Casos y créditos' },
                                { value: 'case', label: 'Solo comprar casos' },
                                { value: 'credits', label: 'Solo recargar créditos de IA' },
                            ]}
                            error={errors.applies_to}
                        />
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2">
                        <SelectField
                            id="code-case"
                            label="Caso que regala"
                            value={data.grants_case_slug}
                            onChange={(value) => setData('grants_case_slug', value)}
                            options={[
                                { value: '', label: 'Ninguno' },
                                { value: ANY_CASE, label: 'A elección de quien lo canjea' },
                                ...cases.map((item) => ({ value: item.slug, label: item.name })),
                            ]}
                            error={errors.grants_case_slug}
                        />
                        <TextField
                            id="code-credits"
                            label="Créditos que regala"
                            type="number"
                            min="1"
                            value={data.grants_credits}
                            onChange={(value) => setData('grants_credits', value)}
                            error={errors.grants_credits}
                            hint="Opcional. Se pueden dar un caso, créditos o los dos."
                        />
                    </div>
                )}

                <div className="grid gap-4 sm:grid-cols-3">
                    <TextField
                        id="code-expires"
                        label="Vence el"
                        type="date"
                        value={data.expires_at}
                        onChange={(value) => setData('expires_at', value)}
                        error={errors.expires_at}
                        hint="Sirve hasta el final de ese día. Vacío = no vence."
                    />
                    {!isBatch && (
                        <>
                            <TextField
                                id="code-max"
                                label="Usos en total"
                                type="number"
                                min="1"
                                value={data.max_redemptions}
                                onChange={(value) => setData('max_redemptions', value)}
                                error={errors.max_redemptions}
                                hint="Vacío = sin tope."
                            />
                            <TextField
                                id="code-per-user"
                                label="Usos por persona"
                                type="number"
                                min="0"
                                value={data.max_per_user}
                                onChange={(value) => setData('max_per_user', value)}
                                error={errors.max_per_user}
                                hint="0 = sin tope."
                            />
                        </>
                    )}
                </div>

                <TextField
                    id="code-note"
                    label="Nota"
                    value={data.note}
                    onChange={(value) => setData('note', value)}
                    error={errors.note}
                    hint="Un recordatorio de para qué campaña es."
                />

                <Button type="submit" loading={processing}>
                    {isBatch ? 'Crear lote' : 'Crear código'}
                </Button>
            </form>

            <div className="mt-6">
                <Accordion summary="Crear desde la terminal">
                    <p className="text-sm text-ink-muted">
                        Lo mismo se puede hacer con <code className="text-ink">php artisan platform:create-promo-code</code>{' '}
                        y <code className="text-ink">platform:create-promo-code-batch</code>, que ahora también
                        aceptan <code className="text-ink">--expires=2026-11-30</code> y{' '}
                        <code className="text-ink">--applies-to=case|credits</code>.
                    </p>
                </Accordion>
            </div>
        </Card>
    );
}

function CreatedBatch({ codes }) {
    if (!codes?.length) return null;

    return (
        <Card as="section">
            <CardHeader
                title={`Lote creado (${codes.length})`}
                description="Cópialos ahora: esta lista solo se muestra una vez. Siempre puedes verlos en la tabla de abajo."
            />
            <textarea
                readOnly
                rows={Math.min(codes.length, 12)}
                value={codes.join('\n')}
                onFocus={(event) => event.target.select()}
                className="w-full rounded-control border border-line-strong bg-surface-sunken px-3 py-2.5 font-mono text-sm text-ink"
                aria-label="Códigos del lote"
            />
        </Card>
    );
}

export default function AdminPromoCodes({ metrics, cases = [], created }) {
    const { summary, codes, recentRedemptions } = metrics;

    return (
        <AppLayout current="admin.codes" kicker="Administración" title="Códigos promocionales">
            <Head title="Códigos" />

            <div className="space-y-8">
                <CreatedBatch codes={created} />

                <CreateCodeForm cases={cases} />

                <section>
                    <SectionHeader title="Resumen" />
                    <div className="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                        <Metric label="Códigos creados" value={summary.total} />
                        <Metric label="Activos" value={summary.active} />
                        <Metric label="Agotados" value={summary.exhausted} tone="alert" />
                        <Metric label="Canjes totales" value={summary.total_redemptions} />
                    </div>
                    <p className="mt-3 text-xs text-ink-subtle">
                        {summary.gifts} de regalo, {summary.discounts} de descuento.
                    </p>
                </section>

                <Card as="section">
                    <CardHeader
                        title="Códigos"
                        description="Todos los códigos creados. «Descontado» es lo que el código ha quitado de compras ya pagadas."
                    />

                    {codes.length === 0 ? (
                        <EmptyState
                            title="Todavía no hay códigos"
                            description="Crea el primero con el formulario de arriba."
                        />
                    ) : (
                        <>
                            <ul className="divide-y divide-line sm:hidden">
                                {codes.map((promo) => (
                                    <li key={promo.id} className="py-3">
                                        <div className="flex items-start justify-between gap-3">
                                            <p className="min-w-0 truncate font-medium text-ink">
                                                {promo.code}
                                            </p>
                                            <span className="shrink-0">
                                                <Badge tone={STATUS_TONE[codeStatus(promo)]}>
                                                    {STATUS_LABELS[codeStatus(promo)]}
                                                </Badge>
                                            </span>
                                        </div>

                                        <p className="mt-1 truncate text-sm text-ink-muted">
                                            {TYPE_LABELS[promo.type]} · {promo.grant}
                                            {appliesLabel(promo) && ` · ${appliesLabel(promo)}`}
                                        </p>

                                        <p className="mt-1 text-sm text-ink-subtle">
                                            <span className="tabular">{promo.redemptions_count}</span>
                                            {'/'}
                                            {promo.max_redemptions ?? '∞'} canjes
                                            {promo.expires_at && ` · vence ${formatDate(promo.expires_at)}`}
                                        </p>

                                        {promo.paid_orders > 0 && (
                                            <p className="mt-1 text-sm text-ink-subtle">
                                                {promo.paid_orders} compras · descontado{' '}
                                                {formatPrice(promo.discounted, 'COP')}
                                            </p>
                                        )}

                                        <div className="mt-2">
                                            <ToggleButton promo={promo} />
                                        </div>
                                    </li>
                                ))}
                            </ul>

                            <div className="hidden overflow-x-auto sm:block">
                                <table className="w-full min-w-4xl text-left text-sm">
                                    <thead>
                                        <tr className="border-b border-line text-xs uppercase tracking-wide text-ink-muted">
                                            <th scope="col" className="py-2 pr-4 font-medium">Código</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Tipo</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Entrega</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Canjes</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Vence</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Descontado</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Estado</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Nota</th>
                                            <th scope="col" className="py-2 font-medium">
                                                <span className="sr-only">Acciones</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-line">
                                        {codes.map((promo) => (
                                            <tr key={promo.id}>
                                                <th scope="row" className="py-2.5 pr-4 font-medium text-ink">
                                                    {promo.code}
                                                </th>
                                                <td className="py-2.5 pr-4 text-ink-muted">
                                                    {TYPE_LABELS[promo.type]}
                                                </td>
                                                <td className="py-2.5 pr-4 text-ink-muted">
                                                    {promo.grant}
                                                    {appliesLabel(promo) && (
                                                        <span className="block text-xs text-ink-subtle">
                                                            {appliesLabel(promo)}
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="max-w-32 py-2.5 pr-4 text-ink-muted">
                                                    <span className="tabular">
                                                        {promo.redemptions_count}/{promo.max_redemptions ?? '∞'}
                                                    </span>
                                                    {promo.max_redemptions !== null && (
                                                        <Meter
                                                            value={promo.redemptions_count}
                                                            max={promo.max_redemptions}
                                                            tone={promo.exhausted ? 'danger' : 'accent'}
                                                            label={`Canjes de ${promo.code}`}
                                                            className="mt-1"
                                                        />
                                                    )}
                                                </td>
                                                <td className="whitespace-nowrap py-2.5 pr-4 text-ink-muted">
                                                    {formatDate(promo.expires_at) ?? (
                                                        <span className="text-ink-subtle">—</span>
                                                    )}
                                                </td>
                                                <td className="whitespace-nowrap py-2.5 pr-4 text-ink-muted">
                                                    {promo.paid_orders > 0 ? (
                                                        <>
                                                            <span className="tabular">
                                                                {formatPrice(promo.discounted, 'COP')}
                                                            </span>
                                                            <span className="block text-xs text-ink-subtle">
                                                                {promo.paid_orders} compras
                                                            </span>
                                                        </>
                                                    ) : (
                                                        <span className="text-ink-subtle">—</span>
                                                    )}
                                                </td>
                                                <td className="py-2.5 pr-4">
                                                    <Badge tone={STATUS_TONE[codeStatus(promo)]}>
                                                        {STATUS_LABELS[codeStatus(promo)]}
                                                    </Badge>
                                                </td>
                                                <td className="max-w-40 truncate py-2.5 pr-4 text-ink-muted">
                                                    {promo.note || <span className="text-ink-subtle">—</span>}
                                                </td>
                                                <td className="whitespace-nowrap py-2.5 text-right">
                                                    <ToggleButton promo={promo} />
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </>
                    )}
                </Card>

                <Card as="section">
                    <CardHeader
                        title="Canjes recientes"
                        description="Los últimos 10, más reciente primero."
                    />

                    {recentRedemptions.length === 0 ? (
                        <EmptyState
                            title="Todavía no hay canjes"
                            description="Aparecerán aquí en cuanto alguien use un código."
                        />
                    ) : (
                        <>
                            <ul className="divide-y divide-line sm:hidden">
                                {recentRedemptions.map((redemption) => (
                                    <li key={redemption.id} className="py-3">
                                        <div className="flex items-start justify-between gap-3">
                                            <p className="min-w-0 truncate font-medium text-ink">
                                                {redemption.code}
                                            </p>
                                            <Badge tone={redemption.has_order ? 'accent' : 'neutral'}>
                                                {redemption.has_order ? 'Con compra' : 'Sin compra'}
                                            </Badge>
                                        </div>

                                        <p className="mt-1 truncate text-sm text-ink-muted">
                                            {redemption.user_name} · {redemption.user_email}
                                        </p>

                                        <p className="mt-1 text-sm text-ink-subtle">
                                            {formatDateTime(redemption.created_at)}
                                        </p>
                                    </li>
                                ))}
                            </ul>

                            <div className="hidden overflow-x-auto sm:block">
                                <table className="w-full min-w-140 text-left text-sm">
                                    <thead>
                                        <tr className="border-b border-line text-xs uppercase tracking-wide text-ink-muted">
                                            <th scope="col" className="py-2 pr-4 font-medium">Código</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Usuario</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Compra</th>
                                            <th scope="col" className="py-2 font-medium">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-line">
                                        {recentRedemptions.map((redemption) => (
                                            <tr key={redemption.id}>
                                                <th scope="row" className="py-2.5 pr-4 font-medium text-ink">
                                                    {redemption.code}
                                                </th>
                                                <td className="max-w-56 py-2.5 pr-4 text-ink-muted">
                                                    <p className="truncate">{redemption.user_name}</p>
                                                    <p className="truncate text-xs text-ink-subtle">
                                                        {redemption.user_email}
                                                    </p>
                                                </td>
                                                <td className="py-2.5 pr-4">
                                                    <Badge tone={redemption.has_order ? 'accent' : 'neutral'}>
                                                        {redemption.has_order ? 'Sí' : 'No'}
                                                    </Badge>
                                                </td>
                                                <td className="whitespace-nowrap py-2.5 text-ink-muted">
                                                    {formatDateTime(redemption.created_at)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </>
                    )}
                </Card>
            </div>
        </AppLayout>
    );
}
