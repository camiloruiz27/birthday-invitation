<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Models\BlogCategory;
use App\Modules\Platform\Models\BlogPost;
use App\Modules\Platform\Models\BlogTag;
use App\Modules\Platform\Support\BlogContent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $posts = BlogPost::query()
            ->published()
            ->with(['category', 'tags'])
            ->newest()
            ->paginate(9)
            ->through(fn (BlogPost $post) => $this->card($post));

        return Inertia::render('Blog/BlogIndex', [
            'posts' => $posts,
            'featured' => BlogPost::query()
                ->published()
                ->where('featured', true)
                ->with(['category', 'tags'])
                ->newest()
                ->limit(3)
                ->get()
                ->map(fn (BlogPost $post) => $this->card($post))
                ->values(),
            'categories' => BlogCategory::query()
                ->whereHas('posts', fn ($query) => $query->published())
                ->withCount(['posts' => fn ($query) => $query->published()])
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'tags' => BlogTag::query()
                ->whereHas('posts', fn ($query) => $query->published())
                ->withCount(['posts' => fn ($query) => $query->published()])
                ->orderByDesc('posts_count')
                ->orderBy('name')
                ->limit(16)
                ->get(['id', 'name', 'slug']),
        ])->withViewData(['seo' => [
            'title' => "Blog \u{2014} MisterioCode",
            'description' => "Ideas, gu\u{00ed}as y recursos para organizar juegos de misterio y experiencias en equipo.",
            'canonical' => route('blog.index'),
            'liteFonts' => true,
        ]]);
    }

    public function show(string $slug): Response
    {
        // Never bind directly: an unpublished row is an editorial draft, not
        // a public URL that happens to be hidden from the index.
        $post = BlogPost::query()
            ->published()
            ->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = BlogPost::query()
            ->published()
            ->with(['category:id,name,slug', 'tags:id,name,slug'])
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($query) => $query->where('blog_category_id', $post->blog_category_id))
            ->newest()
            ->limit(3)
            ->get()
            ->map(fn (BlogPost $relatedPost) => $this->card($relatedPost))
            ->values();

        $canonical = $post->canonical_url ?: route('blog.show', $post->slug);
        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->seo_description ?: $post->excerpt,
            'mainEntityOfPage' => $canonical,
            'datePublished' => $post->published_at?->toAtomString(),
            'dateModified' => $post->updated_at->toAtomString(),
            'author' => ['@type' => 'Person', 'name' => $post->author?->name ?: 'Equipo MisterioCode'],
            'publisher' => ['@type' => 'Organization', 'name' => 'MisterioCode'],
        ];
        if ($post->coverUrl()) $article['image'] = url($post->coverUrl());

        $schema = [$article, [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Blog', 'item' => route('blog.index')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => route('blog.show', $post->slug)],
            ],
        ]];

        $faqs = BlogContent::faqItems((string) $post->content_html);
        if ($faqs !== []) {
            $schema[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn (array $faq) => [
                    '@type' => 'Question',
                    'name' => $faq['question'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
                ], $faqs),
            ];
        }

        return Inertia::render('Blog/BlogShow', [
            'post' => $this->detail($post),
            'related' => $related,
        ])->withViewData(['seo' => array_filter([
            'title' => ($post->seo_title ?: $post->title)." \u{2014} MisterioCode",
            'description' => $post->seo_description ?: $post->excerpt,
            'image' => $post->coverUrl(),
            'canonical' => $canonical,
            'liteFonts' => true,
            'schema' => $schema,
        ])]);
    }

    public function category(string $slug): Response
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        $posts = $category->posts()->published()->with(['category', 'tags'])->newest()->paginate(9)
            ->through(fn (BlogPost $post) => $this->card($post));

        return Inertia::render('Blog/BlogArchive', [
            'archive' => ['type' => "Categor\u{00ed}a", 'name' => $category->name, 'description' => $category->description],
            'posts' => $posts,
        ])->withViewData(['seo' => [
            'title' => $category->name." \u{2014} Blog MisterioCode",
            'description' => $category->description ?: "Art\u{00ed}culos sobre {$category->name}.",
            'canonical' => route('blog.category', $category->slug),
            'liteFonts' => true,
        ]]);
    }

    public function tag(string $slug): Response
    {
        $tag = BlogTag::where('slug', $slug)->firstOrFail();
        $posts = $tag->posts()->published()->with(['category', 'tags'])->newest()->paginate(9)
            ->through(fn (BlogPost $post) => $this->card($post));

        return Inertia::render('Blog/BlogArchive', [
            'archive' => ['type' => 'Etiqueta', 'name' => $tag->name, 'description' => null],
            'posts' => $posts,
        ])->withViewData(['seo' => [
            'title' => $tag->name." \u{2014} Blog MisterioCode",
            'description' => "Art\u{00ed}culos etiquetados con {$tag->name}.",
            'canonical' => route('blog.tag', $tag->slug),
            'liteFonts' => true,
        ]]);
    }

    private function card(BlogPost $post): array
    {
        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'cover_url' => $post->coverUrl(),
            'cover_alt' => $post->cover_image_alt,
            'published_at' => $post->published_at?->toIso8601String(),
            'updated_at' => $post->updated_at->toIso8601String(),
            'category' => $post->category ? $post->category->only(['name', 'slug']) : null,
            'tags' => $post->tags->map->only(['name', 'slug'])->values(),
        ];
    }

    private function detail(BlogPost $post): array
    {
        return $this->card($post) + [
            'content_html' => $post->content_html,
            'author' => ['name' => $post->author?->name ?: 'Equipo MisterioCode'],
        ];
    }
}
