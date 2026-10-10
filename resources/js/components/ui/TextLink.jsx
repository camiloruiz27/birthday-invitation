import { Link } from '@inertiajs/react';

/**
 * A link inside a sentence.
 *
 * Spelled four different ways across the auth and console screens — with and
 * without the accent colour, with and without a hover, at two sizes — which
 * is the kind of drift nobody notices on one page and everybody feels across
 * a product.
 *
 * Renders an Inertia <Link> by default; `external` gives a plain <a>, because
 * Inertia intercepts the click even with target="_blank" and would try to
 * parse a file or an outside page as a page visit.
 */
export default function TextLink({
    href,
    external = false,
    className = '',
    children,
    ...props
}) {
    // py-2.5: a link in a sentence is only as tall as its text (~17px), too
    // small a target for a thumb. Vertical padding on an inline element
    // enlarges what can be tapped without changing the height of the line.
    const classes = `rounded-sm py-2.5 font-medium text-accent underline underline-offset-2 transition-colors hover:text-accent-strong ${className}`;

    if (external) {
        return (
            <a href={href} className={classes} {...props}>
                {children}
            </a>
        );
    }

    return (
        <Link href={href} className={classes} {...props}>
            {children}
        </Link>
    );
}
