import { Head } from '@inertiajs/react';
import PublicLayout from '../../Layouts/PublicLayout';
import Card from '../../components/ui/Card';
import Badge from '../../components/ui/Badge';
import Button from '../../components/ui/Button';
import Section from '../../components/public/Section';

export default function Mechanics({ mechanics }) {
    return (
        <PublicLayout current="mechanics">
            <Head title="Mecánicas" />

            <Section
                kicker="Mecánicas"
                title="De qué está hecho un caso"
                description="Un misterio aquí no es un texto que se lee: es material que llega, evidencia que se revisa y personas a las que se interroga. Estas son las piezas con las que se construye."
            >
                <div className="space-y-5">
                    {mechanics.map((mechanic) => (
                        <Card key={mechanic.slug} as="article">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <h2 className="text-lg font-semibold text-ink">{mechanic.name}</h2>
                                {mechanic.ai && <Badge tone="accent">Usa IA</Badge>}
                            </div>

                            <p className="mt-1 text-sm text-ink">{mechanic.summary}</p>
                            <p className="mt-3 max-w-2xl text-sm leading-relaxed text-ink-muted">
                                {mechanic.detail}
                            </p>
                        </Card>
                    ))}
                </div>

                <p className="mt-8 max-w-2xl text-sm text-ink-muted">
                    Cada caso elige sus mecánicas: uno puede apoyarse en la evidencia visual y
                    otro en los interrogatorios. En la página de cada caso está la lista exacta
                    de lo que incluye.
                </p>
            </Section>

            <Section tone="sunken">
                <div className="flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                    <div>
                        <h2 className="text-xl font-semibold text-ink">
                            Mira cómo se combinan en un caso real
                        </h2>
                        <p className="mt-2 text-sm text-ink-muted">
                            Cada caso indica qué mecánicas usa y para qué.
                        </p>
                    </div>
                    <Button href={route('cases.index')}>Ver los casos</Button>
                </div>
            </Section>
        </PublicLayout>
    );
}
