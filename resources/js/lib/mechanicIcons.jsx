/**
 * One outline icon per mechanic slug (see Mechanics::all() in
 * app/Modules/Platform/Support/Mechanics.php for the slugs themselves —
 * that file is content/copy and stays untouched; this is presentation
 * only). Shared between Landing and CaseDetail so both ever show the same
 * icon for the same mechanic, rather than drifting apart if each page kept
 * its own copy.
 *
 * 24x24 outline paths, stroke-based — draw with <Icon slug="..." />.
 */
export const MECHANIC_ICON_PATHS = {
    inbox: 'M3 8l9 6 9-6M4 6h16a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V7a1 1 0 011-1z',
    timeline: 'M12 7v5l3.5 2M21 12a9 9 0 11-9-9 9 9 0 019 9z',
    gallery: 'M4 5h16a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V6a1 1 0 011-1zM8.5 11a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM21 16l-5-5-9 9',
    audio: 'M12 1.5a3 3 0 00-3 3v7a3 3 0 006 0v-7a3 3 0 00-3-3zM19 10.5v1.5a7 7 0 01-14 0v-1.5M12 19v3.5M8.5 22.5h7',
    interrogation: 'M21 12a8 8 0 01-8 8H8l-4.5 3.5V12a8 8 0 018-8h1a8 8 0 018 8z',
    accusation: 'M5 2.5v19M5 3.5h12.5l-2.2 4.3 2.2 4.3H5',
};

export default function Icon({ slug, className = 'h-6 w-6' }) {
    const path = MECHANIC_ICON_PATHS[slug];

    if (!path) return null;

    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" aria-hidden="true" className={className}>
            <path strokeLinecap="round" strokeLinejoin="round" d={path} />
        </svg>
    );
}
