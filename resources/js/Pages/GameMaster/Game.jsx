import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import GameMasterLayout from '../../Layouts/GameMasterLayout';
import Card, { CardHeader } from '../../components/ui/Card';
import Button from '../../components/ui/Button';
import Badge from '../../components/ui/Badge';
import Alert from '../../components/ui/Alert';
import EmptyState from '../../components/ui/EmptyState';
import Spinner from '../../components/ui/Spinner';
import { ConfirmModal } from '../../components/ui/Modal';
import TimelineEventRow from '../../components/game-master/TimelineEventRow';
import DeleteGameButton from '../../components/game-master/DeleteGameButton';
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
            <div className="flex flex-wrap items-center gap-2">
                <p className="text-sm font-medium text-ink">{player.name}</p>
                {player.is_owner && <Badge tone="accent">Tú</Badge>}
            </div>
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

/**
 * The AI reservation for this run.
 *
 * Before the case starts this is the warning that matters — a shortfall found
 * here is fixable, the same shortfall found mid-game is not. Afterwards it just
 * reports what is frozen and what will come back.
 */
function CreditsCard({ credits, status, onRearm, processing }) {
    const { cost, available, shortfall, hold, armed } = credits;

    if (cost.total === 0 && !hold) {
        return null;
    }

    if (armed) {
        return (
            <Card className="mb-6">
                <CardHeader
                    title="Créditos de IA"
                    description={`${hold.amount} reservados para esta partida.`}
                />
                <p className="text-sm text-ink-muted">
                    Van {hold.spent} usados. Los {hold.remaining} restantes vuelven a tu saldo
                    cuando cierres el caso o la pauses.
                </p>
            </Card>
        );
    }

    // The game had capacity and gave it back — paused, or swept after days of
    // inactivity. Without this card the symptom is a chat that says "sin
    // créditos" while the wallet is visibly full.
    if (hold && status !== 'finished') {
        return (
            <Card className="mb-6">
                <CardHeader
                    title="Créditos de IA"
                    description="Esta partida no tiene capacidad reservada ahora mismo."
                    actions={
                        status !== 'paused' && (
                            <Button size="sm" onClick={onRearm} loading={processing}>
                                Reactivar IA
                            </Button>
                        )
                    }
                />

                {shortfall > 0 ? (
                    <Alert variant="warning" className="mb-0">
                        Hacen falta {hold.remaining} créditos para que los sospechosos vuelvan a
                        responder, y tienes {available}. Recarga {shortfall} más.
                    </Alert>
                ) : (
                    <p className="text-sm text-ink-muted">
                        {status === 'paused'
                            ? `Te devolvimos ${hold.remaining} créditos mientras está en pausa. Se vuelven a reservar al reanudar.`
                            : `Los ${hold.remaining} créditos que le quedaban volvieron a tu saldo. Reactívala para seguir interrogando; lo ya usado no se cobra otra vez.`}
                    </p>
                )}
            </Card>
        );
    }

    if (status !== 'draft') {
        return null;
    }

    return (
        <Card className="mb-6">
            <CardHeader
                title="Créditos de IA"
                description={`Iniciar esta partida congelará ${cost.total} créditos.`}
                actions={
                    <Button href={route('credits')} variant="secondary" size="sm">
                        Ver créditos
                    </Button>
                }
            />

            {shortfall > 0 ? (
                <Alert variant="warning" title="No puedes iniciarla todavía" className="mb-0">
                    Tienes {available} créditos y hacen falta {cost.total}. Recarga {shortfall}{' '}
                    más, o crea la partida sin interrogatorio si prefieres jugarla así.
                </Alert>
            ) : (
                <p className="text-sm text-ink-muted">
                    Tienes {available} disponibles. Lo que la mesa no use vuelve a tu saldo al
                    cerrar el caso.
                </p>
            )}
        </Card>
    );
}

export default function Game({ game, timelineSummary, can, ownerPlayerToken, ending, credits }) {
    // The advanced endings finish on the queue after the reveal, so the console
    // has to keep looking until the recording or the last epilogue lands.
    const endingIsWorking =
        ending.audio_status === 'pending' ||
        (ending.epilogues && ending.epilogues.sent < ending.epilogues.total);

    // The clock and the timeline advance on the server, so a running game
    // refreshes itself; a draft, paused or finished game has nothing to poll.
    usePoll(['game', 'timelineSummary', 'ending'], {
        interval: 5000,
        enabled: game.status === 'running' || Boolean(endingIsWorking),
    });

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

    const hasTimeline = timelineSummary.total > 0;
    const pending = timelineSummary.total - timelineSummary.sent;
    const isAutomatic = game.mode === 'automatic';
    const finished = game.status === 'finished';

    // Disabling the button is a courtesy; the server refuses the start either
    // way. Credits arrive lazily, so a missing prop must not block the button.
    const shortOnCredits = (credits?.shortfall ?? 0) > 0;

    return (
        <GameMasterLayout
            game={game}
            tab="panel"
            can={can}
            actions={
                <>
                    {game.status === 'draft' && (
                        <Button
                            onClick={() => setConfirming('start')}
                            disabled={!hasTimeline || shortOnCredits}
                            title={
                                shortOnCredits
                                    ? 'No tienes créditos de IA suficientes'
                                    : undefined
                            }
                        >
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

                    {can.reveal && (
                        <Button onClick={() => setConfirming('reveal')}>Revelar solución</Button>
                    )}

                    {(game.status === 'running' || game.status === 'paused') && (
                        <Button variant="secondary" onClick={() => setConfirming('finish')}>
                            Cerrar caso
                        </Button>
                    )}

                    <DeleteGameButton game={game} variant="ghost" />
                </>
            }
        >
            <Head title={game.name} />

            {/* In automatic mode the owner is a player: the first thing they
                need is the way into their own inbox. */}
            {isAutomatic && ownerPlayerToken && (
                <Card className="mb-6">
                    <CardHeader
                        title="Tú también juegas"
                        description="Esta partida se dirige sola. Tu bandeja es como la de cualquier otro investigador."
                        actions={
                            <Button href={route('immersion.player.inbox', ownerPlayerToken)}>
                                Abrir mi bandeja
                            </Button>
                        }
                    />
                    {!finished && (
                        <p className="text-sm text-ink-muted">
                            Los interrogatorios y las acusaciones del resto quedan ocultos hasta
                            que cierres el caso, para no arruinarte la partida.
                        </p>
                    )}
                </Card>
            )}

            {credits && (
                <CreditsCard
                    credits={credits}
                    status={game.status}
                    processing={processing}
                    onRearm={() => post('immersion.gm.game.rearm-credits')}
                />
            )}

            {/* Where the run stands relative to its ending. */}
            {game.status !== 'draft' && (
                <Card className="mb-6">
                    <CardHeader
                        title="Final del caso"
                        description={
                            ending.revealed_at
                                ? 'La solución ya está publicada para todo el equipo.'
                                : `${ending.players - ending.pending_accusations} de ${ending.players} acusaciones recibidas.`
                        }
                        actions={
                            ending.revealed_at && can.viewSpoilers ? (
                                <Button
                                    href={route('immersion.gm.game.results', game.id)}
                                    variant="secondary"
                                    size="sm"
                                >
                                    Ver acusaciones
                                </Button>
                            ) : null
                        }
                    />

                    {!ending.revealed_at && (
                        <p className="text-sm text-ink-muted">
                            {ending.pending_accusations === 0
                                ? 'Ya acusaron todos: la solución se revela sola.'
                                : 'Cuando acusen todos, la solución se revela sola. También puedes revelarla tú si alguien no va a acusar.'}
                        </p>
                    )}

                    {/* Confession audio: generated on reveal, played by the
                        Game Master at the table. */}
                    {ending.revealed_at && ending.audio_status && (
                        <div className="mt-4 border-t border-line pt-4">
                            <p className="text-sm font-medium text-ink">
                                Confesión del culpable
                            </p>

                            {ending.audio_status === 'ready' ? (
                                <>
                                    <p className="mt-1 text-sm text-ink-muted">
                                        Súbele el volumen y reprodúcela en la mesa antes de
                                        cerrar el caso. Solo tú la tienes.
                                    </p>
                                    <audio
                                        controls
                                        preload="none"
                                        src={route('immersion.gm.game.ending-audio', game.id)}
                                        className="mt-3 w-full"
                                    >
                                        Tu navegador no puede reproducir audio.
                                    </audio>
                                </>
                            ) : ending.audio_status === 'pending' ? (
                                <p className="mt-1 flex items-center gap-2 text-sm text-ink-muted">
                                    <Spinner />
                                    Generando la grabación. Puede tardar un minuto.
                                </p>
                            ) : (
                                <Alert variant="warning" className="mt-2 mb-0">
                                    No se pudo generar la grabación. La solución escrita ya
                                    está publicada para todo el equipo, así que el caso tiene
                                    su final igual.
                                </Alert>
                            )}
                        </div>
                    )}

                    {/* Epilogues: one email per accusation, written after the
                        reveal. */}
                    {ending.revealed_at && ending.epilogues && (
                        <div className="mt-4 border-t border-line pt-4">
                            <p className="text-sm font-medium text-ink">Epílogos por correo</p>
                            <p className="mt-1 text-sm text-ink-muted">
                                {ending.epilogues.sent} de {ending.epilogues.total} enviados.
                                {ending.epilogues.sent < ending.epilogues.total &&
                                    ' Los demás se están escribiendo.'}
                            </p>

                            {ending.epilogues.failed > 0 && (
                                <Alert variant="warning" className="mt-2 mb-0">
                                    {ending.epilogues.failed}{' '}
                                    {ending.epilogues.failed === 1
                                        ? 'jugador no recibió el suyo'
                                        : 'jugadores no recibieron el suyo'}
                                    . Todos ven la solución completa de todos modos.
                                </Alert>
                            )}
                        </div>
                    )}
                </Card>
            )}

            <div className="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">
                <Card as="section">
                    <CardHeader
                        title="Línea de tiempo"
                        description={
                            hasTimeline
                                ? `${timelineSummary.sent} de ${timelineSummary.total} eventos enviados.`
                                : undefined
                        }
                        actions={
                            can.direct &&
                            hasTimeline &&
                            game.status !== 'draft' &&
                            !finished && (
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
                    ) : can.viewSpoilers ? (
                        <ul>
                            {game.timeline_events.map((event) => (
                                <TimelineEventRow key={event.id} game={game} event={event} />
                            ))}
                        </ul>
                    ) : (
                        // Event titles give the case away, so a playing owner
                        // gets progress instead of contents.
                        <div>
                            <div
                                className="h-2 overflow-hidden rounded-full bg-surface-sunken"
                                role="progressbar"
                                aria-valuenow={timelineSummary.sent}
                                aria-valuemin={0}
                                aria-valuemax={timelineSummary.total}
                                aria-label="Progreso de la línea de tiempo"
                            >
                                <div
                                    className="h-full rounded-full bg-accent transition-all"
                                    style={{
                                        width: `${(timelineSummary.sent / timelineSummary.total) * 100}%`,
                                    }}
                                />
                            </div>
                            <p className="mt-3 text-sm text-ink-muted">
                                {pending === 0
                                    ? 'Ya salió todo el material del caso.'
                                    : `Quedan ${pending} evento(s) por llegar. El sistema los envía solo.`}
                            </p>
                        </div>
                    )}
                </Card>

                <div className="space-y-6">
                    <Card as="section">
                        <CardHeader
                            title="Interrogatorio"
                            description={
                                isAutomatic
                                    ? 'Se habilita solo cuando el caso lo indica.'
                                    : 'Mecánica de preguntas a los sospechosos por IA.'
                            }
                        />

                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <Badge tone={game.interrogation_enabled ? 'success' : 'neutral'}>
                                {game.interrogation_enabled ? 'Habilitado' : 'Deshabilitado'}
                            </Badge>

                            {can.direct && (
                                <Button
                                    variant="secondary"
                                    size="sm"
                                    onClick={() => post('immersion.gm.game.toggle-interrogation')}
                                >
                                    {game.interrogation_enabled ? 'Deshabilitar' : 'Habilitar'}
                                </Button>
                            )}
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
                description={
                    isAutomatic
                        ? 'Arranca el reloj y el sistema empieza a enviar el material solo. El reloj no se puede reiniciar después.'
                        : 'Arranca el reloj y los correos empezarán a salir solos según la línea de tiempo. El reloj no se puede reiniciar después.'
                }
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

            <ConfirmModal
                open={confirming === 'reveal'}
                onClose={() => setConfirming(null)}
                onConfirm={() => post('immersion.gm.game.reveal')}
                processing={processing}
                title="¿Revelar la solución?"
                description={
                    isAutomatic
                        ? 'Le muestra el final a todo el equipo, incluido a ti. No se puede deshacer.'
                        : 'Le muestra el final a todo el equipo y cierra las acusaciones. No se puede deshacer.'
                }
                confirmLabel="Revelar"
            >
                {ending.pending_accusations > 0 && (
                    <p className="text-sm text-danger">
                        Faltan {ending.pending_accusations} de {ending.players} acusaciones. Quien
                        no haya acusado ya no podrá hacerlo.
                    </p>
                )}
            </ConfirmModal>

            <ConfirmModal
                open={confirming === 'finish'}
                onClose={() => setConfirming(null)}
                onConfirm={() => post('immersion.gm.game.finish')}
                processing={processing}
                title="¿Cerrar el caso?"
                description={
                    isAutomatic
                        ? 'Se detiene el reloj y deja de llegar material. Después podrás ver los interrogatorios y las acusaciones de todos.'
                        : 'Se detiene el reloj y deja de enviarse material. Esto no se puede deshacer.'
                }
                confirmLabel="Cerrar caso"
            />
        </GameMasterLayout>
    );
}
