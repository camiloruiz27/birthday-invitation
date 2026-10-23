import { useState } from 'react';
import { initials } from '../../lib/express';

const SIZES = {
    sm: 'h-9 w-9 text-xs',
    md: 'h-12 w-12 text-sm',
    lg: 'h-16 w-16 text-lg',
};

/**
 * A suspect's portrait, or their initials when there is none (or it fails to
 * load: a missing file must never show a broken-image icon). Decorative by
 * default — the name is always printed next to it — so `alt` is empty unless
 * the portrait is the only thing naming the person.
 */
export default function Avatar({ name, src = null, size = 'md', alt = '', className = '' }) {
    const [broken, setBroken] = useState(false);

    return (
        <span
            className={`relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full border border-line-strong bg-accent-dim font-semibold text-accent-strong ${SIZES[size]} ${className}`}
        >
            {src && !broken ? (
                <img
                    src={src}
                    alt={alt}
                    loading="lazy"
                    onError={() => setBroken(true)}
                    className="h-full w-full object-cover object-top"
                />
            ) : (
                <span aria-hidden={alt ? undefined : 'true'}>{initials(name)}</span>
            )}
        </span>
    );
}
