import { Head } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Card from '../components/ui/Card';
import Button from '../components/ui/Button';
import Badge from '../components/ui/Badge';
import Alert from '../components/ui/Alert';
import EmptyState from '../components/ui/EmptyState';
import { CaseFacts } from '../components/public/CaseCard';
import TextLink from '../components/ui/TextLink';

function LibraryCase({ mysteryCase }) {
    return (
        <Card as="article" className="flex flex-col sm:flex-row sm:gap-6">
            {mysteryCase.cover_url && (
                <img
                    src={mysteryCase.cover_url}
                    alt=""
                    loading="lazy"
                    className="mb-4 h-40 w-full shrink-0 rounded-card object-cover sm:mb-0 sm:h-32 sm:w-32"
                />
            )}

            <div className="flex min-w-0 flex-1 flex-col">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <h2 className="font-semibold text-ink">{mysteryCase.name}</h2>
                    <Badge tone={mysteryCase.quota.full ? 'danger' : 'neutral'}>
                        {mysteryCase.quota.used}/{mysteryCase.quota.limit} partidas
                    </Badge>
                </div>

                {mysteryCase.tagline && (
                    <p className="mt-2 text-sm text-ink-muted">{mysteryCase.tagline}</p>
                )}

                <CaseFacts mysteryCase={mysteryCase} className="mt-3" />

                {!mysteryCase.playable ? (
                    <Alert variant="warning" className="mt-4 mb-0">
                        Este caso no está instalado en el servidor ahora mismo, así que no se
                        pueden crear partidas. Sigue siendo tuyo.
                    </Alert>
                ) : (
                    <>
                        {mysteryCase.quota.full && (
                            <p className="mt-3 text-xs text-danger-strong">
                                Sin cupo. Elimina una partida de este caso para crear otra.
                            </p>
                        )}

                        <div className="mt-5 flex flex-wrap gap-2">
                            {mysteryCase.quota.full ? (
                                <Button size="sm" disabled title="Sin cupo para este caso">
                                    Crear partida
                                </Button>
                            ) : (
                                <Button href={route('immersion.gm.games.create')} size="sm">
                                    Crear partida
                                </Button>
                            )}
                            <Button
                                href={route('cases.show', mysteryCase.slug)}
                                variant="secondary"
                                size="sm"
                            >
                                Ver el caso
                            </Button>
                        </div>
                    </>
                )}
            </div>
        </Card>
    );
}

export default function Library({ cases }) {
    return (
        <AppLayout
            current="library"
            kicker="Tus casos"
            title="Biblioteca"
            actions={
                <Button href={route('cases.index')} variant="secondary">
                    Explorar catálogo
                </Button>
            }
        >
            <Head title="Biblioteca" />

            {cases.length === 0 ? (
                <EmptyState
                    title="Todavía no tienes ningún caso"
                    description="Los casos que adquieras quedan aquí de forma permanente, y puedes dirigir partidas de cada uno las veces que quieras."
                    action={<Button href={route('cases.index')}>Ver los casos disponibles</Button>}
                />
            ) : (
                <div className="space-y-5">
                    {cases.map((item) => (
                        <LibraryCase key={item.slug} mysteryCase={item} />
                    ))}
                </div>
            )}

            {cases.length > 0 && (
                <p className="mt-8 text-sm text-ink-muted">
                    Puedes tener hasta {cases[0].quota.limit} partidas de cada caso. El cupo es
                    independiente por caso: llenar uno no afecta a los demás.
                </p>
            )}

            <p className="mt-3 text-sm text-ink-muted">
                ¿Buscas otro misterio?{' '}
                <TextLink href={route('cases.index')}>
                    Mira el catálogo completo
                </TextLink>
                .
            </p>
        </AppLayout>
    );
}
