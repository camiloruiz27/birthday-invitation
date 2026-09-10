import { useEffect, useRef, useState } from 'react';

/**
 * Fades and lifts its children into place the first time they scroll into
 * view — one shared primitive instead of hand-rolling an IntersectionObserver
 * per section. `delay` staggers a group of siblings (pass `index * 0.1`).
 *
 * Fires once: the observer disconnects itself the moment it sees the
 * element, so scrolling back up and down never replays the animation.
 * prefers-reduced-motion needs no special handling here — app.css already
 * forces every animation-duration to near-zero globally, so this degrades to
 * an instant, motionless appearance rather than being skipped outright,
 * which is what keeps the content itself from ever depending on JS/motion to
 * become visible.
 *
 * `threshold: 0` + a negative bottom `rootMargin` means "as soon as it
 * crosses meaningfully into the viewport" rather than requiring 15% of a
 * possibly-tall element to already be on screen, which on a fast scroll or
 * a tall card could take long enough to read as broken. The timeout is a
 * hard backstop: whatever the observer does, content is never allowed to
 * stay invisible for more than a second and a half.
 */
export default function Reveal({ as: Tag = 'div', delay = 0, className = '', children }) {
    const ref = useRef(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const node = ref.current;
        if (!node) return undefined;

        const reveal = () => setVisible(true);

        // Safety net, not the primary trigger: an ad blocker, a browser
        // that never delivers a callback, or a layout this wasn't tested
        // against must never leave real content permanently at opacity 0.
        const fallback = window.setTimeout(reveal, 1500);

        const observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    reveal();
                    window.clearTimeout(fallback);
                    observer.disconnect();
                }
            },
            { threshold: 0, rootMargin: '0px 0px -64px 0px' }
        );

        observer.observe(node);

        return () => {
            window.clearTimeout(fallback);
            observer.disconnect();
        };
    }, []);

    return (
        <Tag
            ref={ref}
            className={`${visible ? 'animate-reveal' : 'opacity-0'} ${className}`}
            style={visible ? { animationDelay: `${delay}s` } : undefined}
        >
            {children}
        </Tag>
    );
}
