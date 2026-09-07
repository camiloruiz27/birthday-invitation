import { Link, usePage } from '@inertiajs/react';

/**
 * The fiction surface: what a player sees.
 *
 * Deliberately the opposite of the platform console — light paper, typewriter
 * type, sharp edges — because this is the case file, not the software.
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
                className={`flex min-h-14 flex-1 items-center justify-center px-2 text-center text-xs uppercase tracking-wide ${
                    active
                        ? 'bg-paper-ink font-bold text-paper'
                        : 'text-paper-ink hover:bg-paper-sunken'
                }`}
            >
                {label}
            </Link>
        );
    }

    return (
        <Link
            href={section.href}
            aria-current={active ? 'page' : undefined}
            className={`min-h-11 border-2 px-3 py-1.5 text-xs uppercase tracking-wide ${
                active
                    ? 'border-paper bg-paper text-paper-ink font-bold'
                    : 'border-paper text-paper hover:bg-paper hover:text-paper-ink'
            }`}
        >
            {label}
        </Link>
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
        <div className="case-surface flex min-h-screen flex-col">
            <a
                href="#case-main"
                className="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:border-2 focus:border-paper-ink focus:bg-paper focus:px-4 focus:py-2 focus:text-sm focus:font-bold"
            >
                Saltar al contenido
            </a>

            <header className="border-b-4 border-double border-paper-ink bg-paper-ink text-paper">
                <div className="mx-auto w-full max-w-4xl px-4 py-4 sm:px-6">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="min-w-0">
                            {kicker && (
                                <p className="case-stamp text-[11px] text-paper-accent">{kicker}</p>
                            )}
                            <h1 className="mt-0.5 truncate text-lg font-bold">{title}</h1>
                        </div>

                        {back && (
                            <Link
                                href={back.href}
                                className="min-h-11 shrink-0 border-2 border-paper px-3 py-1.5 text-xs uppercase tracking-wide hover:bg-paper hover:text-paper-ink"
                            >
                                &larr; {back.label}
                            </Link>
                        )}
                    </div>

                    {/* Desktop navigation. On phones this is the bottom bar. */}
                    {sections.length > 1 && (
                        <nav
                            aria-label="Secciones"
                            className="mt-4 hidden flex-wrap gap-2 sm:flex"
                        >
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
                </div>
            </header>

            <main
                id="case-main"
                /* Bottom padding clears the fixed mobile bar so the last item
                   is never trapped under it. */
                className={`mx-auto flex w-full max-w-4xl flex-1 flex-col px-4 py-6 sm:px-6 sm:pb-8 ${
                    sections.length > 1 ? 'pb-24' : 'pb-6'
                } ${contentClassName}`}
            >
                {status && (
                    <div
                        role="status"
                        className="mb-5 border-2 border-paper-line bg-paper-raised px-4 py-3 text-sm"
                    >
                        {status}
                    </div>
                )}

                {children}
            </main>

            {sections.length > 1 && (
                <nav
                    aria-label="Secciones"
                    className="fixed inset-x-0 bottom-0 z-10 flex border-t-2 border-paper-ink bg-paper-raised sm:hidden"
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
