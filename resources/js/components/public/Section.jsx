import Container from '../ui/Container';

/**
 * Vertical rhythm for the marketing pages, so every section is not spaced
 * slightly differently.
 */
export default function Section({
    id,
    kicker,
    title,
    description,
    width = 'wide',
    tone = 'default',
    className = '',
    children,
}) {
    return (
        <section
            id={id}
            className={`py-16 sm:py-20 ${tone === 'sunken' ? 'bg-surface-sunken' : ''} ${className}`}
        >
            <Container width={width}>
                {(kicker || title) && (
                    <div className="max-w-2xl">
                        {kicker && (
                            <p className="text-xs font-medium uppercase tracking-widest text-accent">
                                {kicker}
                            </p>
                        )}
                        {title && (
                            <h2 className="mt-3 text-2xl font-semibold text-ink sm:text-3xl">
                                {title}
                            </h2>
                        )}
                        {description && (
                            <p className="mt-4 text-base leading-relaxed text-ink-muted">
                                {description}
                            </p>
                        )}
                    </div>
                )}

                {children && <div className={title ? 'mt-10' : ''}>{children}</div>}
            </Container>
        </section>
    );
}
