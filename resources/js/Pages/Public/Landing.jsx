import { Head, Link } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Container from '../../components/ui/Container';
import Button from '../../components/ui/Button';
import Card from '../../components/ui/Card';
import Accordion from '../../components/ui/Accordion';
import EmptyState from '../../components/ui/EmptyState';
import Reveal from '../../components/ui/Reveal';
import Section from '../../components/public/Section';
import CaseCard from '../../components/public/CaseCard';
import Icon from '../../lib/mechanicIcons';

/**
 * The session, as phases rather than absolute minutes — every case has its
 * own real `duration_minutes` on its own page, and this page names three or
 * more cases at once, so a single "00:10, 00:45…" timeline would be a
 * platform-wide claim that is only ever true for one case at a time.
 */
const CASE_CLOCK = [
    {
        label: 'Caso abierto',
        body: 'Cada jugador entra con su propio enlace. Nadie necesita cuenta ni instala nada.',
    },
    {
        label: 'Primera evidencia',
        body: 'Llega el primer correo del expediente: el caso empieza a abrirse.',
    },
    {
        label: 'Interrogatorios',
        body: 'Cada quien elige a quién preguntarle, con un número limitado de preguntas.',
    },
    {
        label: 'Nueva información',
        body: 'El correo sigue llegando mientras investigan — no todo de una vez.',
    },
    {
        label: 'Acusaciones',
        body: 'Cada jugador dice quién fue, con qué y por qué.',
    },
    {
        label: 'La verdad',
        body: 'Se revela la solución, y quién de la mesa acertó.',
    },
];

const FAQ = [
    {
        question: '¿Hay que imprimir algo?',
        answer: 'No. Todo pasa en el navegador y en el correo de cada jugador. No hay PDFs que imprimir, recortar ni repartir.',
    },
    {
        question: '¿Los jugadores necesitan cuenta?',
        answer: 'No. Solo quien dirige la partida tiene cuenta. Los jugadores entran con un enlace único que tú les compartes.',
    },
    {
        question: '¿Se puede jugar a distancia?',
        answer: 'Sí. Cada jugador recibe su material por separado, así que funciona igual si están en la misma mesa o en una videollamada.',
    },
    {
        question: '¿Puedo repetir un caso?',
        answer: 'Puedes dirigirlo cuantas veces quieras con grupos distintos. Eso sí, quien ya lo jugó conoce la solución.',
    },
    {
        question: '¿Cuánto dura una partida?',
        answer: 'Depende del caso; cada uno indica su duración en su página. La mayoría está pensada para una sesión de una tarde o una noche.',
    },
    {
        question: '¿Necesito preparar algo antes?',
        answer: 'Crear la partida y cargar los nombres y correos de tus jugadores. Nada más: el caso trae su propia línea de tiempo.',
    },
];

function CaseClock() {
    return (
        <Section id="el-reloj" kicker="El reloj" title="Así avanza una partida" tone="sunken">
            <ol className="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                {CASE_CLOCK.map((phase, index) => (
                    <Reveal key={phase.label} as="li" delay={index * 0.1} className="border-t-2 border-accent-dim pt-4">
                        <h3 className="font-semibold text-ink">{phase.label}</h3>
                        <p className="mt-1.5 text-sm leading-relaxed text-ink-muted">{phase.body}</p>
                    </Reveal>
                ))}
            </ol>

            <div className="mt-10 flex flex-wrap items-center justify-between gap-4">
                <p className="text-sm text-ink-muted">
                    La duración exacta depende del caso — cada uno indica la suya en su propia
                    página.
                </p>
                <Button href={route('cases.index')} variant="secondary">
                    Ver los casos →
                </Button>
            </div>
        </Section>
    );
}

/**
 * A flat, non-interactive sample of the real interrogation chat — not a
 * glossy phone mockup, not a screenshot. "Sospechoso 02" and its lines are
 * an invented example for this page only: a real suspect's name or a real
 * line of testimony has no place on a public page, since it would spoil an
 * actual case before anyone bought it.
 */
function InterrogationSample() {
    return (
        <div className="overflow-hidden border-2 border-paper-ink bg-paper-raised font-case">
            <div className="flex items-center justify-between bg-paper-ink px-4 py-2.5 text-paper">
                <span className="case-stamp text-xs">Sospechoso 02 · Testigo</span>
                <span className="case-stamp text-xs">2/5 preguntas</span>
            </div>
            <div className="space-y-3 p-4 text-sm text-paper-ink">
                <p className="ml-auto max-w-[85%] border border-paper-line bg-paper px-3 py-2">
                    ¿Dónde estaba esa noche?
                </p>
                <p className="mr-auto max-w-[85%] border border-paper-line bg-paper-raised px-3 py-2">
                    Ya se lo dije a la policía: en la oficina, solo. Nadie puede
                    confirmarlo, lo sé.
                </p>
            </div>
        </div>
    );
}

export default function Landing({ featured, mechanics }) {
    return (
        <PublicLayout current="home" bleed>
            <Head title="MisterioCode — Casos de misterio para jugar en equipo" />

            {/* 01 / El caso — full-bleed photo behind the headline. The
                image sits at opacity-40 over the Carbón background rather
                than under a gradient overlay: the same technique keeps the
                dark palette consistent instead of adding a second dark
                treatment on top of another. Works with no photo yet too —
                an empty <img> src renders nothing and the Carbón background
                alone carries the hero until public/brand/hero-01.png exists. */}
            <header className="relative flex min-h-[90vh] items-center overflow-hidden border-b border-line bg-surface">
                <div className="absolute inset-0 opacity-40">
                    <img src="/brand/hero-01.png" alt="" className="h-full w-full object-cover" />
                </div>

                <Container width="wide" className="relative z-10 pb-20 pt-40 sm:pb-28 sm:pt-48">
                    <div className="max-w-3xl">
                        <Reveal>
                            <h1 className="font-display text-5xl font-semibold leading-[1.05] tracking-tight text-ink sm:text-7xl lg:text-8xl">
                                Un caso sin resolver.{' '}
                                <span className="text-ink-muted">Tu equipo. El reloj corriendo.</span>
                            </h1>
                        </Reveal>

                        <Reveal delay={0.2}>
                            <p className="mt-8 max-w-2xl text-lg leading-relaxed text-ink-muted sm:text-xl">
                                No es un PDF para imprimir. El expediente llega por correo mientras
                                juegan, la evidencia se ve como evidencia, los sospechosos responden
                                cuando los interrogan y al final hay que acusar a alguien.
                            </p>
                        </Reveal>

                        <Reveal delay={0.3} className="mt-10 flex flex-col gap-3 sm:flex-row">
                            <Button href={route('cases.index')} size="lg">
                                Abrir el expediente →
                            </Button>

                            {/* A same-page anchor, not a Button/Link: it must
                                never trigger an Inertia visit, only scroll —
                                app.css already sets scroll-behavior: smooth
                                and scroll-padding-top for exactly this. */}
                            <a
                                href="#el-reloj"
                                className="inline-flex min-h-12 items-center justify-center gap-2 rounded-full border border-line-strong bg-surface-raised/80 px-7 py-3 text-base text-ink backdrop-blur-sm transition-colors hover:bg-line-strong"
                            >
                                Cómo funciona
                            </a>
                        </Reveal>
                    </div>
                </Container>
            </header>

            {/* 02 / La evidencia */}
            <Section
                kicker="La evidencia"
                title="El caso no se lee. Se investiga."
                description="En un juego de misterio impreso, alguien reparte hojas. Aquí cada jugador tiene su propia bandeja, su propio material y sus propios interrogatorios — y no todos reciben lo mismo."
            >
                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {mechanics.map((mechanic, index) => (
                        <Reveal key={mechanic.slug} delay={index * 0.1}>
                            <Card as="article" className="group transition-shadow hover:shadow-overlay">
                                <span className="flex h-12 w-12 items-center justify-center rounded-card bg-surface-sunken text-ink-muted transition-all duration-500 group-hover:-rotate-6 group-hover:bg-accent group-hover:text-ink-inverse">
                                    <Icon slug={mechanic.slug} />
                                </span>
                                <h3 className="mt-4 font-semibold text-ink">{mechanic.name}</h3>
                                <p className="mt-2 text-sm leading-relaxed text-ink-muted">
                                    {mechanic.summary}
                                </p>
                            </Card>
                        </Reveal>
                    ))}
                </div>

                <p className="mt-8 text-sm text-ink-muted">
                    No todos los casos usan todas las mecánicas.{' '}
                    <Link href={route('mechanics')} className="text-accent underline">
                        Ver cada una en detalle
                    </Link>
                    .
                </p>
            </Section>

            {/* 03 / El reloj */}
            <CaseClock />

            {/* 04 / Interrogatorios */}
            <Section kicker="Interrogatorios" title="Le preguntas, y te responde">
                <div className="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <Reveal className="space-y-4 text-base leading-relaxed text-ink-muted">
                        <p>
                            Cada sospechoso tiene su propio testimonio. Puedes preguntarle
                            directamente, con un número limitado de preguntas — así que hay que
                            elegir bien.
                        </p>
                        <p>
                            El sospechoso que interroga un jugador queda suyo: nadie más puede
                            repetirle la pregunta después. Al final, todos comparan lo que
                            averiguaron.
                        </p>
                    </Reveal>

                    <Reveal delay={0.15}>
                        <InterrogationSample />
                    </Reveal>
                </div>
            </Section>

            {/* 05 / La mesa — sin fotografía todavía */}
            <Section
                kicker="La mesa"
                title="Se juega entre personas, no contra la pantalla"
                tone="sunken"
            >
                <div className="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <Reveal className="space-y-4 text-base leading-relaxed text-ink-muted">
                        <p>
                            Cada quien tiene su teléfono, su propio material y sus propias
                            preguntas. La mesa se arma comparando lo que cada uno averiguó, no
                            leyendo lo mismo al mismo tiempo.
                        </p>
                        <p>
                            Quien dirige ve todo; quien juega, solo lo suyo — hasta que llega el
                            momento de acusar.
                        </p>
                    </Reveal>

                    <Reveal delay={0.15} className="relative">
                        <div className="overflow-hidden rounded-hero border border-line bg-surface-sunken shadow-overlay">
                            <img
                                src="/brand/mesa-01.png"
                                alt=""
                                loading="lazy"
                                className="aspect-4/3 w-full object-cover"
                            />
                        </div>

                        {/* Breaks out of the photo's corner on purpose — the
                            same device the reference uses to overlap a data
                            panel on an image, adapted to MisterioCode's own
                            surface/accent colours instead of copying its
                            black-on-white treatment. */}
                        <div className="absolute -bottom-6 -right-4 max-w-52 rounded-card border border-line-strong bg-surface-raised p-5 shadow-overlay sm:-right-8">
                            <p className="text-sm font-semibold text-ink">Jugadores</p>
                            <p className="mt-1.5 text-sm leading-snug text-ink-muted">
                                Cada uno con su enlace, su bandeja y sus propias preguntas.
                            </p>
                        </div>
                    </Reveal>
                </div>
            </Section>

            {/* 06 / Los casos */}
            <Section kicker="Los casos" title="Elige tu misterio">
                {featured.length === 0 ? (
                    <EmptyState
                        title="Todavía no hay casos publicados"
                        description="Estamos preparando los primeros. Vuelve pronto."
                    />
                ) : (
                    <>
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {featured.map((item, index) => (
                                <Reveal key={item.slug} delay={index * 0.1}>
                                    <CaseCard mysteryCase={item} />
                                </Reveal>
                            ))}
                        </div>

                        <div className="mt-10">
                            <Button href={route('cases.index')} variant="secondary">
                                Ver todos los casos
                            </Button>
                        </div>
                    </>
                )}
            </Section>

            {/* Para quien dirige */}
            <Section kicker="Para quien dirige" title="Tú controlas la partida" tone="sunken">
                <div className="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <Reveal>
                        <div className="overflow-hidden rounded-hero border border-line bg-surface-sunken shadow-overlay">
                            <img
                                src="/brand/direccion-01.png"
                                alt=""
                                loading="lazy"
                                className="aspect-4/3 w-full object-cover"
                            />
                        </div>
                    </Reveal>

                    <div className="space-y-8">
                        <Reveal delay={0.1} className="space-y-4 text-base leading-relaxed text-ink-muted">
                            <p>
                                Dirigir no es leer un guion. Tienes una consola donde ves el reloj
                                de la partida, qué le ha llegado a cada jugador y qué está a punto
                                de pasar.
                            </p>
                            <p>
                                Si el grupo va rápido, adelantas un evento. Si necesitan un
                                respiro, pausas y el reloj se detiene. Puedes leer todos los
                                interrogatorios mientras ocurren y comparar las acusaciones al
                                final.
                            </p>
                        </Reveal>

                        <Reveal delay={0.2}>
                            <Card>
                                <ul className="space-y-3 text-sm">
                                    {[
                                        'Reloj de partida con pausa y reanudación',
                                        'Línea de tiempo con lo enviado y lo pendiente',
                                        'Enlace de acceso de cada jugador, listo para copiar',
                                        'Todos los interrogatorios, en vivo',
                                        'Las acusaciones de la mesa, una al lado de la otra',
                                    ].map((item) => (
                                        <li key={item} className="flex gap-3 text-ink-muted">
                                            <span aria-hidden="true" className="text-accent">
                                                —
                                            </span>
                                            {item}
                                        </li>
                                    ))}
                                </ul>
                            </Card>
                        </Reveal>
                    </div>
                </div>
            </Section>

            {/* IA, dicho sin rodeos */}
            <Section kicker="Inteligencia artificial" title="Dónde usamos IA, y dónde no">
                <div className="grid gap-10 lg:grid-cols-2 lg:items-center">
                    <div>
                        <Reveal className="space-y-4 text-base leading-relaxed text-ink-muted">
                            <p>
                                Usamos IA en dos sitios concretos: para que los sospechosos
                                respondan cuando los interrogan, y para poner voz a los mensajes
                                de audio.
                            </p>
                            <p>
                                El resto del caso — la historia, la evidencia, los testimonios, la
                                solución — está escrito por personas. La IA no inventa hechos del
                                caso: cada sospechoso solo conoce su propia declaración.
                            </p>
                        </Reveal>

                        <div className="mt-8">
                            <Button href={route('ai')} variant="secondary">
                                Cómo funciona exactamente
                            </Button>
                        </div>
                    </div>

                    <Reveal delay={0.15}>
                        <div className="overflow-hidden rounded-hero border border-line bg-surface-sunken shadow-overlay">
                            <img
                                src="/brand/ia-01.png"
                                alt=""
                                loading="lazy"
                                className="aspect-4/3 w-full object-cover"
                            />
                        </div>
                    </Reveal>
                </div>
            </Section>

            {/* Preguntas */}
            <Section kicker="Preguntas" title="Lo que suelen preguntar" width="prose" tone="sunken">
                <Reveal className="space-y-3">
                    {FAQ.map((item) => (
                        <Accordion key={item.question} summary={item.question}>
                            <p className="text-sm leading-relaxed text-ink-muted">{item.answer}</p>
                        </Accordion>
                    ))}
                </Reveal>
            </Section>

            {/* 07 / Cierre */}
            <Section>
                <Reveal as="div" className="rounded-hero border border-line bg-surface-raised px-6 py-16 text-center sm:px-12">
                    <h2 className="font-display text-3xl font-semibold tracking-tight text-ink sm:text-5xl">
                        La investigación empieza cuando tú lo digas.
                    </h2>
                    <p className="mx-auto mt-4 max-w-xl text-base text-ink-muted">
                        Crea tu cuenta, elige un misterio e invita a tu equipo.
                    </p>
                    <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <Button href={route('cases.index')} size="lg">
                            Elegir un caso →
                        </Button>
                        <Button href={route('register')} variant="secondary" size="lg">
                            Crear cuenta
                        </Button>
                    </div>
                </Reveal>
            </Section>
        </PublicLayout>
    );
}
