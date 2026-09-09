const SIZES = {
    sm: 'h-3.5 w-3.5 border-[1.5px]',
    md: 'h-4 w-4 border-2',
    lg: 'h-6 w-6 border-2',
};

/**
 * Inline busy indicator. `label` is what a screen reader announces; pass null
 * when the surrounding element already says what is happening (a button whose
 * text changes to "Saving…"), so it is not announced twice.
 */
export default function Spinner({ size = 'md', label = 'Cargando', className = '' }) {
    return (
        <span
            role={label ? 'status' : undefined}
            aria-label={label || undefined}
            aria-hidden={label ? undefined : 'true'}
            className={`inline-block shrink-0 animate-spin rounded-full border-current border-r-transparent ${SIZES[size]} ${className}`}
        />
    );
}
