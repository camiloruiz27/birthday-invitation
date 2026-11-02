import { Link } from "@inertiajs/react";
import Card from "../ui/Card";
import Badge from "../ui/Badge";

export function formatBlogDate(value) {
    return new Intl.DateTimeFormat("es-CO", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(new Date(value));
}

export default function BlogCard({ post }) {
    return (
        <Card as="article" className="flex h-full flex-col overflow-hidden p-0">
            {post.cover_url && (
                <Link
                    href={route("blog.show", post.slug)}
                    className="block aspect-[16/9] overflow-hidden bg-surface-sunken"
                >
                    <img
                        src={post.cover_url}
                        alt={post.cover_alt || ""}
                        className="h-full w-full object-cover transition-transform duration-500 hover:scale-[1.03]"
                    />
                </Link>
            )}
            <div className="flex flex-1 flex-col p-6">
                <div className="flex flex-wrap items-center gap-2 text-xs text-ink-muted">
                    {post.category && (
                        <Badge tone="accent">{post.category.name}</Badge>
                    )}
                    <time dateTime={post.published_at}>
                        {formatBlogDate(post.published_at)}
                    </time>
                </div>
                <h2 className="mt-4 font-display text-2xl font-semibold tracking-tight text-ink">
                    <Link
                        href={route("blog.show", post.slug)}
                        className="hover:text-accent-strong"
                    >
                        {post.title}
                    </Link>
                </h2>
                <p className="mt-3 flex-1 leading-relaxed text-ink-muted">
                    {post.excerpt}
                </p>
                <Link
                    href={route("blog.show", post.slug)}
                    className="mt-6 text-sm font-semibold text-accent hover:text-accent-strong"
                >
                    {"Leer art\u00edculo "}
                    <span aria-hidden="true">{"\u2192"}</span>
                </Link>
            </div>
        </Card>
    );
}
