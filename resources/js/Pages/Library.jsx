import { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';
import Card from '../components/ui/Card';
import Button from '../components/ui/Button';
import Alert from '../components/ui/Alert';
import Badge from '../components/ui/Badge';
import Modal from '../components/ui/Modal';
import EmptyState from '../components/ui/EmptyState';
import { CaseFacts } from '../components/public/CaseCard';
import TextLink from '../components/ui/TextLink';
import QuotaMeter from '../components/app/QuotaMeter';

/**
 * The case, without leaving the library. "Ver el caso" used to send the owner
 * to the public sales page, which is the page for someone who has not logged
 * in yet — and 404s for a case that is theirs but not published.
 */
function CaseModal({ mysteryCase, onClose }) {
    return (
        <Modal
            open={Boolean(mysteryCase)}
            onClose={onClose}
            size="lg"
            title={mysteryCase?.name}
            description={mysteryCase?.tagline}
            footer={
                mysteryCase && (
                    <Button variant="ghost" onClick={onClose}>
                        Cerrar
                    </Button>
                )
            }
        >
            {mysteryCase && (
                <div className="space-y-5">
                    <CaseFacts mysteryCase={mysteryCase} />

                    {/* The reason someone opens this is to start a game, so the
                        button is up here where a phone shows it without
                        scrolling past the cover and the whole description. */}
                    {mysteryCase.playable && !mysteryCase.quota.full && (
                        <Button
                            href={route('immersion.gm.games.create', { case: mysteryCase.slug })}
                            fullWidth
                        >
                            Crear partida con este caso
                        </Button>
                    )}

                    {mysteryCase.quota.full && (
                        <Alert variant="warning" className="mb-0">
                            Ya usaste todas las partidas de este caso. Borra una para liberar un cupo.
                        </Alert>
                    )}

                    {mysteryCase.cover_url && (
                        <img
                            src={mysteryCase.cover_url}
                            alt=""
                            className="h-40 w-full rounded-card border border-line object-cover sm:h-56"
                        />
                    )}

                    {mysteryCase.description && (
                        <div className="space-y-3 text-sm leading-relaxed text-ink-muted">
                            {mysteryCase.description.split(/\n{2,}/).map((paragraph, index) => (
                                <p key={index}>{paragraph.trim()}</p>
                            ))}
                        </div>
                    )}

                    {mysteryCase.mechanics?.length > 0 && (
                        <details className="group rounded-control border border-line">
                            <summary className="flex min-h-11 cursor-pointer list-none items-center justify-between px-4 text-sm font-semibold text-ink">
                                Qué incluye este caso
                                <span aria-hidden="true" className="text-ink-subtle group-open:rotate-180">
                                    ▾
                                </span>
                            </summary>
                            <ul className="grid gap-2 border-t border-line p-3 sm:grid-cols-2">
                                {mysteryCase.mechanics.map((mechanic) => (
                                    <li key={mechanic.slug} className="rounded-control bg-surface-sunken p-3">
                                        <p className="flex items-center gap-2 text-sm font-semibold text-ink">
                                            {mechanic.name}
                                            {mechanic.ai && <Badge tone="accent">IA</Badge>}
                                        </p>
                                        <p className="mt-1 text-xs text-ink-muted">{mechanic.detail}</p>
                                    </li>
                                ))}
                            </ul>
                        </details>
                    )}
                </div>
            )}
        </Modal>
    );
}

function LibraryCase({ mysteryCase, onView }) {
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
                <h2 className="text-lg font-semibold text-ink">{mysteryCase.name}</h2>

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
                        <QuotaMeter quota={mysteryCase.quota} className="mt-4" />

                        <div className="mt-5 flex flex-wrap gap-2">
                            {mysteryCase.quota.full ? (
                                <Button size="sm" disabled>
                                    Crear partida
                                </Button>
                            ) : (
                                <Button
                                    href={route('immersion.gm.games.create', { case: mysteryCase.slug })}
                                    size="sm"
                                >
                                    Crear partida
                                </Button>
                            )}
                            <Button variant="secondary" size="sm" onClick={() => onView(mysteryCase)}>
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
    const [viewing, setViewing] = useState(null);

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
                        <LibraryCase key={item.slug} mysteryCase={item} onView={setViewing} />
                    ))}
                </div>
            )}

            <CaseModal mysteryCase={viewing} onClose={() => setViewing(null)} />

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
