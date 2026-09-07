const VARIANTS = {
    status: 'border-ink bg-paper-card text-ink',
    error: 'border-red-800 bg-red-50 text-red-900',
};

export default function Alert({ variant = 'status', children }) {
    if (!children) return null;

    return (
        <div className={`mb-4 border-2 px-4 py-3 text-sm ${VARIANTS[variant]}`}>
            {children}
        </div>
    );
}
