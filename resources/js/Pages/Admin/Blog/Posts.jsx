import { Link } from "@inertiajs/react";
import AppLayout from "../../../Layouts/AppLayout";
import Card from "../../../components/ui/Card";
import Button from "../../../components/ui/Button";
import EmptyState from "../../../components/ui/EmptyState";
import Badge from "../../../components/ui/Badge";
import { formatBlogDate } from "../../../components/blog/BlogCard";

export default function Posts({ posts }) {
    return (
        <AppLayout
            title="Blog"
            kicker="Contenido"
            current="admin.blog.index"
            actions={
                <>
                    <Button
                        href={route("admin.blog.taxonomies")}
                        variant="secondary"
                    >
                        {"Categor\u00edas y etiquetas"}
                    </Button>
                    <Button href={route("admin.blog.create")}>
                        {"Nuevo art\u00edculo"}
                    </Button>
                </>
            }
        >
            {posts.data.length === 0 ? (
                <EmptyState
                    title={"Todav\u00eda no hay art\u00edculos"}
                    description={
                        "Crea el primer recurso para empezar a construir tr\u00e1fico org\u00e1nico."
                    }
                    action={
                        <Button href={route("admin.blog.create")}>
                            {"Crear art\u00edculo"}
                        </Button>
                    }
                />
            ) : (
                <Card className="overflow-x-auto p-0">
                    <table className="w-full min-w-[720px] text-left text-sm">
                        <thead className="border-b border-line bg-surface-sunken text-xs uppercase tracking-wide text-ink-muted">
                            <tr>
                                <th className="px-5 py-3">{"Art\u00edculo"}</th>
                                <th className="px-5 py-3">Estado</th>
                                <th className="px-5 py-3">
                                    {"Categor\u00eda"}
                                </th>
                                <th className="px-5 py-3">Actualizado</th>
                                <th className="px-5 py-3">
                                    <span className="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {posts.data.map((post) => (
                                <tr
                                    key={post.id}
                                    className="border-b border-line last:border-0"
                                >
                                    <td className="px-5 py-4">
                                        <p className="font-medium text-ink">
                                            {post.title}
                                        </p>
                                        <p className="mt-1 text-xs text-ink-subtle">
                                            /blog/{post.slug}
                                        </p>
                                    </td>
                                    <td className="px-5 py-4">
                                        <Badge
                                            tone={
                                                post.status === "published"
                                                    ? "success"
                                                    : "neutral"
                                            }
                                        >
                                            {post.status === "published"
                                                ? "Publicado"
                                                : "Borrador"}
                                        </Badge>
                                    </td>
                                    <td className="px-5 py-4 text-ink-muted">
                                        {post.category || "—"}
                                    </td>
                                    <td className="px-5 py-4 text-ink-muted">
                                        {formatBlogDate(post.updated_at)}
                                    </td>
                                    <td className="px-5 py-4 text-right">
                                        <Link
                                            href={route(
                                                "admin.blog.edit",
                                                post.id,
                                            )}
                                            className="font-medium text-accent hover:text-accent-strong"
                                        >
                                            Editar
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </Card>
            )}
        </AppLayout>
    );
}
