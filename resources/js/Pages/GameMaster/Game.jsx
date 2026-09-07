import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import GameMasterLayout from '../../Layouts/GameMasterLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import Badge from '../../components/ui/Badge';
import Alert from '../../components/ui/Alert';
import EmptyState from '../../components/ui/EmptyState';
import { ConfirmModal } from '../../components/ui/Modal';
import TimelineEventRow from '../../components/game-master/TimelineEventRow';
import usePoll from '../../hooks/usePoll';

function PlayerLink({ player }) {
    const url = route('immersion.player.inbox', player.access_token);
    const [copied, setCopied] = useState(false);

    async function copy() {
        try {
            await navigator.clipboard.writeText(url);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch {
            // Clipboard is unavailable outside a secure context; the link is
            // shown in full below so it can still be copied by hand.
            setCopied(false);
        }
    }

    return (
        <li className="border-b border-line py-3 last:border-0">
            <p className="text-sm font-medium text-ink">{player.name}</p>
            <p className="truncate text-xs text-ink-muted">{player.email}</p>

            <div className="mt-2 flex items-center gap-2">
                <code className="min-w-0 flex-1 truncate rounded-control bg-surface-sunken px-2 py-1.5 text-xs text-ink-muted">
                    {url}
                </code>
                <Button variant="secondary" size="sm" onClick={copy}>
                    {copied ? 'Copiado' : 'Copiar'}
                </Button>
            </div>
        </li>
    );
}

export default function Game({ game }) {
    // The clock and the timeline advance on the server, so a running game
    // refreshes itself; a draft or paused game has nothing to poll for.
    usePoll(['game'], { interval: 5000, enabled: game.status === 'running' });

    const [confirming, setConfirming] = useState(null);
    const [processing, setProcessing] = useState(false);

    function post(routeName, options = {}) {
        setProcessing(true);
        router.post(route(routeName, game.id), {}, {
            preserveScroll: true,
            onFinish: () => {
                setProcessing(false);
                setConfirming(null);
            },
            ...options,
        });
    }

    const hasTimeline = game.timeline_events.length > 0;
    const pending = game.timeline_events.filter((event) => !event.sent_at).length;

    return (
        <GameMasterLayout
            game={game}
            tab="panel"
            actions={
                <>
                    {game.status === 'draft' && (
                        <Button onClick={() => setConfirming('start')} disabled={!hasTimeline}>
                            Iniciar caso
                        </Button>
                    )}

                    {game.status === 'running' && (
                        <Button variant="secondary" onClick={() => post('immersion.gm.game.pause')}>
                            Pausar
                        </Button>
                    )}

                    {game.status === 'paused' && (
                        <Button onClick={() => post('immersion.gm.game.resume')}>Reanudar</Button>
                    )}
                </>
            }
        >
            <Head title={game.name} />

            <div className="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
                <Card as="section">
                    <CardHeader
                        title="Línea de tiempo"
                        description={
                            hasTimeline
                                ? `${pending} evento(s) pendiente(s) de ${game.timeline_events.length}.`
                                : undefined
                        }
                        actions={
                            hasTimeline &&
                            game.status !== 'draft' && (
                                <Button
                                    variant="secondary"
                                    size="sm"
                                    onClick={() => setConfirming('force')}
                                    disabled={pending === 0}
                                >
                                    Forzar siguiente
                                </Button>
                            )
                        }
                    />

                    {!hasTimeline ? (
                        <>
                            <Alert variant="warning" title="Esta partida no tiene línea de tiempo">
                                Ocurre con partidas creadas antes de un arreglo anterior. Sin
                                línea de tiempo no se enviará ningún correo.
                            </Alert>
                            <Button onClick={() => post('immersion.gm.game.load-default-timeline')}>
                                Cargar línea de tiempo del caso
                            </Button>
                        </>
                    ) : (
                        <ul>
                            {game.timeline_events.map((event) => (
                                <TimelineEventRow key={event.id} game={game} event={event} />
                            ))}
                        </ul>
                    )}
                </Card>

                <div className="space-y-6">
                    <Card as="section">
                        <CardHeader
                            title="Interrogatorio"
                            description="Mecánica de preguntas a los sospechosos por IA."
                        />

                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <Badge tone={game.interrogation_enabled ? 'success' : 'neutral'}>
                                {game.interrogation_enabled ? 'Habilitado' : 'Deshabilitado'}
                            </Badge>

                            <Button
                                variant="secondary"
                                size="sm"
                                onClick={() => post('immersion.gm.game.toggle-interrogation')}
                            >
                                {game.interrogation_enabled ? 'Deshabilitar' : 'Habilitar'}
                            </Button>
                        </div>
                    </Card>

                    <Card as="section">
                        <CardHeader
                            title="Jugadores"
                            description={`${game.players.length} en esta partida.`}
                        />

                        {game.players.length === 0 ? (
                            <EmptyState title="Esta partida no tiene jugadores" />
                        ) : (
                            <ul>
                                {game.players.map((player) => (
                                    <PlayerLink key={player.id} player={player} />
                                ))}
                            </ul>
                        )}
                    </Card>
                </div>
            </div>

            <ConfirmModal
                open={confirming === 'start'}
                onClose={() => setConfirming(null)}
                onConfirm={() => post('immersion.gm.game.start')}
                processing={processing}
                title="¿Iniciar el caso?"
                description="Arranca el reloj y los correos empezarán a salir solos según la línea de tiempo. El reloj no se puede reiniciar después."
                confirmLabel="Iniciar"
            />

            <ConfirmModal
                open={confirming === 'force'}
                onClose={() => setConfirming(null)}
                onConfirm={() => post('immersion.gm.game.force-next')}
                processing={processing}
                title="¿Forzar el siguiente evento?"
                description="Envía ya el próximo evento pendiente sin esperar su minuto. Si es un audio puede tardar hasta un minuto en generarse."
                confirmLabel="Forzar evento"
            />
        </GameMasterLayout>
    );
}
