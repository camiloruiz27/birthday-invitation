import { useCallback, useEffect, useState } from 'react';
import { Head } from '@inertiajs/react';
import PlayerLayout from '../../Layouts/PlayerLayout';
import EmptyState from '../../components/ui/EmptyState';
import InboxItem, { EnvelopeRow } from '../../components/player/InboxItem';
import usePoll from '../../hooks/usePoll';

/** `#sobre-12` opens envelope 12; `#sobre-12-alguna-seccion` opens it too and
 *  lets CaseDocument scroll to the section inside. */
const ENVELOPE_HASH = /^sobre-(\d+)/;

function envelopeFromHash() {
    if (typeof window === 'undefined') {
        return null;
    }

    const match = ENVELOPE_HASH.exec(window.location.hash.slice(1));

    return match ? Number(match[1]) : null;
}

/**
 * Which envelope is open lives in the URL hash, not in component state.
 *
 * That is what makes the phone's back gesture close the envelope instead of
 * leaving the game, and what lets one player send another a link straight to
 * a piece of evidence.
 */
function useOpenEnvelope() {
    const [openId, setOpenId] = useState(envelopeFromHash);

    useEffect(() => {
        const sync = () => setOpenId(envelopeFromHash());

        window.addEventListener('hashchange', sync);

        return () => window.removeEventListener('hashchange', sync);
    }, []);

    return openId;
}

const READ_KEY = (gameId) => `case-inbox:${gameId}:read`;

function readEnvelopes(gameId) {
    try {
        return new Set(JSON.parse(window.localStorage.getItem(READ_KEY(gameId)) ?? '[]'));
    } catch {
        // Private browsing throws. An envelope that forgets it was read is a
        // cosmetic problem; a page that fails to render is not.
        return new Set();
    }
}

export default function Inbox({ player, game, items, case: mysteryCase }) {
    // New envelopes arrive on the server's clock, so the inbox refreshes
    // itself while the player is reading.
    usePoll(['items'], { interval: 15000 });

    const openId = useOpenEnvelope();
    const selected = items.find((item) => item.event.id === openId) ?? null;

    // localStorage, not session: which envelopes you have read is worth
    // keeping if the phone reloads mid-game, and it is scoped per game so
    // the next case starts clean.
    const [read, setRead] = useState(() =>
        typeof window === 'undefined' ? new Set() : readEnvelopes(game.id)
    );

    const markRead = useCallback(
        (eventId) =>
            setRead((previous) => {
                if (previous.has(eventId)) {
                    return previous;
                }

                const next = new Set(previous).add(eventId);

                try {
                    window.localStorage.setItem(READ_KEY(game.id), JSON.stringify([...next]));
                } catch {
                    /* see readEnvelopes */
                }

                return next;
            }),
        [game.id]
    );

    useEffect(() => {
        if (openId !== null) {
            markRead(openId);
        }
    }, [openId, markRead]);

    // Opening from the list should start at the top of the envelope. When
    // the hash points at a section inside it, leave the scrolling to
    // CaseDocument instead of fighting it.
    useEffect(() => {
        if (openId === null || typeof window === 'undefined') {
            return;
        }

        if (window.location.hash.slice(1) === `sobre-${openId}`) {
            // `instant` overrides the global scroll-behavior: smooth, which
            // would animate all the way up a 4,000-word envelope.
            window.scrollTo({ top: 0, behavior: 'instant' });
        }
    }, [openId]);

    const unreadCount = items.filter((item) => !read.has(item.event.id)).length;

    return (
        <PlayerLayout
            player={player}
            game={game}
            section="inbox"
            kicker={`Caso ${mysteryCase.code} · Confidencial`}
            title={`Bandeja de ${player.name}`}
        >
            <Head title={`Bandeja de ${player.name}`} />

            {items.length === 0 ? (
                <EmptyState
                    title="Sin mensajes todavía"
                    description="El expediente llegará por partes a medida que avance la investigación. Deja esta página abierta: se actualiza sola."
                />
            ) : (
                <div className="lg:grid lg:grid-cols-[20rem_1fr] lg:items-start lg:gap-8">
                    {/* On a phone the list and the envelope take turns; from
                        lg they sit side by side like a mail client. */}
                    <div className={selected ? 'hidden lg:block' : ''}>
                        <div className="mb-3 flex items-baseline justify-between gap-3">
                            <h2 className="text-sm font-semibold text-ink">
                                <span className="tabular">{items.length}</span>{' '}
                                {items.length === 1 ? 'sobre' : 'sobres'}
                            </h2>
                            {unreadCount > 0 && (
                                <p className="text-xs text-accent-strong" aria-live="polite">
                                    <span className="tabular">{unreadCount}</span> sin abrir
                                </p>
                            )}
                        </div>

                        <ul className="space-y-2.5">
                            {items.map((item, index) => (
                                <EnvelopeRow
                                    key={item.event.id}
                                    item={item}
                                    number={index + 1}
                                    active={item.event.id === openId}
                                    unread={!read.has(item.event.id)}
                                />
                            ))}
                        </ul>
                    </div>

                    <div className={selected ? '' : 'hidden lg:block'}>
                        {selected ? (
                            <>
                                <a
                                    href="#"
                                    className="mb-4 inline-flex min-h-11 items-center text-sm text-ink-muted hover:text-ink lg:hidden"
                                >
                                    &larr; Todos los sobres
                                </a>

                                <InboxItem
                                    key={selected.event.id}
                                    item={selected}
                                    playerToken={player.access_token}
                                    game={game}
                                />
                            </>
                        ) : (
                            <EmptyState
                                title="Elige un sobre"
                                description="Cada sobre trae una parte del expediente. Ábrelos en orden si es tu primera vuelta."
                            />
                        )}
                    </div>
                </div>
            )}
        </PlayerLayout>
    );
}
