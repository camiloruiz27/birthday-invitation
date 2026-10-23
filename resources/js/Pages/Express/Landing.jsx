import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import ExpressLayout, { ExpressContainer } from '../../Layouts/ExpressLayout';
import Button from '../../components/ui/Button';
import Card from '../../components/ui/Card';

const STEPS = [
    { title: 'Crea una sala', body: 'Tú serás el anfitrión y también juegas.' },
    { title: 'Invita de 1 a 4 amigos', body: 'Con un QR o un código de 5 letras. Sin apps ni cuentas.' },
    { title: 'Investiguen y acusen', body: 'Cada celular ve pistas distintas: compárenlas antes de acusar.' },
];

/** What a closed event says: the server's own reason if it gave one. */
function Closed({ reason }) {
    return (
        <Card as="section" className="mt-8 text-center">
            <h2 className="font-display text-xl font-semibold text-ink">Por ahora no hay partidas abiertas</h2>
            <p className="mt-2 text-sm text-ink-muted">
                {reason || 'La demo no está disponible en este momento. Pásate de nuevo por el stand en un rato.'}
            </p>
        </Card>
    );
}

function CodeForm() {
    const [code, setCode] = useState('');
    const ready = code.length === 5;

    function submit(event) {
        event.preventDefault();

        if (ready) router.visit(route('express.join', code));
    }

    return (
        <form onSubmit={submit} aria-labelledby="have-code" className="mt-8">
            <h2 id="have-code" className="text-sm font-semibold text-ink">
                Tengo un código
            </h2>

            <div className="mt-2 flex items-stretch gap-2">
                <label htmlFor="room-code" className="sr-only">
                    Código de la sala (5 caracteres)
                </label>
                <input
                    id="room-code"
                    name="code"
                    type="text"
                    value={code}
                    maxLength={5}
                    inputMode="text"
                    autoCapitalize="characters"
                    autoComplete="off"
                    autoCorrect="off"
                    spellCheck={false}
                    enterKeyHint="go"
                    placeholder="K7M2Q"
                    onChange={(event) =>
                        setCode(event.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 5))
                    }
                    className="min-h-12 min-w-0 flex-1 rounded-control border border-line-strong bg-surface-sunken px-4 text-center font-mono text-xl uppercase tracking-[0.3em] text-ink placeholder:text-ink-subtle"
                />
                <Button type="submit" variant="secondary" size="lg" disabled={!ready}>
                    Entrar
                </Button>
            </div>
        </form>
    );
}

/**
 * The page the stand's QR lands on. One line of pitch, one big button, and a
 * smaller way in for the friend who was handed a code.
 */
export default function Landing({ event, enabled = true, closed_reason: closedReason = null }) {
    const createHref = route('express.create', event?.slug ? { e: event.slug } : {});

    return (
        <ExpressLayout brandHref={route('express.home')}>
            <Head title="Resuelvan un caso en 5 minutos" />

            <ExpressContainer className="py-8 sm:py-12">
                {event?.name && <p className="case-stamp text-[10px] text-accent">{event.name}</p>}

                <h1 className="mt-2 font-display text-4xl font-semibold leading-[1.05] tracking-tight text-ink sm:text-5xl">
                    Resuelvan un caso en 5 minutos.{' '}
                    <span className="text-ink-muted">Cada celular ve pistas distintas.</span>
                </h1>

                {event?.tagline && <p className="mt-4 text-base leading-relaxed text-ink-muted">{event.tagline}</p>}

                {enabled ? (
                    <>
                        <div className="mt-7">
                            <Button href={createHref} size="lg" fullWidth>
                                Crear una sala
                            </Button>
                        </div>

                        <CodeForm />

                        <section aria-labelledby="how" className="mt-10">
                            <h2 id="how" className="case-stamp text-[10px] text-ink-subtle">
                                Cómo se juega
                            </h2>
                            <ol className="mt-3 space-y-4">
                                {STEPS.map((step, index) => (
                                    <li key={step.title} className="flex gap-4 border-t-2 border-accent-dim pt-3">
                                        <span className="font-mono text-sm text-accent">0{index + 1}</span>
                                        <div>
                                            <h3 className="font-semibold text-ink">{step.title}</h3>
                                            <p className="mt-1 text-sm leading-relaxed text-ink-muted">{step.body}</p>
                                        </div>
                                    </li>
                                ))}
                            </ol>
                        </section>
                    </>
                ) : (
                    <Closed reason={closedReason} />
                )}
            </ExpressContainer>
        </ExpressLayout>
    );
}
