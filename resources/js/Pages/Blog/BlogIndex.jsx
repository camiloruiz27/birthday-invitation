import { Head, Link } from "@inertiajs/react";
import PublicLayout from "../../Layouts/PublicLayout";
import Container from "../../components/ui/Container";
import EmptyState from "../../components/ui/EmptyState";
import Button from "../../components/ui/Button";
import BlogCard from "../../components/blog/BlogCard";

export default function BlogIndex({ posts, featured, categories, tags }) {
    return (
        <PublicLayout current="blog.index">
            <Head title="Blog" />
            <Container width="wide" className="py-10 sm:py-16">
                <div className="max-w-3xl">
                    <p className="text-xs font-medium uppercase tracking-widest text-accent">
                        Recursos
                    </p>
                    <h1 className="mt-3 font-display text-4xl font-semibold tracking-tight text-ink sm:text-6xl">
                        Ideas para jugar, investigar y reunir a tu equipo
                    </h1>
                    <p className="mt-5 text-lg leading-relaxed text-ink-muted">
                        {
                            "Gu\u00edas claras para convertir una noche cualquiera en una experiencia de misterio compartida."
                        }
                    </p>
                </div>
                {categories.length > 0 && (
                    <nav
                        aria-label={"Categor\u00edas"}
                        className="mt-10 flex flex-wrap gap-2"
                    >
                        {categories.map((category) => (
                            <Link
                                key={category.id}
                                href={route("blog.category", category.slug)}
                                className="rounded-full border border-line px-3 py-1.5 text-sm text-ink-muted hover:border-accent hover:text-accent"
                            >
                                {category.name}{" "}
                                <span className="text-ink-subtle">
                                    {category.posts_count}
                                </span>
                            </Link>
                        ))}
                    </nav>
                )}
                {featured.length > 0 && (
                    <section className="mt-12">
                        <div className="flex items-baseline justify-between gap-4">
                            <h2 className="font-display text-3xl font-semibold tracking-tight text-ink">
                                Destacados
                            </h2>
                            <span className="text-sm text-ink-muted">
                                Para empezar
                            </span>
                        </div>
                        <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {featured.map((post) => (
                                <BlogCard key={post.slug} post={post} />
                            ))}
                        </div>
                    </section>
                )}
                {posts.data.length === 0 ? (
                    <EmptyState
                        title={"Estamos preparando los primeros art\u00edculos"}
                        description={
                            "Vuelve pronto para encontrar gu\u00edas y recursos nuevos."
                        }
                        action={
                            <Button href={route("home")} variant="secondary">
                                Volver al inicio
                            </Button>
                        }
                    />
                ) : (
                    <>
                        <h2 className="mt-12 font-display text-3xl font-semibold tracking-tight text-ink">
                            {"Todos los art\u00edculos"}
                        </h2>
                        <div className="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {posts.data.map((post) => (
                                <BlogCard key={post.slug} post={post} />
                            ))}
                        </div>
                        <Pagination links={posts.links} />
                    </>
                )}
                {tags.length > 0 && (
                    <section className="mt-16 border-t border-line pt-8">
                        <h2 className="font-display text-2xl font-semibold text-ink">
                            Explora por tema
                        </h2>
                        <div className="mt-4 flex flex-wrap gap-2">
                            {tags.map((tag) => (
                                <Link
                                    key={tag.id}
                                    href={route("blog.tag", tag.slug)}
                                    className="text-sm text-accent underline underline-offset-2 hover:text-accent-strong"
                                >
                                    #{tag.name}
                                </Link>
                            ))}
                        </div>
                    </section>
                )}
            </Container>
        </PublicLayout>
    );
}

export function Pagination({ links }) {
    if (!links || links.length < 4) return null;
    return (
        <nav
            aria-label={"Paginaci\u00f3n"}
            className="mt-10 flex flex-wrap gap-2"
        >
            {links.map((link, index) =>
                link.url ? (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        className={`rounded-control border px-3 py-2 text-sm ${link.active ? "border-accent bg-accent text-ink-inverse" : "border-line text-ink-muted hover:text-ink"}`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span
                        key={index}
                        className="px-3 py-2 text-sm text-ink-subtle"
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ),
            )}
        </nav>
    );
}
