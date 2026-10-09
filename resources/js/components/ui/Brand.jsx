import { Link } from '@inertiajs/react';

/**
 * The MisterioCode mark. Every layout uses this instead of repeating a
 * dot-and-wordmark snippet inline.
 *
 * The icon is the real isotype (public/brand/isotipo.png, served as small
 * derivatives) — replacing the
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
            className={`flex min-h-11 shrink-0 items-center gap-2.5 text-sm font-semibold text-ink ${className}`}
        >
            {/* The source art is 1254 px and 1.2 MB; this shows at 32-36 px, on
                every page. 96/192 px copies (platform:build-case-images
                --brand) cover 1x-3x screens at 6-30 KB. */}
            <img
                src="/brand/isotipo-96.png"
                srcSet="/brand/isotipo-96.png 1x, /brand/isotipo-192.png 2x"
                width="36"
                height="36"
                alt="MisterioCode"
                className="h-8 w-8 shrink-0 rounded-control object-cover sm:h-9 sm:w-9"
            />

            <span className={hideWordmarkOnMobile ? `hidden sm:inline ${wordmarkClassName}` : wordmarkClassName}>
                <span className="font-display">MisterioCode</span>
            </span>
        </Link>
    );
}
