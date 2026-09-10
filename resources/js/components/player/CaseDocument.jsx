import { useCallback, useEffect, useId, useLayoutEffect, useMemo, useRef, useState } from 'react';
import { splitCaseDocument } from '../../lib/caseDocument';
import Sheet from './paper/Sheet';
import Stamp from './paper/Stamp';

/**
 * Renders one case document — an envelope body, the solution, a suspect's
 * official statement — as navigable sheets instead of one enormous blob.
 *
 * WHY THIS EXISTS: sobre-2 is ~3,900 words and nine complete interrogation
 * transcripts. As a single dangerouslySetInnerHTML div it is a wall a player
 * scrolls past rather than reads, and there is no way to treat one testimony
 * as a thing you can hold next to another — which is the actual game.
 *
 * WHAT IT REFUSES TO DO: a document with no headings, or with a single
 * section, renders as one plain sheet with no toolbar and no rows. Chrome is
 * earned by having something to navigate. And sobre-3's body_html arrives
 * literally empty (all of its sections are gallery duplicates, dropped
 * server-side), so `emptyFallback` is a live code path, not padding.
 *
 * WHY CLOSED SECTIONS STAY MOUNTED: the dominant player behaviour is Ctrl+F
 * ("who mentioned the chess piece?"). An accordion that unmounts what it
 * closes destroys that silently — nobody files a bug, they just fail to
 * solve the case. Sections are hidden with hidden="until-found" instead,
 * which Chrome and Edge expand on find-in-page; Firefox and Safari treat the
 * unknown value as a plain hidden, i.e. an ordinary closed accordion. Page
 * weight does not change, because today's blob already ships every byte.
 */

const LEVELS = { 2: 'h2', 3: 'h3', 4: 'h4', 5: 'h5' };

/**
 * Both gates, not either.
 *
 * Section count alone would collapse the solution page (7 sections, 2,288
 * words) — the ending reveal, written to be read straight through and out
 * loud at the table. Requiring length too leaves suspect statements and
 * sobre-1 open and collapses only sobre-2, which is the document that
 * actually reads as a wall.
 */
function shouldCollapseByDefault(doc, collapsibleCount) {
    return collapsibleCount >= 4 && doc.words >= 1200;
}

function initialOpenIds(doc, initial) {
    const collapsible = doc.pieces.filter((piece) => piece.collapsible);
    const alwaysOpen = doc.pieces.filter((piece) => !piece.collapsible).map((piece) => piece.id);

    let mode = initial;

    if (initial === 'auto') {
        mode = shouldCollapseByDefault(doc, collapsible.length) ? 'first' : 'all';
    }

    if (mode === 'all') {
        return new Set(doc.pieces.map((piece) => piece.id));
    }

    if (mode === 'none') {
        return new Set(alwaysOpen);
    }

    // 'first': one section open is the worked example that shows the player
    // the other rows open too.
    return new Set([...alwaysOpen, ...collapsible.slice(0, 1).map((piece) => piece.id)]);
}

function readStorage(key) {
    try {
        return new Set(JSON.parse(window.sessionStorage.getItem(key) ?? '[]'));
    } catch {
        // Safari in private mode throws on sessionStorage. Read marks are a
        // nicety; losing them must never take the document down with them.
        return new Set();
    }
}

function writeStorage(key, ids) {
    try {
        window.sessionStorage.setItem(key, JSON.stringify([...ids]));
    } catch {
        /* see readStorage */
    }
}

/**
 * @param {object} props
 * @param {string} props.html Server-rendered case HTML.
 * @param {string} [props.id] Stable prefix for fragment ids. PASS THIS: the
 *        inbox renders several documents on one page, and a reloaded deep
 *        link needs the id to be what it was last time. Falls back to
 *        useId(), which is unique but not stable across reloads.
 * @param {string} [props.title] Overrides the document's own leading heading.
 * @param {2|3|4|5} [props.headingLevel] Level for the section rows.
 * @param {'auto'|'all'|'first'|'none'} [props.initial]
 * @param {boolean|'auto'} [props.toc] 'auto' shows a jump list only when the
 *        document opens expanded and has 4+ sections. While sections are
 *        collapsed the rows already ARE the table of contents, and rendering
 *        both is two navigations that can disagree.
 * @param {React.ReactNode} [props.emptyFallback]
 * @param {string} [props.className]
 */
export default function CaseDocument({
    html,
    id,
    title,
    headingLevel = 3,
    initial = 'auto',
    toc = 'auto',
    emptyFallback = null,
    className = '',
}) {
    const generatedId = useId().replace(/:/g, '');
    const idPrefix = id ?? `case-${generatedId}`;

    // Memoised for correctness, not speed. Inbox polls every 15s, so this
    // re-renders four times a minute with a byte-identical html string; a
    // fresh parse would hand back fresh piece objects each time and churn
    // the re-seed effect below, which can reset open sections mid-read.
    const doc = useMemo(() => splitCaseDocument(html, { idPrefix }), [html, idPrefix]);

    const signature = doc.pieces.map((piece) => piece.id).join('|');
    const storageKey = `case-doc:${idPrefix}:read`;

    const [openIds, setOpenIds] = useState(() => initialOpenIds(doc, initial));
    const [readIds, setReadIds] = useState(() =>
        typeof window === 'undefined' ? new Set() : readStorage(storageKey)
    );
    const [pendingScroll, setPendingScroll] = useState(null);

    // Re-seed only when the section list genuinely changes (a Game Master
    // edits content mid-game), never on an identical poll response.
    const lastSignature = useRef(signature);

    useEffect(() => {
        if (lastSignature.current === signature) {
            return;
        }

        lastSignature.current = signature;
        setOpenIds(initialOpenIds(doc, initial));
    }, [signature, doc, initial]);

    const markRead = useCallback(
        (pieceId) =>
            setReadIds((previous) => {
                if (previous.has(pieceId)) {
                    return previous;
                }

                const next = new Set(previous).add(pieceId);
                writeStorage(storageKey, next);

                return next;
            }),
        [storageKey]
    );

    const openPiece = useCallback(
        (pieceId) => {
            setOpenIds((previous) => new Set(previous).add(pieceId));
            markRead(pieceId);
        },
        [markRead]
    );

    const togglePiece = useCallback(
        (pieceId) => {
            setOpenIds((previous) => {
                const next = new Set(previous);

                if (next.has(pieceId)) {
                    next.delete(pieceId);
                } else {
                    next.add(pieceId);
                }

                return next;
            });
            markRead(pieceId);
        },
        [markRead]
    );

    // A deep link has to survive its section being closed: open first, then
    // scroll, or the browser scrolls to a zero-height hidden panel.
    useEffect(() => {
        const hash = window.location.hash.slice(1);

        if (!hash) {
            return;
        }

        const piece = doc.pieces.find((entry) => entry.anchorId === hash);

        if (!piece) {
            return;
        }

        openPiece(piece.id);
        setPendingScroll(piece.anchorId);
    }, [doc, openPiece]);

    useEffect(() => {
        if (!pendingScroll) {
            return undefined;
        }

        // One frame after the panel un-hides, so the target has a height.
        // `instant` overrides the global `scroll-behavior: smooth`, which
        // would otherwise animate all the way across a 4,000-word document
        // while the player waits. The offset comes from scroll-padding-top
        // in app.css — no scroll-mt here, the two would add up.
        const frame = window.requestAnimationFrame(() => {
            document
                .getElementById(pendingScroll)
                ?.scrollIntoView({ block: 'start', behavior: 'instant' });
            setPendingScroll(null);
        });

        return () => window.cancelAnimationFrame(frame);
    }, [pendingScroll]);

    if (doc.isEmpty) {
        return emptyFallback;
    }

    // One section, or none to speak of: this is the pre-redesign rendering,
    // deliberately unchanged. A toolbar over a single block of prose would
    // be chrome with nothing to navigate.
    if (!doc.isSplit) {
        return (
            <Sheet className={className}>
                <div
                    className="case-prose px-4 py-4 text-base sm:px-6"
                    dangerouslySetInnerHTML={{ __html: doc.pieces[0].html }}
                />
            </Sheet>
        );
    }

    const collapsible = doc.pieces.filter((piece) => piece.collapsible);
    const unread = collapsible.filter((piece) => !readIds.has(piece.id)).length;
    const allOpen = collapsible.every((piece) => openIds.has(piece.id));
    const showToc = toc === true || (toc === 'auto' && allOpen && collapsible.length >= 4);

    const setAll = (open) => {
        if (open) {
            setOpenIds(new Set(doc.pieces.map((piece) => piece.id)));

            const next = new Set([...readIds, ...collapsible.map((piece) => piece.id)]);
            setReadIds(next);
            writeStorage(storageKey, next);

            return;
        }

        setOpenIds(
            new Set(doc.pieces.filter((piece) => !piece.collapsible).map((piece) => piece.id))
        );
    };

    // Numbering counts sections, so unlabelled lead content does not consume
    // an index the player can never see.
    let sectionNumber = 0;

    return (
        <div className={className}>
            <DocumentToolbar
                title={title ?? doc.title}
                count={collapsible.length}
                unread={unread}
                allOpen={allOpen}
                onToggleAll={() => setAll(!allOpen)}
            />

            {showToc && (
                <TableOfContents
                    pieces={collapsible}
                    onJump={(piece) => {
                        openPiece(piece.id);
                        setPendingScroll(piece.anchorId);
                    }}
                />
            )}

            <div className="mt-4 space-y-4">
                {doc.pieces.map((piece) => {
                    if (piece.collapsible) {
                        sectionNumber += 1;
                    }

                    return (
                        <DocumentSection
                            key={piece.id}
                            piece={piece}
                            number={piece.collapsible ? sectionNumber : null}
                            total={collapsible.length}
                            headingLevel={headingLevel}
                            open={openIds.has(piece.id)}
                            read={readIds.has(piece.id)}
                            onToggle={() => togglePiece(piece.id)}
                            onFound={() => openPiece(piece.id)}
                        />
                    );
                })}
            </div>
        </div>
    );
}

/** Sits on the desk, not on the paper — it is about the document, not part
 *  of it, so it takes platform tokens. */
function DocumentToolbar({ title, count, unread, allOpen, onToggleAll }) {
    return (
        <div className="flex flex-wrap items-center justify-between gap-3">
            <div className="min-w-0">
                {title && <p className="truncate text-sm font-semibold text-ink">{title}</p>}
                <p className="text-xs text-ink-muted">
                    <span className="tabular">{count}</span> secciones
                    {unread > 0 && (
                        // aria-live so a screen reader hears the counter fall
                        // as sections open — that is the cue for "you have
                        // been through everything in this envelope".
                        <span aria-live="polite">
                            {' · '}
                            <span className="tabular">{unread}</span> sin abrir
                        </span>
                    )}
                </p>
            </div>

            <button
                type="button"
                onClick={onToggleAll}
                className="min-h-11 shrink-0 rounded-full border border-line-strong px-4 py-1.5 text-xs text-ink-muted transition-colors hover:border-accent hover:text-ink"
            >
                {allOpen ? 'Cerrar todo' : 'Abrir todo'}
            </button>
        </div>
    );
}

/**
 * Plain links in an ordered list: Tab and Enter are the right keyboard model
 * for a navigation list. A roving-tabindex widget here would take ten links
 * OUT of the tab order to solve a problem ten links do not have. The href is
 * real so an entry can be copied or opened in a new tab; the click is
 * intercepted only because the target section may be closed, and the browser
 * would otherwise scroll to a zero-height hidden panel.
 */
function TableOfContents({ pieces, onJump }) {
    return (
        <nav
            aria-label="Secciones del documento"
            className="mt-4 rounded-card border border-line bg-surface-raised px-4 py-3"
        >
            <ol className="grid gap-x-6 gap-y-1 sm:grid-cols-2">
                {pieces.map((piece, index) => (
                    <li key={piece.id} className="flex items-baseline gap-2 text-sm">
                        <span aria-hidden="true" className="tabular shrink-0 text-ink-subtle">
                            {String(index + 1).padStart(2, '0')}
                        </span>
                        <a
                            href={`#${piece.anchorId}`}
                            onClick={(event) => {
                                event.preventDefault();
                                window.history.replaceState(null, '', `#${piece.anchorId}`);
                                onJump(piece);
                            }}
                            /* min-h-11: this is the main navigation of a
                               nine-section document, and stacked 28px links
                               four pixels apart are a coin toss on a thumb. */
                            className="flex min-h-11 min-w-0 items-center text-ink-muted underline decoration-line-strong underline-offset-4 hover:text-ink hover:decoration-accent"
                        >
                            {piece.title}
                        </a>
                    </li>
                ))}
            </ol>
        </nav>
    );
}

function DocumentSection({ piece, number, total, headingLevel, open, read, onToggle, onFound }) {
    const Heading = LEVELS[headingLevel] ?? 'h3';
    const panelRef = useRef(null);

    // A layout effect, not an effect: this decides whether the panel is
    // visible, and doing it after paint would flash every closed section
    // open on mount.
    useLayoutEffect(() => {
        const node = panelRef.current;

        if (!node) {
            return undefined;
        }

        // React 18 types `hidden` as a boolean DOM property, so writing
        // hidden="until-found" in JSX renders hidden="" and silently loses
        // find-in-page. setAttribute is the only way to get the real value.
        // (React 19 fixed this — delete the workaround when we upgrade.)
        if (open) {
            node.removeAttribute('hidden');
        } else {
            node.setAttribute('hidden', 'until-found');
        }

        // Chrome removes the attribute itself when find-in-page reveals the
        // panel; tell React, so the toggle and the read mark agree with what
        // is on screen.
        const handleBeforeMatch = () => onFound();

        node.addEventListener('beforematch', handleBeforeMatch);

        return () => node.removeEventListener('beforematch', handleBeforeMatch);
    }, [open, onFound]);

    if (!piece.collapsible) {
        return (
            <Sheet id={piece.anchorId} as="section">
                <div
                    className="case-prose px-4 py-4 text-base sm:px-6"
                    dangerouslySetInnerHTML={{ __html: piece.html }}
                />
            </Sheet>
        );
    }

    return (
        <Sheet id={piece.anchorId} as="section">
            <Heading className="m-0">
                <button
                    type="button"
                    onClick={onToggle}
                    aria-expanded={open}
                    aria-controls={piece.panelId}
                    /* active: as well as hover: — hover does nothing on a
                       touch screen, and this is the most-tapped control in
                       the whole case. */
                    className="flex min-h-14 w-full items-start gap-3 px-4 py-3 text-left hover:bg-paper-sunken active:bg-paper-sunken sm:px-6"
                >
                    <span
                        aria-hidden="true"
                        className="tabular mt-0.5 shrink-0 text-xs text-paper-muted"
                    >
                        {String(number).padStart(2, '0')}
                    </span>

                    <span className="min-w-0 flex-1">
                        <span className="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                            <span className="font-bold">{piece.title}</span>
                            {!read && (
                                <Stamp tone="danger">
                                    <span className="sr-only">Sección </span>sin abrir
                                </Stamp>
                            )}
                        </span>

                        {/* The preview is the anti-miss affordance: a closed
                            row still says what is behind it, so skipping one
                            is a choice rather than an accident. Hidden from
                            assistive tech — it is a truncated duplicate of
                            text the panel already exposes in full. */}
                        {!open && piece.preview && (
                            <span
                                aria-hidden="true"
                                /* text-sm, not xs: this is the anti-miss
                                   affordance, so it has to be read, not
                                   glanced at — and it is Courier. */
                                className="mt-1 line-clamp-2 block text-sm leading-snug text-paper-muted"
                            >
                                {piece.preview}
                            </span>
                        )}

                        <span className="case-stamp mt-1 block text-[10px] text-paper-muted">
                            <span className="tabular">{piece.minutes}</span> min de lectura
                            {' · '}
                            <span className="tabular">{number}</span> de{' '}
                            <span className="tabular">{total}</span>
                        </span>
                    </span>

                    <span aria-hidden="true" className="mt-1 shrink-0 text-lg text-paper-muted">
                        {open ? '−' : '+'}
                    </span>
                </button>
            </Heading>

            <div
                ref={panelRef}
                id={piece.panelId}
                className={`case-prose border-t-2 border-dashed border-paper-line px-4 py-4 text-base sm:px-6 ${
                    open ? 'case-sheet-open' : ''
                }`}
                dangerouslySetInnerHTML={{ __html: piece.html }}
            />
        </Sheet>
    );
}
