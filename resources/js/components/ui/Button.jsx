import { Link } from '@inertiajs/react';
import Spinner from './Spinner';

/**
 * `fill` is the colour the sliding wipe reveals on hover (see `content`
 * below) — only `primary`/`secondary` get the wipe. `ghost`/`danger` stay a
 * plain colour transition: a flashy fill wipe doesn't suit a low-emphasis or
 * a destructive action, and the reference this pattern comes from only ever
 * used it on its two loudest calls to action.
 */
const VARIANTS = {
    primary: {
        base: 'bg-accent text-ink-inverse border-transparent font-semibold',
        fill: 'bg-surface',
        hoverText: 'group-hover:text-ink',
    },
    secondary: {
        base: 'bg-surface-raised text-ink border-line-strong',
        fill: 'bg-line-strong',
        hoverText: '',
    },
    ghost: {
        base: 'bg-transparent text-ink-muted hover:text-ink hover:bg-surface-raised border-transparent transition-colors',
    },
    danger: {
        // -strong, not the base danger hex: that one is calibrated for ~7:1
        // on Papel, and measures under 2:1 as text/border directly on a
        // dark button — see the token comment in app.css.
        base: 'bg-transparent text-danger-strong hover:bg-danger-dim border-danger-strong transition-colors',
    },
};

const SIZES = {
    // Touch targets: md and lg clear 44px, so they are comfortable on a phone
    // without needing a separate mobile style.
    sm: 'min-h-8 px-4 py-1.5 text-xs',
    md: 'min-h-11 px-5 py-2.5 text-sm',
    lg: 'min-h-12 px-7 py-3 text-base',
};

/**
 * The one button in the platform surface.
 *
 * Renders as <button>, or as an Inertia <Link> when given `href` — a thing
 * that navigates must be a link so it can be opened in a new tab and read
 * correctly by assistive tech.
 *
 * `loading` both shows a spinner and blocks the control, which is what stops
 * double submits.
 *
 * Pill-shaped (`rounded-full`) by design, not by token: the radius scale in
 * app.css is for cards and controls that stay rectangular, and a button
 * going all the way round is a shape choice, not a point on that scale.
 */
export default function Button({
    variant = 'primary',
    size = 'md',
    href = null,
    type = 'button',
    loading = false,
    disabled = false,
    fullWidth = false,
    className = '',
    children,
    ...props
}) {
    const { base, fill, hoverText } = VARIANTS[variant];

    const classes = [
        'group relative inline-flex items-center justify-center gap-2 overflow-hidden rounded-full border',
        'disabled:cursor-not-allowed disabled:opacity-50',
        'aria-disabled:cursor-not-allowed aria-disabled:opacity-50',
        base,
        SIZES[size],
        fullWidth ? 'w-full' : '',
        className,
    ].join(' ');

    const content = (
        <>
            {fill && (
                <span
                    aria-hidden="true"
                    className={`absolute inset-0 origin-left scale-x-0 transition-transform duration-500 ease-out group-hover:scale-x-100 ${fill}`}
                />
            )}
            <span className={`relative z-10 inline-flex items-center gap-2 transition-colors duration-500 ${hoverText || ''}`}>
                {loading && <Spinner size={size === 'lg' ? 'md' : 'sm'} label={null} />}
                {children}
            </span>
        </>
    );

    if (href) {
        return (
            <Link
                href={href}
                className={classes}
                aria-disabled={disabled || loading || undefined}
                {...props}
            >
                {content}
            </Link>
        );
    }

    return (
        <button
            type={type}
            disabled={disabled || loading}
            className={classes}
            {...props}
        >
            {content}
        </button>
    );
}
