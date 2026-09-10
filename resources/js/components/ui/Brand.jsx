import { Link } from '@inertiajs/react';

/**
 * The single MisterioCode mark. Every layout uses this instead of repeating
 * a dot-and-wordmark snippet inline — a real logo file, once one exists,
 * replaces the <svg> below in exactly one place.
 *
 * HAND-BUILT MARK, not a traced copy: no production file (PNG/SVG) of the
 * real isotype exists — only screenshots of a moodboard, which cannot be
 * turned into a clean asset. This is a "seal": M and C set in the same
 * display serif as the wordmark, cut by a single diagonal stroke, framed
 * like a stamp — the same composition idea as the reference, built from
 * scratch in vector rather than copied pixel-for-pixel.
 *
 * The mark's own colours are fixed (paper cream on the frame, brass for the
 * stroke) rather than `currentColor`: a stamp reads the same wherever it
 * sits, the way a wax seal does not recolour itself to match the page. The
 * favicon (public/brand/isotipo.svg) is the same geometry restated with
 * literal hex, since an externally-loaded SVG cannot see this document's CSS
 * custom properties — keep the two in sync by hand if this one changes.
 */
export default function Brand({
    href,
    onClick,
    className = '',
    hideWordmarkOnMobile = false,
    wordmarkClassName = '',
}) {
    return (
        <Link
            href={href || route('home')}
            onClick={onClick}
            className={`flex shrink-0 items-center gap-2.5 text-sm font-semibold text-ink ${className}`}
        >
            <svg viewBox="0 0 32 32" aria-hidden="true" className="h-7 w-7 shrink-0">
                <rect
                    x="1"
                    y="1"
                    width="30"
                    height="30"
                    rx="6"
                    fill="none"
                    stroke="var(--color-line-strong)"
                    strokeWidth="1.25"
                />
                <text
                    x="4"
                    y="22.5"
                    fontFamily="var(--font-display)"
                    fontWeight="700"
                    fontSize="16"
                    fill="var(--color-paper)"
                >
                    M
                </text>
                <text
                    x="15.5"
                    y="23.5"
                    fontFamily="var(--font-display)"
                    fontWeight="700"
                    fontSize="16"
                    fill="var(--color-paper)"
                >
                    C
                </text>
                <line
                    x1="23"
                    y1="3"
                    x2="7"
                    y2="29"
                    stroke="var(--color-accent)"
                    strokeWidth="1.5"
                    strokeLinecap="round"
                />
            </svg>

            <span className={hideWordmarkOnMobile ? `hidden sm:inline ${wordmarkClassName}` : wordmarkClassName}>
                <span className="font-display">
                    Misterio<span className="text-accent">Code</span>
                </span>
            </span>
        </Link>
    );
}
