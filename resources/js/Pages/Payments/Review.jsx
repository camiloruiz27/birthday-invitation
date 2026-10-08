import { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import Alert from '../../components/ui/Alert';
import TextLink from '../../components/ui/TextLink';
import { CheckboxField, TextField } from '../../components/ui/Field';
import { formatPrice } from '../../lib/format';

/**
 * The stop between "I want this" and Bold's own payment page. Every real
 * purchase (a case or a credit package) lands here first — what you're
 * buying, any discount applied, the actual total — before the confirm button
 * below ever creates an Order and leaves the site.
 *
 * The discount code lives only here, not on the catalog or credits page:
 * typing it and seeing the total change immediately is the whole point of
 * this screen existing.
 */
export default function Review({
    alreadyOwned,
    case_slug,
    type,
    package_id,
    title,
    subtitle,
    original_amount,
    currency,
    promo_code,
    discount_amount,
    final_amount,
    is_gift,
    gift_credits,
    promo_error,
    promo_redirect,
}) {
    const isCase = type === 'case';

    // Starts unticked on every visit: accepting is an act, not a default.
    const [acceptedTerms, setAcceptedTerms] = useState(false);

    const codeForm = useForm(
        isCase
            ? { promo_code: promo_code || '' }
            : { promo_code: promo_code || '', package: package_id }
    );

    const confirmForm = useForm(
        isCase ? { promo_code: promo_code || '' } : { promo_code: promo_code || '', package: package_id }
    );

    function reviewUrl() {
        return isCase ? route('cases.checkout.review', case_slug) : route('credits.checkout.review');
    }

    function applyCode(event) {
        event.preventDefault();
        codeForm.get(reviewUrl(), { preserveScroll: true, preserveState: true });
    }

    function removeCode() {
        // Not codeForm.get(): useForm's get() always sends whatever is
        // currently in its own `data` state at the time this render's
        // closure was created, so a setData() right before it would still
        // submit the OLD value — router.get() lets the query be built
        // explicitly instead.
        router.get(
            reviewUrl(),
            isCase ? {} : { package: package_id },
            { preserveScroll: true, preserveState: true }
        );
    }

    function confirmPurchase(event) {
        event.preventDefault();
        confirmForm.post(isCase ? route('cases.acquire', case_slug) : route('credits.purchase'));
    }

    if (alreadyOwned) {
        return (
            <AppLayout current="library" title="Ya tienes este caso">
                <Head title="Ya tienes este caso" />
                <Card as="section" className="mx-auto max-w-md text-center">
                    <p className="text-sm text-ink-muted">Este caso ya está en tu biblioteca.</p>
                    <Button href={route('cases.show', case_slug)} className="mt-4">
                        Ver el caso
                    </Button>
                </Card>
            </AppLayout>
        );
    }

    const codeApplied = Boolean(promo_code && discount_amount);

    return (
        <AppLayout current={isCase ? 'library' : 'credits'} kicker="Resumen" title="Confirma tu compra">
            <Head title="Confirma tu compra" />

            <Card as="section" className="mx-auto max-w-md">
                <CardHeader title={title} description={subtitle} />

                <dl className="mt-2 space-y-2 border-t border-line pt-4 text-sm">
                    <div className="flex items-baseline justify-between">
                        <dt className="text-ink-muted">Precio</dt>
                        <dd className="tabular text-ink">{formatPrice(original_amount, currency)}</dd>
                    </div>

                    {codeApplied && (
                        <div className="flex items-baseline justify-between">
                            <dt className="text-ink-muted">
                                {is_gift ? `Regalo ${promo_code}` : `Código ${promo_code}`}
                            </dt>
                            <dd className="tabular text-success-strong">
                                -{formatPrice(discount_amount, currency)}
                            </dd>
                        </div>
                    )}

                    <div className="flex items-baseline justify-between border-t border-line pt-2">
                        <dt className="font-semibold text-ink">Total</dt>
                        <dd className="tabular text-xl font-semibold text-ink">
                            {formatPrice(final_amount, currency)}
                        </dd>
                    </div>
                </dl>

                {/* The code, applied via its own small GET request — no
                    payment happens until "Pagar con tarjeta" below. */}
                <div className="mt-6 border-t border-line pt-6">
                    {codeApplied ? (
                        <div className="flex items-center justify-between text-sm">
                            <span className="text-ink-muted">
                                Código <span className="font-medium text-ink">{promo_code}</span> aplicado
                            </span>
                            <button
                                type="button"
                                onClick={removeCode}
                                className="text-accent underline"
                            >
                                Quitar
                            </button>
                        </div>
                    ) : (
                        <form onSubmit={applyCode} className="flex items-end gap-3">
                            <div className="flex-1">
                                <TextField
                                    id="promo_code"
                                    label={
                                        isCase
                                            ? '¿Tienes un código de descuento o de regalo?'
                                            : '¿Tienes un código de descuento?'
                                    }
                                    value={codeForm.data.promo_code}
                                    onChange={(value) => codeForm.setData('promo_code', value.toUpperCase())}
                                    error={promo_error}
                                    placeholder={isCase ? 'TU-CÓDIGO' : 'DESCUENTO20'}
                                    autoComplete="off"
                                />
                            </div>
                            <Button
                                type="submit"
                                variant="secondary"
                                loading={codeForm.processing}
                                disabled={!codeForm.data.promo_code}
                            >
                                Aplicar
                            </Button>
                        </form>
                    )}

                    {promo_redirect && (
                        <p className="mt-2 text-sm text-ink-muted">
                            <Link href={promo_redirect} className="text-accent underline">
                                Ir a "Canjear código"
                            </Link>
                        </p>
                    )}
                </div>

                {final_amount === 0 && (
                    <Alert variant="status" className="mt-6 mb-0">
                        {is_gift
                            ? 'Tu código de regalo cubre este caso — no se te cobrará nada.'
                            : 'Tu código cubre el 100% — no se te cobrará nada.'}
                        {is_gift && gift_credits > 0 && ` Además recibirás ${gift_credits} créditos.`}
                    </Alert>
                )}

                <form onSubmit={confirmPurchase} className="mt-6 space-y-4">
                    {/* What Ley 1480 de 2011 (art. 50) wants the buyer told
                        before paying — payment, delivery and the withdrawal
                        right with its procedure — lives in the Terms
                        (#precios, #retracto); here the buyer only accepts it,
                        with a link to each part. New tab, so the review and
                        any code already applied survive. The native `required`
                        stops the submit with the browser's own message, so the
                        button never looks dead. Not sent to the server. */}
                    <CheckboxField
                        id="accept_purchase_terms"
                        label={
                            <>
                                Acepto las condiciones de esta compra:{' '}
                                <TextLink
                                    href={`${route('terms')}#precios`}
                                    external
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    pago y entrega
                                </TextLink>{' '}
                                y{' '}
                                <TextLink
                                    href={`${route('terms')}#retracto`}
                                    external
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    derecho de retracto
                                </TextLink>
                                , según los{' '}
                                <TextLink href={route('terms')} external target="_blank" rel="noopener noreferrer">
                                    Términos y Condiciones
                                </TextLink>
                                .
                            </>
                        }
                        checked={acceptedTerms}
                        onChange={setAcceptedTerms}
                        required
                    />

                    <Button type="submit" loading={confirmForm.processing} fullWidth>
                        {confirmForm.processing
                            ? 'Redirigiendo…'
                            : final_amount === 0
                              ? 'Confirmar'
                              : 'Pagar con tarjeta'}
                    </Button>
                </form>
            </Card>
        </AppLayout>
    );
}
