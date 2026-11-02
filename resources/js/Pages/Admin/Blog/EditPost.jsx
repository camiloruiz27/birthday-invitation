import { Head, Link, useForm } from "@inertiajs/react";
import { useState } from "react";
import AppLayout from "../../../Layouts/AppLayout";
import Card, { CardHeader } from "../../../components/ui/Card";
import Button from "../../../components/ui/Button";
import {
    CheckboxField,
    SelectField,
    TextArea,
    TextField,
} from "../../../components/ui/Field";
import RichTextEditor from "../../../components/blog/RichTextEditorClean";

function initialData(post) {
    return {
        title: post?.title || "",
        slug: post?.slug || "",
        excerpt: post?.excerpt || "",
        category_id: post?.category_id ? String(post.category_id) : "",
        tag_ids: post?.tag_ids?.map(String) || [],
        cover_image: null,
        cover_alt: post?.cover_alt || "",
        status: post?.status || "draft",
        featured: Boolean(post?.featured),
        seo_title: post?.seo_title || "",
        seo_description: post?.seo_description || "",
        canonical_url: post?.canonical_url || "",
    };
}

export default function EditPost({ post, categories, tags }) {
    const form = useForm(initialData(post));
    const [editorValue, setEditorValue] = useState({
        json: post?.content || null,
        html: post?.content_html || "",
    });
    const { data, setData, errors, processing } = form;
    const editing = Boolean(post);
    const coverName = data.cover_image?.name;

    function toggleTag(id) {
        const value = String(id);
        setData(
            "tag_ids",
            data.tag_ids.includes(value)
                ? data.tag_ids.filter((item) => item !== value)
                : [...data.tag_ids, value],
        );
    }

    function submit(event) {
        event.preventDefault();
        form.transform((current) => ({
            ...current,
            _method: editing ? "put" : undefined,
            content: editorValue.json ? JSON.stringify(editorValue.json) : null,
            content_html: editorValue.html,
        }));
        form.post(
            editing
                ? route("admin.blog.update", post.id)
                : route("admin.blog.store"),
            { forceFormData: true, preserveScroll: true },
        );
    }

    return (
        <AppLayout
            title={editing ? "Editar art\u00edculo" : "Nuevo art\u00edculo"}
            kicker="Blog"
            current="admin.blog.index"
            width="wide"
            actions={
                <>
                    <Button href={route("admin.blog.index")} variant="ghost">
                        Volver
                    </Button>
                    {editing && (
                        <Button
                            href={route("admin.blog.preview", post.id)}
                            variant="secondary"
                        >
                            Vista previa
                        </Button>
                    )}
                </>
            }
        >
            <Head
                title={
                    editing ? `Editar: ${post.title}` : "Nuevo art\u00edculo"
                }
            />
            <form
                onSubmit={submit}
                className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]"
            >
                <div className="space-y-6">
                    <Card>
                        <CardHeader
                            title="Contenido"
                            description="Empieza con una respuesta clara y desarrolla la idea con secciones que una persona pueda escanear."
                        />
                        <div className="space-y-5">
                            <TextField
                                id="post-title"
                                label={"T\u00edtulo"}
                                value={data.title}
                                onChange={(value) => setData("title", value)}
                                error={errors.title}
                                required
                                maxLength="180"
                            />
                            <TextArea
                                id="post-excerpt"
                                label="Extracto"
                                value={data.excerpt}
                                onChange={(value) => setData("excerpt", value)}
                                error={errors.excerpt}
                                required
                                rows={3}
                                maxLength="500"
                                hint="Aparece en el listado, en redes y como resumen para buscadores."
                            />
                            <div>
                                <p className="block text-sm font-medium text-ink">
                                    {"Art\u00edculo"}
                                </p>
                                <div className="mt-1.5">
                                    <RichTextEditor
                                        value={post?.content}
                                        onChange={setEditorValue}
                                        error={errors.content_html}
                                    />
                                </div>
                            </div>
                        </div>
                    </Card>
                    <Card>
                        <CardHeader
                            title={"SEO y publicaci\u00f3n"}
                            description={
                                "Estas opciones refinan c\u00f3mo se presenta el art\u00edculo fuera de la plataforma."
                            }
                        />
                        <div className="space-y-5">
                            <TextField
                                id="post-slug"
                                label="URL"
                                value={data.slug}
                                onChange={(value) =>
                                    setData("slug", value.toLowerCase())
                                }
                                error={errors.slug}
                                hint={
                                    "Vac\u00edo: se crea desde el t\u00edtulo. Usa min\u00fasculas, n\u00fameros y guiones."
                                }
                            />
                            <TextField
                                id="post-seo-title"
                                label={"T\u00edtulo SEO"}
                                value={data.seo_title}
                                onChange={(value) =>
                                    setData("seo_title", value)
                                }
                                error={errors.seo_title}
                                hint={
                                    "Vac\u00edo: se usa el t\u00edtulo del art\u00edculo."
                                }
                                maxLength="255"
                            />
                            <TextArea
                                id="post-seo-description"
                                label={"Descripci\u00f3n SEO"}
                                value={data.seo_description}
                                onChange={(value) =>
                                    setData("seo_description", value)
                                }
                                error={errors.seo_description}
                                rows={3}
                                maxLength="320"
                                hint={"Vac\u00edo: se usa el extracto."}
                            />
                            <TextField
                                id="post-canonical"
                                label="URL canonical"
                                type="url"
                                value={data.canonical_url}
                                onChange={(value) =>
                                    setData("canonical_url", value)
                                }
                                error={errors.canonical_url}
                                hint={
                                    "D\u00e9jalo vac\u00edo salvo que este contenido tenga una URL can\u00f3nica distinta."
                                }
                            />
                        </div>
                    </Card>
                </div>
                <aside className="space-y-6">
                    <Card>
                        <CardHeader title={"Publicaci\u00f3n"} />
                        <div className="space-y-5">
                            <SelectField
                                id="post-status"
                                label="Estado"
                                value={data.status}
                                onChange={(value) => setData("status", value)}
                                error={errors.status}
                                options={[
                                    { value: "draft", label: "Borrador" },
                                    { value: "published", label: "Publicado" },
                                ]}
                            />
                            <CheckboxField
                                id="post-featured"
                                label={
                                    "Mostrar entre los art\u00edculos destacados"
                                }
                                checked={data.featured}
                                onChange={(value) => setData("featured", value)}
                            />
                            <Button
                                type="submit"
                                disabled={processing}
                                className="w-full"
                            >
                                {processing
                                    ? "Guardando\u2026"
                                    : data.status === "published"
                                      ? "Guardar y publicar"
                                      : "Guardar borrador"}
                            </Button>
                            {editing && post.status === "published" && (
                                <Link
                                    href={route("blog.show", post.slug)}
                                    className="block text-center text-sm text-accent underline"
                                >
                                    {"Ver art\u00edculo p\u00fablico"}
                                </Link>
                            )}
                        </div>
                    </Card>
                    <Card>
                        <CardHeader title={"Organizaci\u00f3n"} />
                        <div className="space-y-5">
                            <SelectField
                                id="post-category"
                                label={"Categor\u00eda"}
                                value={data.category_id}
                                onChange={(value) =>
                                    setData("category_id", value)
                                }
                                error={errors.category_id}
                                options={[
                                    { value: "", label: "Sin categor\u00eda" },
                                    ...categories.map((item) => ({
                                        value: String(item.id),
                                        label: item.name,
                                    })),
                                ]}
                            />
                            <fieldset>
                                <legend className="text-sm font-medium text-ink">
                                    Etiquetas
                                </legend>
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {tags.length ? (
                                        tags.map((tag) => (
                                            <label
                                                key={tag.id}
                                                className={`cursor-pointer rounded-full border px-3 py-1.5 text-sm ${data.tag_ids.includes(String(tag.id)) ? "border-accent bg-accent text-ink-inverse" : "border-line text-ink-muted"}`}
                                            >
                                                <input
                                                    className="sr-only"
                                                    type="checkbox"
                                                    checked={data.tag_ids.includes(
                                                        String(tag.id),
                                                    )}
                                                    onChange={() =>
                                                        toggleTag(tag.id)
                                                    }
                                                />
                                                {tag.name}
                                            </label>
                                        ))
                                    ) : (
                                        <p className="text-sm text-ink-muted">
                                            {
                                                "Crea etiquetas desde la pantalla de categor\u00edas."
                                            }
                                        </p>
                                    )}
                                </div>
                            </fieldset>
                        </div>
                    </Card>
                    <Card>
                        <CardHeader title="Imagen destacada" />
                        <div className="space-y-4">
                            {post?.cover_url && !data.cover_image && (
                                <img
                                    src={post.cover_url}
                                    alt=""
                                    className="aspect-video w-full rounded-control object-cover"
                                />
                            )}
                            <input
                                id="post-cover"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                onChange={(event) =>
                                    setData(
                                        "cover_image",
                                        event.target.files?.[0] || null,
                                    )
                                }
                                className="sr-only"
                            />
                            <label
                                htmlFor="post-cover"
                                className="block cursor-pointer rounded-control border border-dashed border-line-strong bg-surface-sunken p-4 transition-colors hover:border-accent hover:bg-surface"
                            >
                                <span className="flex items-center gap-3">
                                    <span className="inline-flex min-h-10 items-center rounded-full border border-line-strong bg-surface-raised px-4 text-sm font-medium text-ink">
                                        {post?.cover_url
                                            ? "Reemplazar imagen"
                                            : "Seleccionar imagen"}
                                    </span>
                                    <span className="min-w-0 text-sm text-ink-muted">
                                        {coverName ||
                                            "JPG, PNG o WebP. Tama\u00f1o m\u00e1ximo: 5 MB."}
                                    </span>
                                </span>
                            </label>
                            <TextField
                                id="post-cover-alt"
                                label="Texto alternativo"
                                value={data.cover_alt}
                                onChange={(value) =>
                                    setData("cover_alt", value)
                                }
                                error={errors.cover_alt}
                                hint="Describe la imagen para lectores de pantalla y buscadores."
                                maxLength="255"
                            />
                        </div>
                    </Card>
                </aside>
            </form>
        </AppLayout>
    );
}
