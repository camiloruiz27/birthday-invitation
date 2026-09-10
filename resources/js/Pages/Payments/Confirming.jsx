import { useEffect, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import Card from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import Spinner from '../../components/ui/Spinner';
import usePoll from '../../hooks/usePoll';
import TextLink from '../../components/ui/TextLink';

/**
 * Where Bold sends the buyer back after checkout.
 *
 * Every load of this page (the initial one, and every poll while pending)
 * makes the backend actively re-check the order against Bold directly — not
 * just wait for its webhook, which can be slow, misconfigured, or never
 * arrive. See PaymentCallbackController for why.
 */
export default function Confirming({ order }) {
    const [waitedLong, setWaitedLong] = useState(false);

    usePoll(['order'], { interval: 3000, enabled: order.status === 'pending' });

    useEffect(() => {
        if (order.status !== 'pending') return undefined;

        const timer = setTimeout(() => setWaitedLong(true), 45000);

        return () => clearTimeout(timer);
    }, [order.status]);

    useEffect(() => {
        // Credit purchases stay put so the buyer can actually read the new
        // balance instead of being whisked away from it; a case purchase
        // still jumps straight to the case, which is confirmation enough.
        if (order.status !== 'approved' || order.type !== 'case' || !order.case_slug) return undefined;

        const timer = setTimeout(() => router.visit(route('cases.show', order.case_slug)), 1500);

        return () => clearTimeout(timer);
    }, [order.status, order.type, order.case_slug]);

    return (
        <AppLayout current={order.type === 'case' ? 'library' : 'credits'} title="Confirmando tu pago">
            <Head title="Confirmando tu pago" />

            <Card className="mx-auto max-w-md py-10 text-center">
                {order.status === 'pending' && (
                    <>
                        <Spinner size="lg" className="mx-auto text-accent" label={null} />
                        <h1 className="mt-5 text-lg font-semibold text-ink">
                            Confirmando tu pago
                        </h1>
                        <p className="mt-2 text-sm text-ink-muted">
                            Bold nos está avisando del resultado. Normalmente toma unos segundos —
                            no cierres esta página.
                        </p>
                        {waitedLong && (
                            <p className="mt-4 text-xs text-ink-subtle">
                                Está tardando más de lo normal. Si el pago se completó, te llega
                                un correo de confirmación en cuanto lo procesemos; si no, puedes
                                cerrar esta página e intentarlo de nuevo.
                            </p>
                        )}
                    </>
                )}

                {order.status === 'approved' && (
                    <>
                        <p className="text-3xl" aria-hidden="true">✓</p>
                        <h1 className="mt-3 text-lg font-semibold text-ink">Pago confirmado</h1>

                        {order.type === 'case' ? (
                            <p className="mt-2 text-sm text-ink-muted">Ya está en tu biblioteca.</p>
                        ) : (
                            <>
                                <p className="mt-2 text-sm text-ink-muted">
                                    Se añadieron{' '}
                                    <span className="tabular font-semibold text-ink">
                                        {order.credits_granted}
                                    </span>{' '}
                                    créditos a tu cuenta.
                                </p>
                                {order.wallet_available !== null && (
                                    <p className="tabular mt-4 text-3xl font-semibold text-ink">
                                        {order.wallet_available}
                                        <span className="ml-1.5 text-sm font-normal text-ink-muted">
                                            disponibles ahora
                                        </span>
                                    </p>
                                )}
                            </>
                        )}

                        <Button
                            href={
                                order.type === 'case' && order.case_slug
                                    ? route('cases.show', order.case_slug)
                                    : route('credits')
                            }
                            className="mt-6"
                        >
                            {order.type === 'case' ? 'Ver el caso' : 'Ver mis créditos'}
                        </Button>
                    </>
                )}

                {order.status === 'rejected' && (
                    <>
                        <h1 className="text-lg font-semibold text-ink">El pago no pasó</h1>
                        <p className="mt-2 text-sm text-ink-muted">
                            Bold rechazó la transacción. No se hizo ningún cobro. Puedes
                            intentarlo de nuevo con otra tarjeta.
                        </p>
                        <Button href={route(order.type === 'case' ? 'cases.index' : 'credits')} className="mt-6">
                            Intentar de nuevo
                        </Button>
                    </>
                )}

                {(order.status === 'voided' || order.status === 'expired') && (
                    <>
                        <h1 className="text-lg font-semibold text-ink">Esta orden ya no está activa</h1>
                        <p className="mt-2 text-sm text-ink-muted">
                            Si esperabas un cargo o una devolución, escríbenos con el número de
                            orden #{order.id}.
                        </p>
                        <TextLink href={route('dashboard')} className="mt-6 inline-block text-sm">
                            Volver al panel
                        </TextLink>
                    </>
                )}
            </Card>
        </AppLayout>
    );
}
