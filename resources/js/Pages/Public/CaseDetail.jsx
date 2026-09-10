import { Head, Link, useForm, usePage } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Container from '../../components/ui/Container';
import Card from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import Badge from '../../components/ui/Badge';
import Alert from '../../components/ui/Alert';
import Reveal from '../../components/ui/Reveal';
import Section from '../../components/public/Section';
import { CaseFacts } from '../../components/public/CaseCard';
import Icon from '../../lib/mechanicIcons';
import { formatPrice } from '../../lib/format';

function PurchasePanel({ mysteryCase, owned, canPurchase, canSimulatePurchase }) {
    const { auth } = usePage().props;
    const { post, processing } = useForm({});

    // The simulated stand-in acquires immediately — there is nothing to
    // confirm about a purchase that costs nothing. A real purchase goes to
    // the review screen first (see Payments/Review), never straight here.
    function acquireSimulated() {
        post(route('cases.acquire', mysteryCase.slug));
    }

    return (
        <Card className="lg:sticky lg:top-24">
            <p className="text-2xl font-semibold text-ink">
                {formatPrice(mysteryCase.price_amount, mysteryCase.currency)}
            </p>
            <p className="mt-1 text-sm text-ink-muted">
                Pago único. Acceso permanente, partidas ilimitadas.
            </p>

            <div className="mt-6">
                {owned ? (
                    <Button href={route('dashboard')} fullWidth>
                        Crear una partida
                    </Button>
                ) : !auth?.user ? (
                    <>
                        <Button href={route('register')} fullWidth>
                            Crear cuenta para adquirirlo
                        </Button>
                        <p className="mt-3 text-center text-sm text-ink-muted">
                            ¿Ya tienes cuenta?{' '}
                            <Link href={route('login')} className="text-accent underline">
                                Ingresar
                            </Link>
                        </p>
                    </>
                ) : canPurchase ? (
                    <Button href={route('cases.checkout.review', mysteryCase.slug)} fullWidth>
                        Comprar
                    </Button>
                ) : canSimulatePurchase ? (
                    <>
                        <Button onClick={acquireSimulated} loading={processing} fullWidth>
                            {processing ? 'Añadiendo…' : 'Añadir a mi biblioteca'}
                        </Button>
                        {/* Never let a simulated acquisition look like a real
                            purchase. */}
                        <Alert variant="warning" className="mt-4 mb-0">
                            Adquisición simulada: la pasarela de pagos todavía no está
                            integrada, así que esto no cobra nada.
                        </Alert>
                    </>
                ) : (
                    <Alert variant="info" className="mb-0">
                        La compra en línea todavía no está disponible. Escríbenos y te damos
                        acceso.
                    </Alert>
                )}
            </div>

            <ul className="mt-6 space-y-2.5 border-t border-line pt-6 text-sm text-ink-muted">
                {[
                    'Acceso permanente al caso',
                    'Partidas ilimitadas, con grupos distintos',
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
        </Card>
    );
}

export default function CaseDetail({ case: mysteryCase, owned, canPurchase, canSimulatePurchase }) {
    return (
        <PublicLayout current="cases.index">
            <Head title={mysteryCase.name} />

            <div className="border-b border-line">
                <Container width="wide" className="py-12 sm:py-16">
                    <Link
                        href={route('cases.index')}
                        className="text-sm text-ink-muted hover:text-ink"
                    >
                        ← Todos los casos
                    </Link>

                    <div className="mt-6 grid gap-10 lg:grid-cols-[1.5fr_1fr]">
                        <div>
                            {/* No two-tone split here the way the landing hero
                                has one: a case's own name is dynamic content,
                                and forcing an arbitrary colour split into a
                                sentence we didn't write would break for a
                                short or punctuation-free title. The tagline
                                already carries the same "full tone, then
                                muted" read as a separate, safer element. */}
                            <Reveal>
                                <h1 className="font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ink sm:text-6xl">
                                    {mysteryCase.name}
                                </h1>
                            </Reveal>

                            {mysteryCase.tagline && (
                                <Reveal delay={0.2}>
                                    <p className="mt-4 text-lg text-ink-muted">{mysteryCase.tagline}</p>
                                </Reveal>
                            )}

                            <CaseFacts mysteryCase={mysteryCase} className="mt-6" />

                            {mysteryCase.cover_url && (
                                <img
                                    src={mysteryCase.cover_url}
                                    alt=""
                                    className="mt-8 w-full rounded-card border border-line object-cover"
                                />
                            )}

                            {mysteryCase.description && (
                                <div className="mt-8 space-y-4 text-base leading-relaxed text-ink-muted">
                                    {mysteryCase.description
                                        .split(/\n{2,}/)
                                        .map((paragraph, index) => (
                                            <p key={index}>{paragraph.trim()}</p>
                                        ))}
                                </div>
                            )}
                        </div>

                        <div>
                            <PurchasePanel
                                mysteryCase={mysteryCase}
                                owned={owned}
                                canPurchase={canPurchase}
                                canSimulatePurchase={canSimulatePurchase}
                            />
                        </div>
                    </div>
                </Container>
            </div>

            <Section
                kicker="Qué incluye"
                title="Las mecánicas de este caso"
                description="No todos los casos usan las mismas. Estas son las de este."
            >
                <div className="grid gap-5 sm:grid-cols-2">
                    {mysteryCase.mechanics.map((mechanic, index) => (
                        <Reveal key={mechanic.slug} delay={index * 0.1}>
                            <Card as="article" className="group transition-shadow hover:shadow-overlay">
                                <div className="flex items-start justify-between gap-3">
                                    <span className="flex h-12 w-12 items-center justify-center rounded-card bg-surface-sunken text-ink-muted transition-all duration-500 group-hover:-rotate-6 group-hover:bg-accent group-hover:text-ink-inverse">
                                        <Icon slug={mechanic.slug} />
                                    </span>
                                    {mechanic.ai && <Badge tone="accent">IA</Badge>}
                                </div>
                                <h3 className="mt-4 font-semibold text-ink">{mechanic.name}</h3>
                                <p className="mt-2 text-sm leading-relaxed text-ink-muted">
                                    {mechanic.detail}
                                </p>
                            </Card>
                        </Reveal>
                    ))}
                </div>

                {mysteryCase.uses_ai && (
                    <p className="mt-8 text-sm text-ink-muted">
                        Este caso usa inteligencia artificial en algunas mecánicas.{' '}
                        <Link href={route('ai')} className="text-accent underline">
                            Te explicamos exactamente cómo
                        </Link>
                        .
                    </p>
                )}
            </Section>
        </PublicLayout>
    );
}
