import { Link } from '@inertiajs/react';
import Spinner from './Spinner';

const VARIANTS = {
    primary: 'bg-accent text-ink-inverse hover:bg-accent-strong border-transparent font-semibold',
    secondary: 'bg-surface-raised text-ink hover:bg-line border-line-strong',
    ghost: 'bg-transparent text-ink-muted hover:text-ink hover:bg-surface-raised border-transparent',
    danger: 'bg-transparent text-danger hover:bg-danger-dim border-danger',
};

const SIZES = {
    // Touch targets: md and lg clear 44px, so they are comfortable on a phone
    // without needing a separate mobile style.
    sm: 'min-h-8 px-3 py-1.5 text-xs',
    md: 'min-h-11 px-4 py-2.5 text-sm',
    lg: 'min-h-12 px-6 py-3 text-base',
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
    const classes = [
        'inline-flex items-center justify-center gap-2 rounded-control border transition-colors',
        'disabled:cursor-not-allowed disabled:opacity-50',
        'aria-disabled:cursor-not-allowed aria-disabled:opacity-50',
        VARIANTS[variant],
        SIZES[size],
        fullWidth ? 'w-full' : '',
        className,
    ].join(' ');

    const content = (
        <>
            {loading && <Spinner size={size === 'lg' ? 'md' : 'sm'} label={null} />}
            {children}
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
