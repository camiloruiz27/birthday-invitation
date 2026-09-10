import { Link } from '@inertiajs/react';

/**
 * The MisterioCode mark. Every layout uses this instead of repeating a
 * dot-and-wordmark snippet inline.
 *
 * The icon is the real isotype (public/brand/isotipo.png) — replacing the
 * hand-built placeholder from the first pass, now that a production file
 * exists. The wordmark stays set as real text rather than the matching
 * logo.png: that file has visible colour-fringing artifacts around every
 * letter (a bad background removal), and shipping it would look worse than
 * typesetting "MisterioCode" cleanly. Swap `wordmarkImage` in for the text
 * the moment a clean export exists — everything else here stays the same.
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
            <img
                src="/brand/isotipo.png"
                alt="MisterioCode"
                className="h-8 w-8 shrink-0 rounded-control object-cover sm:h-9 sm:w-9"
            />

            <span className={hideWordmarkOnMobile ? `hidden sm:inline ${wordmarkClassName}` : wordmarkClassName}>
                <span className="font-display">MisterioCode</span>
            </span>
        </Link>
    );
}
