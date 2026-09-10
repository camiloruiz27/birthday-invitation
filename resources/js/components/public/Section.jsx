import Container from '../ui/Container';
import Reveal from '../ui/Reveal';

/**
 * Vertical rhythm for the marketing pages, so every section is not spaced
 * slightly differently.
 *
 * The kicker/title/description block animates in on scroll by itself —
 * every page built on Section gets that motion for free, rather than each
 * page having to remember to wrap its own heading in <Reveal>.
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
                    <Reveal as="div" className="max-w-2xl">
                        {kicker && (
                            <p className="text-xs font-medium uppercase tracking-widest text-accent">
                                {kicker}
                            </p>
                        )}
                        {title && (
                            <h2 className="mt-3 font-display text-3xl font-semibold tracking-tight text-ink sm:text-5xl">
                                {title}
                            </h2>
                        )}
                        {description && (
                            <p className="mt-4 text-base leading-relaxed text-ink-muted">
                                {description}
                            </p>
                        )}
                    </Reveal>
                )}

                {children && <div className={title ? 'mt-10' : ''}>{children}</div>}
            </Container>
        </section>
    );
}
