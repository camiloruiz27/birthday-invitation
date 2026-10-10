import { useEffect, useRef, useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AdLayout from '../../Layouts/AdLayout';
import Container from '../../components/ui/Container';
import Button from '../../components/ui/Button';
import Accordion from '../../components/ui/Accordion';
import Reveal from '../../components/ui/Reveal';
import Section from '../../components/public/Section';
import PurchaseCta from '../../components/public/PurchaseCta';
import { CaseFacts } from '../../components/public/CaseCard';
import { FAQ } from '../../components/public/faqItems';
import { trackAd } from '../../lib/analytics';
import { formatPrice, minorUnitValue } from '../../lib/format';

/**
 * The page paid ads land on, in two modes (AdLandingController):
 *
 *   case      /jugar/{slug}   one case, one price, one button
 *   platform  /jugar          the brand, and three cases to choose from
 *
 * Built for a stranger on a phone inside the TikTok or Instagram browser: the
 * headline, the price and the button are all in the first screen, nothing
 * above the fold waits on a scroll animation (<Reveal> keeps content invisible
 * until JavaScript has run), and nothing links away except to the next step.
 */

const STEPS = [
    {
        title: 'Eliges el caso y creas la partida',
        body: 'Compras el caso una sola vez y lo abres cuando quieras.',
    },
    {
        title: 'Tu equipo recibe el expediente',
        body: 'Cada jugador entra con su propio enlace, desde el correo. Sin cuentas ni apps.',
    },
    {
        title: 'Investigan, interrogan y acusan',
        body: 'La evidencia llega con el reloj corriendo. Al final, cada uno acusa a alguien.',
    },
];

/** "20% de descuento", "$10.000 de descuento" or "Caso de regalo". */
function offerHeadline(offer, currency = 'COP') {
    if (offer.kind === 'percent') return `${offer.value}% de descuento`;
    if (offer.kind === 'fixed') return `${formatPrice(offer.value, currency)} de descuento`;

    return 'Caso de regalo';
}

/**
 * What the ad promised, said on the page the ad lands on. The discount used to
 * show up only at the checkout, after signing up: someone who clicked an ad
 * saying "20% off" saw the full price here and had no way to know. The code is
 * already remembered (see Attribution) and applies by itself at the end, so
 * this only has to say so, and say it where the price is.
 */
function OfferStrip({ offer, currency, mysteryCase = null }) {
    const until = offer.expires_at
        ? new Date(offer.expires_at).toLocaleDateString('es-CO', { day: 'numeric', month: 'long' })
        : null;

    // Two wordings on purpose. On a phone the long sentence took three lines,
    // a fifth of the first screen, and pushed the title and the button down:
    // there it is one line of facts. From sm up it is the full sentence.
    // The short date for the one-line phone version ("15 oct").
    const untilShort = offer.expires_at
        ? new Date(offer.expires_at).toLocaleDateString('es-CO', { day: 'numeric', month: 'short' }).replace('.', '')
        : null;

    return (
        <div className="border-b border-accent bg-accent-dim px-4 py-2.5 text-center text-sm text-ink sm:py-3">
            {offer.kind === 'gift' ? (
                <>
                    <p className="sm:hidden">
                        <strong className="font-semibold">Caso de regalo</strong> · código {offer.code}
                    </p>
                    <p className="hidden sm:block">
                        <strong className="font-semibold">
                            Tienes {mysteryCase ? 'este caso' : 'un caso'} de regalo
                        </strong>{' '}
                        con el código {offer.code}. Crea tu cuenta y actívalo: no se te cobrará nada.
                    </p>
                </>
            ) : (
                <>
                    <p className="sm:hidden">
                        <strong className="font-semibold">{offerHeadline(offer, currency)}</strong> ·{' '}
                        {offer.code}
                        {untilShort && ` · hasta el ${untilShort}`}
                    </p>
                    <p className="hidden sm:block">
                        <strong className="font-semibold">{offerHeadline(offer, currency)}</strong> con el
                        código {offer.code}. Se aplica solo cuando vayas a pagar.
                        {until && ` Válido hasta el ${until}.`}
                    </p>
                </>
            )}
        </div>
    );
}

/** The list price, struck through, and what it comes to with the offer. */
function OfferPrice({ price, finalAmount, currency, className = '' }) {
    return (
        <span className={`inline-flex items-baseline gap-2 ${className}`}>
            <span className="text-base text-ink-subtle line-through">{price}</span>
            <span>{finalAmount === 0 ? 'Gratis' : formatPrice(finalAmount, currency)}</span>
        </span>
    );
}

function HowItWorks() {
    return (
        <Section kicker="Cómo se juega" title="Tres pasos">
            <ol className="grid gap-6 sm:grid-cols-3">
                {STEPS.map((step, index) => (
                    <li key={step.title} className="border-t-2 border-accent-dim pt-4">
                        <span className="font-mono text-sm text-accent">0{index + 1}</span>
                        <h3 className="mt-2 font-semibold text-ink">{step.title}</h3>
                        <p className="mt-2 text-sm leading-relaxed text-ink-muted">{step.body}</p>
                    </li>
                ))}
            </ol>
        </Section>
    );
}

function MiniFaq() {
    return (
        <Section kicker="Preguntas" title="Lo que suelen preguntar" width="prose" tone="sunken">
            <div className="space-y-3">
                {FAQ.slice(0, 4).map((item) => (
                    <Accordion key={item.question} summary={item.question}>
                        <p className="text-sm leading-relaxed text-ink-muted">{item.answer}</p>
                    </Accordion>
                ))}
            </div>
        </Section>
    );
}

/**
 * True once the element has scrolled out of view — what decides when the
 * fixed bottom bar appears: not while the main button is already on screen.
 */
function useScrolledPast(ref) {
    const [past, setPast] = useState(false);

    useEffect(() => {
        const node = ref.current;
        if (!node || typeof IntersectionObserver === 'undefined') return undefined;

        const observer = new IntersectionObserver(
            ([entry]) => setPast(!entry.isIntersecting && entry.boundingClientRect.top < 0),
            { threshold: 0 }
        );
        observer.observe(node);

        return () => observer.disconnect();
    }, [ref]);

    return past;
}

/** The fixed strip at the bottom of a phone: price and the same button. */
function StickyBar({ visible, children }) {
    return (
        <div
            aria-hidden={!visible}
            className={`fixed inset-x-0 bottom-0 z-40 border-t border-line-strong bg-surface-raised/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur transition-transform duration-200 sm:hidden ${
                visible ? 'translate-y-0' : 'translate-y-full'
            }`}
        >
            {children}
        </div>
    );
}

function CaseLanding({ mysteryCase, offer, owned, canPurchase, canSimulatePurchase, gamesPerCase }) {
    const heroCta = useRef(null);
    const showBar = useScrolledPast(heroCta);
    const price = formatPrice(mysteryCase.price_amount, mysteryCase.currency);
    const { ad } = mysteryCase;
    // Only when it really changes the price here; the strip shows either way.
    const discounted = offer && offer.final_amount !== null && offer.final_amount < mysteryCase.price_amount;

    // The button is where the person decides, so it carries the offer too.
    // (The bar pinned to the bottom of a phone keeps the plain label: the
    // struck price beside it already says it.)
    let ctaLabel = `${ad.cta} →`;

    if (discounted) {
        if (offer.kind === 'percent') {
            ctaLabel = `Quiero mi ${offer.value}% de descuento →`;
        } else if (offer.kind === 'fixed') {
            ctaLabel = `Quiero mi descuento de ${formatPrice(offer.value, mysteryCase.currency)} →`;
        } else {
            ctaLabel = 'Reclamar mi caso gratis →';
        }
    }

    // Tells the ad pixels which case this visitor looked at (a no-op unless
    // they accepted the marketing category). Same event as the case's own
    // page, so a campaign's view counts do not depend on which one it used.
    useEffect(() => {
        trackAd(
            'ViewContent',
            {
                contentId: mysteryCase.slug,
                contentName: mysteryCase.name,
                value: minorUnitValue(mysteryCase.price_amount, mysteryCase.currency),
                currency: mysteryCase.currency,
            },
            `view-${mysteryCase.slug}`
        );
    }, [mysteryCase.slug]);

    const cta = (
        <PurchaseCta
            mysteryCase={mysteryCase}
            owned={owned}
            canPurchase={canPurchase}
            canSimulatePurchase={canSimulatePurchase}
            guestLabel={ctaLabel}
            buyLabel={ctaLabel}
            size="lg"
        />
    );

    return (
        <AdLayout
            brandHref={route('ads.case', mysteryCase.slug)}
            bar={
                <StickyBar visible={showBar}>
                    <div className="flex items-center gap-3">
                        <div className="shrink-0">
                            <p className="text-lg font-semibold leading-tight text-ink">
                                {discounted ? (
                                    <OfferPrice
                                        price={price}
                                        finalAmount={offer.final_amount}
                                        currency={mysteryCase.currency}
                                    />
                                ) : (
                                    price
                                )}
                            </p>
                            <p className="text-xs text-ink-muted">Pago único</p>
                        </div>
                        <div className="min-w-0 flex-1">
                            <PurchaseCta
                                mysteryCase={mysteryCase}
                                owned={owned}
                                canPurchase={canPurchase}
                                canSimulatePurchase={canSimulatePurchase}
                                guestLabel={ad.cta}
                                buyLabel={ad.cta}
                            />
                        </div>
                    </div>
                </StickyBar>
            }
        >
            <Head title={mysteryCase.name} />

            {offer && <OfferStrip offer={offer} currency={mysteryCase.currency} mysteryCase={mysteryCase} />}

            {/* First screen: what it is, what it costs, the button. */}
            <header className="border-b border-line">
                <Container width="wide" className="grid gap-6 py-5 sm:gap-8 sm:py-14 lg:grid-cols-2 lg:items-center lg:gap-12">
                    <div>
                        <p className="text-xs font-medium uppercase tracking-widest text-accent">
                            Misterio para resolver en equipo
                        </p>
                        <h1 className="mt-3 font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ink sm:text-6xl">
                            {mysteryCase.name}
                        </h1>
                        <p className="mt-4 text-lg leading-relaxed text-ink-muted">{ad.hook}</p>

                        <CaseFacts mysteryCase={mysteryCase} className="mt-3 sm:mt-5" />

                        <div className="mt-3 flex items-baseline gap-3 sm:mt-6">
                            <span className="text-3xl font-semibold text-ink">
                                {discounted ? (
                                    <OfferPrice
                                        price={price}
                                        finalAmount={offer.final_amount}
                                        currency={mysteryCase.currency}
                                    />
                                ) : (
                                    price
                                )}
                            </span>
                            <span className="text-sm text-ink-muted">pago único</span>
                        </div>

                        <div ref={heroCta} className="mt-3 max-w-sm sm:mt-5">
                            {cta}
                        </div>
                    </div>

                    {mysteryCase.landing_cover_url && (
                        <img
                            src={mysteryCase.landing_cover_url}
                            fetchpriority="high"
                            alt={`Portada de ${mysteryCase.name}`}
                            className="mx-auto max-h-[28rem] w-full rounded-card border border-line object-cover lg:max-h-[36rem]"
                        />
                    )}
                </Container>
            </header>

            <Section kicker="Lo que te llevas" title="Un caso completo, listo para jugar">
                <ul className="grid gap-3 sm:grid-cols-3">
                    {ad.bullets.map((bullet) => (
                        <li
                            key={bullet}
                            className="flex gap-3 rounded-card border border-line bg-surface-raised p-4 text-sm leading-relaxed text-ink"
                        >
                            <span aria-hidden="true" className="text-accent">
                                —
                            </span>
                            {bullet}
                        </li>
                    ))}
                </ul>
            </Section>

            <HowItWorks />

            <Section tone="sunken" kicker="Incluye" title="Compras una vez, juegas cuando quieras">
                <ul className="grid gap-3 text-sm text-ink-muted sm:grid-cols-2">
                    {[
                        'Acceso permanente al caso',
                        `Hasta ${gamesPerCase} partidas, con grupos distintos`,
                        'Los jugadores no necesitan cuenta',
                        'Se puede jugar presencial o a distancia',
                    ].map((item) => (
                        <li key={item} className="flex gap-2.5">
                            <span aria-hidden="true" className="text-accent">
                                —
                            </span>
                            {item}
                        </li>
                    ))}
                </ul>
                <p className="mt-6 text-xs text-ink-subtle">
                    Pago seguro con tarjeta a través de Bold. Tu derecho de retracto está explicado en los{' '}
                    <Link href={route('terms')} className="py-2.5 underline">
                        Términos y Condiciones
                    </Link>
                    .
                </p>
            </Section>

            <MiniFaq />

            <Section>
                <div className="rounded-hero border border-line bg-surface-raised px-6 py-12 text-center sm:px-12">
                    <h2 className="font-display text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                        {mysteryCase.name}
                    </h2>
                    <p className="mx-auto mt-3 max-w-xl text-base text-ink-muted">
                        {discounted ? (
                                    <OfferPrice
                                        price={price}
                                        finalAmount={offer.final_amount}
                                        currency={mysteryCase.currency}
                                    />
                                ) : (
                                    price
                                )}{' '}
                        · pago único
                    </p>
                    <div className="mx-auto mt-6 max-w-sm">{cta}</div>
                </div>
            </Section>
        </AdLayout>
    );
}

/** One of the three cases on the platform landing: leads to ITS ad page. */
function PickCard({ mysteryCase }) {
    return (
        <Link
            href={route('ads.case', mysteryCase.slug)}
            className="group flex flex-col overflow-hidden rounded-card border border-line bg-surface-raised transition-colors hover:border-line-strong"
        >
            {mysteryCase.thumb_url && (
                <div className="aspect-[3/2] overflow-hidden bg-surface-sunken">
                    <img
                        src={mysteryCase.thumb_url}
                        alt=""
                        loading="lazy"
                        className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                </div>
            )}
            <div className="flex flex-1 flex-col p-5">
                <h3 className="font-semibold text-ink">{mysteryCase.name}</h3>
                {mysteryCase.tagline && <p className="mt-2 text-sm text-ink-muted">{mysteryCase.tagline}</p>}
                <CaseFacts mysteryCase={mysteryCase} className="mt-4" />
                <div className="mt-5 flex items-baseline justify-between gap-3 border-t border-line pt-4">
                    <span className="font-semibold text-ink">
                        {mysteryCase.offer_amount != null && mysteryCase.offer_amount < mysteryCase.price_amount ? (
                            <OfferPrice
                                price={formatPrice(mysteryCase.price_amount, mysteryCase.currency)}
                                finalAmount={mysteryCase.offer_amount}
                                currency={mysteryCase.currency}
                            />
                        ) : (
                            formatPrice(mysteryCase.price_amount, mysteryCase.currency)
                        )}
                    </span>
                    <span className="text-sm text-accent group-hover:text-accent-strong">Quiero este →</span>
                </div>
            </div>
        </Link>
    );
}

function PlatformLanding({ featured, offer, canPurchase }) {
    const { auth } = usePage().props;
    const heroCta = useRef(null);
    const showBar = useScrolledPast(heroCta);

    useEffect(() => {
        trackAd('ViewContent', { contentId: 'plataforma', contentName: 'MisterioCode' }, 'view-plataforma');
    }, []);

    // One button either way: signed out → make an account; signed in → go
    // and choose. (Payments being off changes nothing here: choosing a case
    // is still the next step.)
    const label = auth?.user ? 'Elegir mi caso →' : 'Crear cuenta gratis →';
    const target = auth?.user ? route('cases.index') : route('register');

    return (
        <AdLayout
            brandHref={route('ads.platform')}
            bar={
                <StickyBar visible={showBar}>
                    <Button href={target} fullWidth>
                        {label}
                    </Button>
                </StickyBar>
            }
        >
            <Head title="Resuelvan un crimen en equipo" />

            {offer && <OfferStrip offer={offer} currency={featured[0]?.currency} />}

            <header className="relative overflow-hidden border-b border-line bg-surface">
                <div className="absolute inset-0 opacity-40">
                    <img
                        src="/brand/hero-01-1200.jpg"
                        srcSet="/brand/hero-01-700.jpg 700w, /brand/hero-01-1200.jpg 1200w"
                        sizes="100vw"
                        width="1200"
                        height="675"
                        fetchpriority="high"
                        alt=""
                        className="h-full w-full object-cover"
                    />
                </div>

                <Container width="wide" className="relative z-10 py-10 sm:py-24">
                    <div className="max-w-2xl">
                        <h1 className="font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ink sm:text-6xl">
                            Un caso sin resolver.{' '}
                            <span className="text-ink-muted">Tu equipo. El reloj corriendo.</span>
                        </h1>
                        <p className="mt-5 text-lg leading-relaxed text-ink-muted">
                            El expediente llega por correo mientras juegan. Interrogan a los sospechosos y,
                            al final, hay que acusar a alguien. Sin imprimir nada.
                        </p>
                        <div ref={heroCta} className="mt-7 max-w-sm">
                            <Button href={target} size="lg" fullWidth>
                                {label}
                            </Button>
                            {!auth?.user && (
                                <p className="flex min-h-11 flex-wrap items-center justify-center gap-x-1.5 text-center text-sm text-ink-muted">
                                    ¿Ya tienes cuenta?{' '}
                                    <Link
                                        href={route('login')}
                                        className="inline-flex min-h-11 items-center px-1 text-accent underline"
                                    >
                                        Ingresar
                                    </Link>
                                </p>
                            )}
                        </div>
                    </div>
                </Container>
            </header>

            <HowItWorks />

            <Section
                tone="sunken"
                kicker="Elige tu caso"
                title="Tres para empezar"
                description={canPurchase ? 'Pago único por caso. Lo juegas las veces que quieras.' : undefined}
            >
                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {featured.map((mysteryCase, index) => (
                        <Reveal key={mysteryCase.slug} delay={index * 0.1}>
                            <PickCard mysteryCase={mysteryCase} />
                        </Reveal>
                    ))}
                </div>
            </Section>

            <MiniFaq />

            <Section>
                <div className="rounded-hero border border-line bg-surface-raised px-6 py-12 text-center sm:px-12">
                    <h2 className="font-display text-3xl font-semibold tracking-tight text-ink sm:text-4xl">
                        La investigación empieza cuando tú lo digas.
                    </h2>
                    <div className="mx-auto mt-6 max-w-sm">
                        <Button href={target} size="lg" fullWidth>
                            {label}
                        </Button>
                    </div>
                </div>
            </Section>
        </AdLayout>
    );
}

export default function AdLanding({
    mode,
    case: mysteryCase,
    offer = null,
    featured = [],
    owned = false,
    canPurchase = false,
    canSimulatePurchase = false,
    gamesPerCase,
}) {
    if (mode === 'case' && mysteryCase) {
        return (
            <CaseLanding
                mysteryCase={mysteryCase}
                offer={offer}
                owned={owned}
                canPurchase={canPurchase}
                canSimulatePurchase={canSimulatePurchase}
                gamesPerCase={gamesPerCase}
            />
        );
    }

    return <PlatformLanding featured={featured} offer={offer} canPurchase={canPurchase} />;
}
