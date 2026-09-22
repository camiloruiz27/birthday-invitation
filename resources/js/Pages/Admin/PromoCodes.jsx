import { Head } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Badge from '../../components/ui/Badge';
import EmptyState from '../../components/ui/EmptyState';
import Meter from '../../components/ui/Meter';
import SectionHeader from '../../components/ui/SectionHeader';
import StatTile from '../../components/app/StatTile';
import { formatDateTime } from '../../lib/format';

const TYPE_LABELS = {
    gift: 'Regalo',
    discount: 'Descuento',
};

const STATUS_LABELS = {
    active: 'Activo',
    inactive: 'Inactivo',
    exhausted: 'Agotado',
};

const STATUS_TONE = {
    active: 'success',
    inactive: 'neutral',
    exhausted: 'danger',
};

function codeStatus(promo) {
    if (!promo.active) return 'inactive';
    if (promo.exhausted) return 'exhausted';

    return 'active';
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

function HowTo() {
    return (
        <Card as="section">
            <CardHeader
                title="Crear un código nuevo"
                description="Los códigos se siguen creando solo por consola, nunca desde esta pantalla."
            />

            <pre className="overflow-x-auto rounded-control bg-surface-sunken px-4 py-3 text-sm text-ink">
                <code>php artisan platform:create-promo-code CODIGO [opciones]</code>
            </pre>

            <dl className="mt-4 space-y-2 text-sm">
                {[
                    ['--case=', 'Regala acceso al caso con este slug.'],
                    ['--credits=', 'Regala esta cantidad de créditos (combinable con --case para el bundle).'],
                    ['--discount-percent=', 'Descuento porcentual sobre una compra real, de 1 a 100.'],
                    ['--discount-fixed=', 'Descuento de un monto fijo sobre una compra real.'],
                    ['--max-redemptions=', 'Tope total de usos entre todos. Se omite para no poner tope.'],
                    ['--max-per-user=', 'Tope de usos por persona. Por defecto 1; 0 quita el tope.'],
                    ['--note=', 'Recordatorio de para qué campaña es el código.'],
                ].map(([flag, description]) => (
                    <div key={flag} className="flex flex-wrap gap-x-2 gap-y-0.5">
                        <dt>
                            <code className="rounded bg-surface-sunken px-1.5 py-0.5 text-ink">{flag}</code>
                        </dt>
                        <dd className="text-ink-muted">{description}</dd>
                    </div>
                ))}
            </dl>

            <p className="mt-4 text-sm text-ink-muted">
                Un código es un <strong className="text-ink">regalo</strong> (
                <code className="text-ink">--case</code>/<code className="text-ink">--credits</code>) o un{' '}
                <strong className="text-ink">descuento</strong> (
                <code className="text-ink">--discount-percent</code>/
                <code className="text-ink">--discount-fixed</code>), nunca las dos cosas.
            </p>

            <div className="mt-4 space-y-2">
                <pre className="overflow-x-auto rounded-control bg-surface-sunken px-4 py-3 text-sm text-ink">
                    <code>
                        php artisan platform:create-promo-code LANZAMIENTO2026 --case=steve-jacobs
                        --credits=85 --max-redemptions=50 --note=&quot;Lanzamiento&quot;
                    </code>
                </pre>
                <pre className="overflow-x-auto rounded-control bg-surface-sunken px-4 py-3 text-sm text-ink">
                    <code>
                        php artisan platform:create-promo-code DESCUENTO20 --discount-percent=20
                        --max-per-user=1
                    </code>
                </pre>
            </div>

            <p className="mt-4 text-xs text-ink-subtle">
                Para revisarlos desde la terminal: <code>php artisan platform:list-promo-codes</code>.
            </p>
        </Card>
    );
}

export default function AdminPromoCodes({ metrics }) {
    const { summary, codes, recentRedemptions } = metrics;

    return (
        <AppLayout current="admin.codes" kicker="Administración" title="Códigos promocionales">
            <Head title="Códigos" />

            <div className="space-y-8">
                <HowTo />

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
                        description="Todos los códigos creados, sin importar si siguen activos."
                    />

                    {codes.length === 0 ? (
                        <EmptyState
                            title="Todavía no hay códigos"
                            description="Se crean con php artisan platform:create-promo-code."
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
                                        </p>

                                        <p className="mt-1 text-sm text-ink-subtle">
                                            <span className="tabular">{promo.redemptions_count}</span>
                                            {'/'}
                                            {promo.max_redemptions ?? '∞'} canjes ·{' '}
                                            {formatDateTime(promo.created_at)}
                                        </p>
                                    </li>
                                ))}
                            </ul>

                            <div className="hidden overflow-x-auto sm:block">
                                <table className="w-full min-w-176 text-left text-sm">
                                    <thead>
                                        <tr className="border-b border-line text-xs uppercase tracking-wide text-ink-muted">
                                            <th scope="col" className="py-2 pr-4 font-medium">Código</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Tipo</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Entrega</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Canjes</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Estado</th>
                                            <th scope="col" className="py-2 pr-4 font-medium">Nota</th>
                                            <th scope="col" className="py-2 font-medium">Creado</th>
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
                                                <td className="py-2.5 pr-4 text-ink-muted">{promo.grant}</td>
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
                                                <td className="py-2.5 pr-4">
                                                    <Badge tone={STATUS_TONE[codeStatus(promo)]}>
                                                        {STATUS_LABELS[codeStatus(promo)]}
                                                    </Badge>
                                                </td>
                                                <td className="max-w-40 truncate py-2.5 pr-4 text-ink-muted">
                                                    {promo.note || <span className="text-ink-subtle">—</span>}
                                                </td>
                                                <td className="whitespace-nowrap py-2.5 text-ink-muted">
                                                    {formatDateTime(promo.created_at)}
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
                            description="Aparecerán aquí en cuanto alguien use un código en /canjear."
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
