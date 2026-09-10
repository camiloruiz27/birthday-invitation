import { useEffect, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import Alert from '../components/ui/Alert';
import Brand from '../components/ui/Brand';
import Container from '../components/ui/Container';
import { MECHANIC_ICON_PATHS } from '../lib/mechanicIcons';

/**
 * What a player sees, on the dark desk.
 *
 * This layout used to put `.case-surface` on the root, so the entire screen
 * was paper. It is now the platform console like everywhere else, and paper
 * is reserved for the documents themselves (see components/player/paper).
 * The reason is both brand and code: the player screens were the only ones
 * that could not use Button, Field, Badge, Alert or EmptyState — all of
 * which are built on platform tokens — so every one of those patterns had
 * been re-implemented by hand, four and five times over, and had drifted.
 *
 * Mobile-first for real: players are on phones at a table, so the sections
 * live in a thumb-reachable bottom bar on small screens and move up into the
 * header on desktop. It is not a scaled-down desktop layout.
 */

const SECTION_LABELS = {
    inbox: 'Bandeja',
    interrogation: 'Personas',
    accusation: 'Acusación',
    solution: 'Solución',
};

// Reuses the mechanic icon set so the same idea never gets two drawings.
const SECTION_ICONS = {
    inbox: MECHANIC_ICON_PATHS.inbox,
    interrogation: MECHANIC_ICON_PATHS.interrogation,
    accusation: MECHANIC_ICON_PATHS.accusation,
    solution: 'M9 12.5l2.2 2.2L15.5 10M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
};

function SectionIcon({ section, className = 'h-5 w-5' }) {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.5"
            aria-hidden="true"
            className={className}
        >
            <path strokeLinecap="round" strokeLinejoin="round" d={SECTION_ICONS[section]} />
        </svg>
    );
}

function sectionsFor(player, game) {
    const sections = [
        { key: 'inbox', href: route('immersion.player.inbox', player.access_token) },
    ];

    if (game?.interrogation_enabled) {
        sections.push({
            key: 'interrogation',
            href: route('immersion.player.interrogation.index', player.access_token),
        });
    }

    // Once the ending is out, "Solución" REPLACES "Acusación" rather than
    // joining it: the form is locked anyway, and a fourth tab does not fit
    // legibly in the mobile bottom bar.
    sections.push(
        game?.ending_revealed_at
            ? { key: 'solution', href: route('immersion.player.solution', player.access_token) }
            : { key: 'accusation', href: route('immersion.player.accusation', player.access_token) }
    );

    return sections;
}

function SectionLink({ section, active, variant }) {
    const label = SECTION_LABELS[section.key];

    if (variant === 'bar') {
        return (
            <Link
                href={section.href}
                aria-current={active ? 'page' : undefined}
                className={`flex min-h-16 flex-1 flex-col items-center justify-center gap-1 px-2 text-center text-[11px] transition-colors ${
                    active ? 'text-accent-strong' : 'text-ink-muted hover:text-ink'
                }`}
            >
                <SectionIcon section={section.key} />
                <span className={active ? 'font-semibold' : ''}>{label}</span>
            </Link>
        );
    }

    // Desktop: the sliding underline from the public site, so the case does
    // not feel like a different product than the one they bought.
    return (
        <Link
            href={section.href}
            aria-current={active ? 'page' : undefined}
            className={`group relative inline-flex min-h-11 items-center gap-2 py-1 text-sm transition-colors ${
                active ? 'text-ink' : 'text-ink-muted hover:text-ink'
            }`}
        >
            <SectionIcon section={section.key} className="h-4 w-4" />
            {label}
            <span
                aria-hidden="true"
                className={`absolute inset-x-0 bottom-0 h-px origin-left bg-accent transition-transform duration-300 ${
                    active ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100'
                }`}
            />
        </Link>
    );
}

/**
 * Time on the clock, ticking.
 *
 * `elapsed_minutes` is computed server-side (it discounts paused time), but
 * the inbox only polls `items`, so the value would sit frozen for the whole
 * session. Counting forward from the last value the server sent keeps it
 * honest between reloads, and any poll that does carry `game` corrects it.
 */
function CaseClock({ game }) {
    const base = game?.elapsed_minutes;
    const [drift, setDrift] = useState(0);

    useEffect(() => {
        setDrift(0);

        if (game?.status !== 'running') {
            return undefined;
        }

        const mountedAt = Date.now();
        const timer = window.setInterval(
            () => setDrift(Math.floor((Date.now() - mountedAt) / 60000)),
            30000
        );

        return () => window.clearInterval(timer);
    }, [base, game?.status]);

    if (base == null || !game?.started_at) {
        return null;
    }

    if (game.status === 'paused') {
        return <p className="case-stamp shrink-0 text-[10px] text-warning-strong">En pausa</p>;
    }

    if (game.status !== 'running') {
        return null;
    }

    const total = base + drift;

    return (
        <p className="shrink-0 text-right">
            <span className="case-stamp block text-[10px] text-ink-subtle">En juego</span>
            <span className="tabular font-mono text-sm text-ink">
                {Math.floor(total / 60)}:{String(total % 60).padStart(2, '0')}
            </span>
        </p>
    );
}

/**
 * `focused` suppresses the section navigation for a detail view (an
 * interrogation chat), which needs the whole screen and its own back link
 * rather than competing with a bottom bar.
 */
export default function PlayerLayout({
    player,
    game,
    section,
    title,
    kicker,
    back,
    focused = false,
    contentClassName = '',
    children,
}) {
    const { props } = usePage();
    const status = props.flash?.status;
    const sections = focused || !player ? [] : sectionsFor(player, game);

    return (
        <div className="flex min-h-dvh flex-col bg-surface">
            <a
                href="#case-main"
                className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-control focus:bg-accent focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-ink-inverse"
            >
                Saltar al contenido
            </a>

            <header className="sticky top-0 z-20 border-b border-line bg-surface/95 backdrop-blur-md">
                <Container width="app" className="py-3">
                    <div className="flex items-center justify-between gap-3">
                        <div className="flex min-w-0 items-center gap-3">
                            {player && (
                                <>
                                    {/* On a 360px screen with a back link the
                                        title gets squeezed to nothing, and the
                                        case name matters more than the mark. */}
                                    <Brand
                                        href={route('immersion.player.inbox', player.access_token)}
                                        hideWordmarkOnMobile
                                        className={back ? 'hidden sm:flex' : ''}
                                    />
                                    <span
                                        aria-hidden="true"
                                        className="hidden h-8 w-px shrink-0 bg-line sm:block"
                                    />
                                </>
                            )}

                            <div className="min-w-0">
                                {kicker && (
                                    <p className="case-stamp truncate text-[10px] text-accent">
                                        {kicker}
                                    </p>
                                )}
                                <h1 className="truncate font-display text-base font-semibold text-ink">
                                    {title}
                                </h1>
                            </div>
                        </div>

                        <div className="flex shrink-0 items-center gap-4">
                            <CaseClock game={game} />

                            {back && (
                                <Link
                                    href={back.href}
                                    className="inline-flex min-h-11 items-center gap-2 rounded-full border border-line-strong px-4 text-sm text-ink-muted transition-colors hover:border-accent hover:text-ink"
                                >
                                    <span aria-hidden="true">&larr;</span>
                                    {/* The arrow carries it on a phone; the
                                        word is what would push the title out. */}
                                    <span className="sr-only sm:not-sr-only">{back.label}</span>
                                </Link>
                            )}
                        </div>
                    </div>

                    {/* Desktop navigation. On phones this is the bottom bar. */}
                    {sections.length > 1 && (
                        <nav aria-label="Secciones" className="mt-2 hidden flex-wrap gap-6 sm:flex">
                            {sections.map((item) => (
                                <SectionLink
                                    key={item.key}
                                    section={item}
                                    active={item.key === section}
                                    variant="header"
                                />
                            ))}
                        </nav>
                    )}
                </Container>
            </header>

            <main
                id="case-main"
                /* Bottom padding clears the fixed mobile bar so the last item
                   is never trapped under it. */
                className={`flex w-full flex-1 flex-col ${
                    sections.length > 1 ? 'pb-28 sm:pb-10' : 'pb-10'
                }`}
            >
                <Container width="app" className={`flex flex-1 flex-col py-6 ${contentClassName}`}>
                    <Alert variant="status">{status}</Alert>
                    {children}
                </Container>
            </main>

            {sections.length > 1 && (
                <nav
                    aria-label="Secciones"
                    /* Opaque, not blurred: a second backdrop-blur layer
                       compositing over a 4,000-word document while it scrolls
                       is a known source of stutter on iOS and mid-range
                       Android, and this bar has nothing worth seeing through
                       it. The padding keeps the tabs clear of the iPhone home
                       indicator, which swallows taps that land on it. */
                    className="player-bottom-nav fixed inset-x-0 bottom-0 z-20 flex border-t border-line bg-surface pb-[env(safe-area-inset-bottom)] sm:hidden"
                >
                    {sections.map((item) => (
                        <SectionLink
                            key={item.key}
                            section={item}
                            active={item.key === section}
                            variant="bar"
                        />
                    ))}
                </nav>
            )}
        </div>
    );
}
