import { useCallback, useEffect, useRef, useState } from 'react';
import { Head } from '@inertiajs/react';
import ExpressLayout, { ExpressContainer } from '../../Layouts/ExpressLayout';
import Button from '../../components/ui/Button';
import Meter from '../../components/ui/Meter';
import Spinner from '../../components/ui/Spinner';
import AccuseForm from '../../components/express/AccuseForm';
import BottomTabs from '../../components/express/BottomTabs';
import CluesTab from '../../components/express/CluesTab';
import CountdownChip from '../../components/express/CountdownChip';
import InterrogateTab from '../../components/express/InterrogateTab';
import LobbyView from '../../components/express/LobbyView';
import RevealPanel from '../../components/express/RevealPanel';
import useExpressRoom from '../../hooks/useExpressRoom';
import { secondsLeft } from '../../lib/express';

const TOAST_MS = 6000;
const NEW_BADGE_MS = 20000;

/** A short message above the tab bar. `action` is an optional one-tap shortcut. */
function Toast({ toast, onClose }) {
    if (!toast) return null;

    return (
        <div
            role="status"
            aria-live="polite"
            className="fixed inset-x-4 bottom-[calc(5rem+env(safe-area-inset-bottom))] z-30 mx-auto flex max-w-md items-center gap-3 rounded-card border border-accent bg-surface-raised px-4 py-3 text-sm text-ink shadow-overlay"
        >
            <span className="min-w-0 flex-1">{toast.text}</span>
            {toast.action && (
                <button
                    type="button"
                    onClick={() => {
                        toast.action.run();
                        onClose();
                    }}
                    className="min-h-11 shrink-0 px-2 font-semibold text-accent-strong"
                >
                    {toast.action.label}
                </button>
            )}
            <button
                type="button"
                onClick={onClose}
                aria-label="Cerrar aviso"
                className="flex h-11 w-9 shrink-0 items-center justify-center text-ink-muted"
            >
                ×
            </button>
        </div>
    );
}

/** Sticky strip: which case, which phase, how long until the next thing. */
function RoomHeader({ state, clock, elapsedS }) {
    const hardClose = state.room.schedule?.hard_close_s;

    return (
        <div className="sticky top-0 z-10 border-b border-line bg-surface/95">
            <ExpressContainer className="py-2.5">
                <div className="flex items-center justify-between gap-3">
                    <p className="min-w-0 truncate text-sm font-semibold text-ink">{state.room.case?.title}</p>
                    <span className="shrink-0 font-mono text-xs text-ink-subtle">{state.room.code}</span>
                </div>

                {clock ? (
                    <div className="mt-2">
                        <CountdownChip
                            label={clock.label}
                            seconds={secondsLeft(clock.leftS)}
                            caption={clock.caption}
                        />
                        {typeof hardClose === 'number' && elapsedS != null && (
                            <Meter
                                value={Math.min(elapsedS, hardClose)}
                                max={hardClose}
                                tone="accent"
                                label="Avance de la ronda"
                                className="mt-2"
                            />
                        )}
                    </div>
                ) : null}
            </ExpressContainer>
        </div>
    );
}

export default function Room({ state: initialState, token }) {
    const { state, elapsedS, clock, act, gone } = useExpressRoom(initialState, token);

    const status = state.room?.status;
    const live = status === 'playing' || status === 'accusing';
    // `accusation.open` is false once I have accused (the form is done), but
    // the "waiting for the others" screen still lives in the same tab.
    const accusationOpen = Boolean(state.accusation?.open);
    const accuseAvailable = accusationOpen || Boolean(state.accusation?.submitted);

    const [tab, setTab] = useState(accusationOpen ? 'accuse' : 'clues');
    // The effect below reacts to new clues, not to tab changes, so it reads the tab through a ref.
    const tabRef = useRef(tab);
    tabRef.current = tab;
    const [toast, setToast] = useState(null);
    const [sharingId, setSharingId] = useState(null);
    const [newIds, setNewIds] = useState(() => new Set());
    const [cluesDot, setCluesDot] = useState(false);

    const toastTimer = useRef(null);
    const seenEvidence = useRef(new Set((initialState.evidence ?? []).map((item) => item.id)));
    const wasOpen = useRef(accusationOpen);

    const showToast = useCallback((text, action = null) => {
        clearTimeout(toastTimer.current);
        setToast({ text, action });
        toastTimer.current = setTimeout(() => setToast(null), TOAST_MS);
    }, []);

    useEffect(() => () => clearTimeout(toastTimer.current), []);

    // New clues: mark them, and tell the player if they are looking elsewhere.
    const evidence = state.evidence;

    useEffect(() => {
        const fresh = (Array.isArray(evidence) ? evidence : []).filter(
            (item) => !seenEvidence.current.has(item.id)
        );

        if (fresh.length === 0) return;

        fresh.forEach((item) => seenEvidence.current.add(item.id));

        setNewIds((current) => new Set([...current, ...fresh.map((item) => item.id)]));
        setTimeout(() => {
            setNewIds((current) => {
                const next = new Set(current);
                fresh.forEach((item) => next.delete(item.id));

                return next;
            });
        }, NEW_BADGE_MS);

        if (status === 'playing' || status === 'accusing') {
            // Already looking at the clues, or the opening clues of the round
            // (they are the first thing on screen): a toast would only cover them.
            const lookingAtClues = tabRef.current === 'clues';
            const openingClues = fresh.every((item) => item.unlocked_at_s === 0 && !item.shared);

            if (!lookingAtClues) setCluesDot(true);

            if (!lookingAtClues && !openingClues) {
                const sharedBy = fresh.find((item) => item.shared && item.shared_by)?.shared_by;

                showToast(sharedBy ? `${sharedBy} compartió una pista.` : 'Llegó una pista nueva.', {
                    label: 'Verla',
                    run: () => setTab('clues'),
                });
            }
        }
    }, [evidence, status, showToast]);

    useEffect(() => {
        if (tab === 'clues') setCluesDot(false);
    }, [tab]);

    // The accusation window opening is the moment of the game: take everyone there.
    useEffect(() => {
        if (accusationOpen && !wasOpen.current) {
            setTab('accuse');
            showToast('¡Se abrieron las acusaciones!');
        }

        wasOpen.current = accusationOpen;
    }, [accusationOpen, showToast]);

    // A tab that is no longer reachable (never the case once open, but safe).
    useEffect(() => {
        if (tab === 'accuse' && !accuseAvailable) setTab('clues');
    }, [tab, accuseAvailable]);

    const share = useCallback(
        async (item) => {
            setSharingId(item.id);

            const result = await act('express.share', { evidence_id: item.id });

            setSharingId(null);
            showToast(result.ok ? 'Pista compartida con el grupo.' : result.message || 'No se pudo compartir.');
        },
        [act, showToast]
    );

    const ask = useCallback((payload) => act('express.ask', payload), [act]);
    const accuse = useCallback((payload) => act('express.accuse', payload), [act]);
    const start = useCallback(() => act('express.start', {}), [act]);

    const tabs = [
        { key: 'clues', label: 'Pistas', icon: 'clues', dot: cluesDot },
        { key: 'ask', label: 'Interrogar', icon: 'ask' },
        {
            key: 'accuse',
            label: 'Acusar',
            icon: 'accuse',
            disabled: !accuseAvailable,
            hint: 'se abre más adelante',
        },
    ];

    let content;

    if (gone) {
        return (
            <ExpressLayout brandHref={route('express.home')}>
                <Head title="Enlace no válido" />
                <ExpressContainer className="flex flex-col items-center gap-4 py-16 text-center">
                    <h1 className="font-display text-2xl font-semibold text-ink">Enlace no válido</h1>
                    <p className="max-w-sm text-sm text-ink-muted">
                        Esta sala ya no existe o el enlace está incompleto. Si tus amigos siguen jugando,
                        pídeles que te compartan el código de la sala de nuevo.
                    </p>
                    <Button href={route('express.home')} size="lg">
                        Ir al inicio
                    </Button>
                </ExpressContainer>
            </ExpressLayout>
        );
    }

    if (status === 'lobby') {
        content = <LobbyView state={state} onStart={start} />;
    } else if (live) {
        content = (
            <>
                <RoomHeader state={state} clock={clock} elapsedS={elapsedS} />

                <ExpressContainer className="py-5">
                    <div
                        role="tabpanel"
                        id={`express-panel-${tab}`}
                        aria-labelledby={`express-tab-${tab}`}
                        tabIndex={-1}
                    >
                        {tab === 'clues' && (
                            <CluesTab
                                state={state}
                                elapsedS={elapsedS}
                                clock={clock}
                                newIds={newIds}
                                onShare={share}
                                sharingId={sharingId}
                            />
                        )}
                        {tab === 'ask' && <InterrogateTab state={state} clock={clock} onAsk={ask} />}
                        {tab === 'accuse' && (
                            <AccuseForm state={state} clock={clock} players={state.players} onSubmit={accuse} />
                        )}
                    </div>
                </ExpressContainer>
            </>
        );
    } else if (status === 'reveal' && state.reveal) {
        content = (
            <ExpressContainer className="py-6">
                <RevealPanel state={state} act={act} />
            </ExpressContainer>
        );
    } else if (status === 'expired') {
        content = (
            <ExpressContainer className="flex flex-col items-center gap-4 py-16 text-center">
                <h1 className="font-display text-2xl font-semibold text-ink">Esta sala caducó</h1>
                <p className="max-w-sm text-sm text-ink-muted">
                    La ronda no se terminó a tiempo y la sala se cerró. Pueden armar una nueva en un momento.
                </p>
                <Button href={route('express.create', state.room?.event?.slug ? { e: state.room.event.slug } : {})} size="lg">
                    Crear una sala nueva
                </Button>
            </ExpressContainer>
        );
    } else {
        // `reveal` without its payload yet, or a status we do not know:
        // wait, the next poll fills it in.
        content = (
            <ExpressContainer className="flex flex-col items-center gap-3 py-16 text-center">
                <Spinner size="lg" />
                <p className="text-sm text-ink-muted">Preparando el desenlace…</p>
            </ExpressContainer>
        );
    }

    return (
        <ExpressLayout
            brandHref={route('express.home')}
            bar={live ? <BottomTabs tabs={tabs} active={tab} onChange={setTab} /> : null}
        >
            <Head title={`Sala ${state.room?.code ?? ''}`} />
            {content}
            <Toast toast={toast} onClose={() => setToast(null)} />
        </ExpressLayout>
    );
}
