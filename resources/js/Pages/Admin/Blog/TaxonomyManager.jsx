import { useForm } from "@inertiajs/react";
import AppLayout from "../../../Layouts/AppLayout";
import Card, { CardHeader } from "../../../components/ui/Card";
import Button from "../../../components/ui/Button";
import { TextArea, TextField } from "../../../components/ui/Field";

function TaxonomyForm({ kind }) {
    const form = useForm({ name: "", slug: "", description: "" });
    const isCategory = kind === "category";
    function submit(event) {
        event.preventDefault();
        form.post(
            route(
                isCategory
                    ? "admin.blog.categories.store"
                    : "admin.blog.tags.store",
            ),
            { preserveScroll: true, onSuccess: () => form.reset() },
        );
    }
    return (
        <form onSubmit={submit} className="space-y-4">
            <TextField
                id={`${kind}-name`}
                label="Nombre"
                value={form.data.name}
                onChange={(value) => form.setData("name", value)}
                error={form.errors.name}
                required
            />
            <TextField
                id={`${kind}-slug`}
                label="Slug"
                value={form.data.slug}
                onChange={(value) => form.setData("slug", value.toLowerCase())}
                error={form.errors.slug}
                hint={"Vac\u00edo: se crea desde el nombre."}
            />
            {isCategory && (
                <TextArea
                    id="category-description"
                    label={"Descripci\u00f3n"}
                    value={form.data.description}
                    onChange={(value) => form.setData("description", value)}
                    error={form.errors.description}
                    rows={3}
                />
            )}
            <Button type="submit" disabled={form.processing}>
                Crear {isCategory ? "categor\u00eda" : "etiqueta"}
            </Button>
        </form>
    );
}

function TaxonomyList({ title, items }) {
    return (
        <Card>
            <CardHeader title={title} />
            <ul className="divide-y divide-line">
                {items.length ? (
                    items.map((item) => (
                        <li
                            key={item.id}
                            className="flex items-center justify-between gap-4 py-3"
                        >
                            <div>
                                <p className="font-medium text-ink">
                                    {item.name}
                                </p>
                                <p className="text-xs text-ink-subtle">
                                    /{item.slug}
                                </p>
                            </div>
                            <span className="text-sm text-ink-muted">
                                {item.posts_count} art.
                            </span>
                        </li>
                    ))
                ) : (
                    <li className="py-3 text-sm text-ink-muted">
                        {"A\u00fan no hay elementos."}
                    </li>
                )}
            </ul>
        </Card>
    );
}

export default function TaxonomyManager({ categories, tags }) {
    return (
        <AppLayout
            title={"Categor\u00edas y etiquetas"}
            kicker="Blog"
            current="admin.blog.index"
            width="wide"
            actions={
                <Button href={route("admin.blog.index")} variant="ghost">
                    Volver al blog
                </Button>
            }
        >
            <div className="grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader
                        title={"Nueva categor\u00eda"}
                        description={"Agrupa art\u00edculos por un tema principal."}
                    />
                    <TaxonomyForm kind="category" />
                </Card>
                <Card>
                    <CardHeader
                        title="Nueva etiqueta"
                        description={
                            "A\u00f1ade temas m\u00e1s espec\u00edficos y reutilizables."
                        }
                    />
                    <TaxonomyForm kind="tag" />
                </Card>
                <TaxonomyList
                    title={"Categor\u00edas actuales"}
                    items={categories}
                />
                <TaxonomyList title="Etiquetas actuales" items={tags} />
            </div>
        </AppLayout>
    );
}
