export default function Card({ className = '', children, ...props }) {
    return (
        <div
            className={`border-2 border-ink bg-paper-card p-5 ${className}`}
            {...props}
        >
            {children}
        </div>
    );
}
