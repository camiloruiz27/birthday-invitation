import { Head } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Card from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import Section from '../../components/public/Section';

function MechanicList({ mechanics, tone }) {
    return (
        <ul className="space-y-4">
            {mechanics.map((mechanic) => (
                <li key={mechanic.slug} className="flex gap-3">
                    <span
                        aria-hidden="true"
                        className={tone === 'ai' ? 'text-accent' : 'text-ink-subtle'}
                    >
                        —
                    </span>
                    <div>
                        <p className="text-sm font-medium text-ink">{mechanic.name}</p>
                        <p className="mt-0.5 text-sm text-ink-muted">{mechanic.summary}</p>
                    </div>
                </li>
            ))}
        </ul>
    );
}

export default function Ai({ withAi, withoutAi }) {
    return (
        <PublicLayout current="ai">
            <Head title="Inteligencia artificial" />

            <Section
                kicker="Inteligencia artificial"
                title="Dónde usamos IA, y dónde no"
                description="La IA aquí hace dos cosas concretas. El resto del caso lo escriben personas, y creemos que hay que decirlo claramente en vez de venderlo como magia."
                width="prose"
            >
                <div className="grid gap-6 sm:grid-cols-2">
                    <Card>
                        <h2 className="font-semibold text-accent">Usan IA</h2>
                        <div className="mt-4">
                            <MechanicList mechanics={withAi} tone="ai" />
                        </div>
                    </Card>

                    <Card>
                        <h2 className="font-semibold text-ink">No usan IA</h2>
                        <div className="mt-4">
                            <MechanicList mechanics={withoutAi} />
                        </div>
                    </Card>
                </div>
            </Section>

            <Section title="Cómo funciona el interrogatorio" width="prose" tone="sunken">
                <div className="space-y-4 text-base leading-relaxed text-ink-muted">
                    <p>
                        Cuando interrogas a un sospechoso, el modelo recibe{' '}
                        <strong className="text-ink">
                            únicamente la declaración escrita de esa persona
                        </strong>
                        . No conoce la solución del caso ni lo que declararon los demás.
                    </p>
                    <p>
                        Eso es a propósito: significa que un sospechoso no puede filtrarte quién
                        fue, ni contradecir el material que ya tienes, ni inventar una prueba que
                        no existe. Responde en personaje sobre lo que su declaración dice, y poco
                        más.
                    </p>
                    <p>
                        Las preguntas por persona son limitadas, así que interrogar es una
                        decisión: no puedes preguntarlo todo a todos.
                    </p>
                </div>
            </Section>

            <Section title="Qué pasa si la IA falla" width="prose">
                <div className="space-y-4 text-base leading-relaxed text-ink-muted">
                    <p>
                        La partida no depende de ella. Si el servicio de IA no responde, el
                        sospechoso da una respuesta neutra y la investigación sigue; si un audio
                        no se puede generar, el correo llega igual sin la grabación.
                    </p>
                    <p>
                        Un caso puede desactivar por completo las mecánicas con IA y seguir
                        siendo jugable: la historia, la evidencia y los testimonios escritos no
                        dependen de ningún modelo.
                    </p>
                </div>
            </Section>

            <Section title="Lo que no hacemos" width="prose" tone="sunken">
                <div className="space-y-4 text-base leading-relaxed text-ink-muted">
                    <p>
                        No generamos casos con IA. La historia, los personajes, las pistas y la
                        solución están escritos y revisados por personas — un misterio tiene que
                        cuadrar, y eso todavía no se delega.
                    </p>
                    <p>
                        Tampoco usamos lo que escriben los jugadores durante la partida para
                        entrenar nada.
                    </p>
                </div>

                <div className="mt-8">
                    <Button href={route('cases.index')}>Ver los casos</Button>
                </div>
            </Section>
        </PublicLayout>
    );
}
