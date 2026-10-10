import { Link, useForm, usePage } from '@inertiajs/react';
import Alert from '../ui/Alert';
import Button from '../ui/Button';

/**
 * What the buy button of a case is, for whoever is looking at it. The one
 * place these states are decided, so the case page and the ad landing can
 * never disagree about where "buy" leads.
 *
 *   owner             → their panel, to create a game
 *   guest             → register, remembering which case they came for
 *   signed in, live   → the review screen (never straight to Bold)
 *   signed in, demo   → the simulated acquisition
 *   anything else     → an honest "not available yet"
 *
 * `guestLabel` / `buyLabel` let a campaign page word the button its own way;
 * the defaults are the case page's.
 */
export default function PurchaseCta({
    mysteryCase,
    owned,
    canPurchase,
    canSimulatePurchase,
    guestLabel = 'Crear cuenta para adquirirlo',
    buyLabel = 'Comprar',
    size = 'md',
    className = '',
}) {
    const { auth } = usePage().props;
    const { post, processing } = useForm({});

    // The simulated stand-in acquires immediately — there is nothing to
    // confirm about a purchase that costs nothing. A real purchase goes to
    // the review screen first (see Payments/Review), never straight here.
    function acquireSimulated() {
        post(route('cases.acquire', mysteryCase.slug));
    }

    if (owned) {
        return (
            <Button href={route('dashboard')} size={size} fullWidth className={className}>
                Crear una partida
            </Button>
        );
    }

    if (!auth?.user) {
        return (
            <>
                {/* ?case= is how registering remembers what the visitor came
                    to buy: afterwards they land on its checkout, not the
                    panel (RegisteredUserController). */}
                <Button
                    href={route('register', { case: mysteryCase.slug })}
                    size={size}
                    fullWidth
                    className={className}
                >
                    {guestLabel}
                </Button>
                <p className="flex min-h-11 flex-wrap items-center justify-center gap-x-1.5 text-center text-sm text-ink-muted">
                    ¿Ya tienes cuenta?{' '}
                    {/* Same ?case= as the button above: a customer coming back
                        lands on this case's checkout, not on the panel. */}
                    <Link
                        href={route('login', { case: mysteryCase.slug })}
                        className="inline-flex min-h-11 items-center px-1 text-accent underline"
                    >
                        Ingresar
                    </Link>
                </p>
            </>
        );
    }

    if (canPurchase) {
        return (
            <Button
                href={route('cases.checkout.review', mysteryCase.slug)}
                size={size}
                fullWidth
                className={className}
            >
                {buyLabel}
            </Button>
        );
    }

    if (canSimulatePurchase) {
        return (
            <>
                <Button onClick={acquireSimulated} loading={processing} size={size} fullWidth className={className}>
                    {processing ? 'Añadiendo…' : 'Añadir a mi biblioteca'}
                </Button>
                {/* Never let a simulated acquisition look like a real
                    purchase. */}
                <Alert variant="warning" className="mt-4 mb-0">
                    Adquisición simulada: la pasarela de pagos todavía no está integrada, así que
                    esto no cobra nada.
                </Alert>
            </>
        );
    }

    return (
        <Alert variant="info" className="mb-0">
            La compra en línea todavía no está disponible. Escríbenos y te damos acceso.
        </Alert>
    );
}
