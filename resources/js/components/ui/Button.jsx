const VARIANTS = {
    primary: 'bg-ink text-paper border-ink hover:bg-ink/90',
    outline: 'bg-transparent text-ink border-ink hover:bg-ink/10',
    danger: 'bg-transparent text-red-800 border-red-800 hover:bg-red-800/10',
};

export default function Button({
    variant = 'primary',
    className = '',
    disabled = false,
    type = 'button',
    children,
    ...props
}) {
    return (
        <button
            type={type}
            disabled={disabled}
            className={`inline-flex items-center justify-center gap-2 border-2 px-4 py-2 font-display text-sm uppercase tracking-wide transition disabled:cursor-not-allowed disabled:opacity-50 ${VARIANTS[variant]} ${className}`}
            {...props}
        >
            {children}
        </button>
    );
}
