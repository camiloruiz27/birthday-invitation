import { Head, Link } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Container from '../../components/ui/Container';
import Button from '../../components/ui/Button';
import Card from '../../components/ui/Card';
import Accordion from '../../components/ui/Accordion';
import EmptyState from '../../components/ui/EmptyState';
import Section from '../../components/public/Section';
import CaseCard from '../../components/public/CaseCard';

const STEPS = [
    {
        title: 'Elige un caso',
        body: 'Compras el misterio una vez y queda en tu biblioteca para siempre. Puedes dirigirlo las veces que quieras, con grupos distintos.',
    },
    {
        title: 'Invita a tu equipo',
        body: 'Cada jugador recibe su propio enlace. No necesitan crear cuenta ni instalar nada: abren el enlace en su teléfono y ya están dentro.',
    },
    {
        title: 'Inicia la investigación',
        body: 'El caso arranca y el expediente empieza a llegar por partes. Tú decides el ritmo: puedes pausar, reanudar o adelantar un evento.',
    },
    {
        title: 'Acusen',
        body: 'Al final cada jugador entrega su acusación: quién, con qué y por qué. Tú las ves todas juntas antes de revelar la solución.',
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

export default function Landing({ featured, mechanics }) {
    return (
        <PublicLayout current="home">
            <Head title="Misterios interactivos para jugar en equipo" />

            {/* Hero */}
            <div className="border-b border-line">
                <Container width="wide" className="py-20 sm:py-28">
                    <div className="max-w-3xl">
                        <p className="text-xs font-medium uppercase tracking-widest text-accent">
                            Misterios interactivos
                        </p>

                        <h1 className="mt-4 text-3xl font-semibold leading-tight text-ink sm:text-5xl">
                            Un caso sin resolver, tu equipo y un reloj corriendo.
                        </h1>

                        <p className="mt-6 max-w-2xl text-lg leading-relaxed text-ink-muted">
                            No es un PDF para imprimir. El expediente llega por correo mientras
                            juegan, la evidencia se ve como evidencia, los sospechosos responden
                            cuando los interrogan y al final hay que acusar a alguien.
                        </p>

                        <div className="mt-10 flex flex-col gap-3 sm:flex-row">
                            <Button href={route('cases.index')} size="lg">
                                Ver los casos
                            </Button>
                            <Button href={route('mechanics')} variant="secondary" size="lg">
                                Cómo funciona
                            </Button>
                        </div>
                    </div>
                </Container>
            </div>

            {/* Why it is different */}
            <Section
                kicker="Qué lo hace distinto"
                title="La diferencia es que el caso te llega a ti"
                description="En un juego de misterio impreso, alguien reparte hojas. Aquí cada jugador tiene su propia bandeja, su propio material y sus propios interrogatorios — y no todos reciben lo mismo."
                tone="sunken"
            >
                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {mechanics.map((mechanic) => (
                        <Card key={mechanic.slug} as="article">
                            <h3 className="font-semibold text-ink">{mechanic.name}</h3>
                            <p className="mt-2 text-sm leading-relaxed text-ink-muted">
                                {mechanic.summary}
                            </p>
                        </Card>
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

            {/* How it works */}
            <Section kicker="Cómo funciona" title="Cuatro pasos, de la compra a la acusación">
                <ol className="grid gap-6 sm:grid-cols-2">
                    {STEPS.map((step, index) => (
                        <li key={step.title} className="flex gap-4">
                            <span
                                aria-hidden="true"
                                className="tabular flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-accent-dim bg-accent-dim text-sm font-semibold text-accent-strong"
                            >
                                {index + 1}
                            </span>
                            <div>
                                <h3 className="font-semibold text-ink">{step.title}</h3>
                                <p className="mt-1.5 text-sm leading-relaxed text-ink-muted">
                                    {step.body}
                                </p>
                            </div>
                        </li>
                    ))}
                </ol>
            </Section>

            {/* Featured cases */}
            <Section
                kicker="Casos"
                title="Elige tu misterio"
                tone="sunken"
            >
                {featured.length === 0 ? (
                    <EmptyState
                        title="Todavía no hay casos publicados"
                        description="Estamos preparando los primeros. Vuelve pronto."
                    />
                ) : (
                    <>
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {featured.map((item) => (
                                <CaseCard key={item.slug} mysteryCase={item} />
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

            {/* Game Master */}
            <Section kicker="Para quien dirige" title="Tú controlas la partida">
                <div className="grid gap-10 lg:grid-cols-2">
                    <div className="space-y-4 text-base leading-relaxed text-ink-muted">
                        <p>
                            Dirigir no es leer un guion. Tienes una consola donde ves el reloj de
                            la partida, qué le ha llegado a cada jugador y qué está a punto de
                            pasar.
                        </p>
                        <p>
                            Si el grupo va rápido, adelantas un evento. Si necesitan un respiro,
                            pausas y el reloj se detiene. Puedes leer todos los interrogatorios
                            mientras ocurren y comparar las acusaciones al final.
                        </p>
                    </div>

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
                </div>
            </Section>

            {/* AI, stated plainly */}
            <Section kicker="Inteligencia artificial" title="Dónde usamos IA, y dónde no" tone="sunken">
                <div className="max-w-2xl space-y-4 text-base leading-relaxed text-ink-muted">
                    <p>
                        Usamos IA en dos sitios concretos: para que los sospechosos respondan
                        cuando los interrogan, y para poner voz a los mensajes de audio.
                    </p>
                    <p>
                        El resto del caso — la historia, la evidencia, los testimonios, la
                        solución — está escrito por personas. La IA no inventa hechos del caso:
                        cada sospechoso solo conoce su propia declaración.
                    </p>
                </div>

                <div className="mt-8">
                    <Button href={route('ai')} variant="secondary">
                        Cómo funciona exactamente
                    </Button>
                </div>
            </Section>

            {/* FAQ */}
            <Section kicker="Preguntas" title="Lo que suelen preguntar" width="prose">
                <div className="space-y-3">
                    {FAQ.map((item) => (
                        <Accordion key={item.question} summary={item.question}>
                            <p className="text-sm leading-relaxed text-ink-muted">{item.answer}</p>
                        </Accordion>
                    ))}
                </div>
            </Section>

            {/* Closing call to action */}
            <Section tone="sunken">
                <div className="rounded-card border border-line bg-surface-raised px-6 py-12 text-center sm:px-12">
                    <h2 className="text-2xl font-semibold text-ink sm:text-3xl">
                        ¿Listo para abrir tu primer caso?
                    </h2>
                    <p className="mx-auto mt-4 max-w-xl text-base text-ink-muted">
                        Crea tu cuenta, elige un misterio e invita a tu equipo. La investigación
                        empieza cuando tú lo digas.
                    </p>
                    <div className="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                        <Button href={route('cases.index')} size="lg">
                            Ver los casos
                        </Button>
                        <Button href={route('register')} variant="secondary" size="lg">
                            Crear cuenta
                        </Button>
                    </div>
                </div>
            </Section>
        </PublicLayout>
    );
}
