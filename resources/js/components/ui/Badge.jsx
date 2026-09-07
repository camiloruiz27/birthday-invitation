const VARIANTS = {
    neutral: 'bg-paper text-ink border-border-soft',
    success: 'bg-green-100 text-green-900 border-green-800',
    warning: 'bg-yellow-100 text-yellow-900 border-yellow-800',
    danger: 'bg-red-100 text-red-900 border-red-800',
};

export default function Badge({ variant = 'neutral', children }) {
    return (
        <span
            className={`inline-block border px-2 py-0.5 text-xs uppercase tracking-wide ${VARIANTS[variant]}`}
        >
            {children}
        </span>
    );
}
