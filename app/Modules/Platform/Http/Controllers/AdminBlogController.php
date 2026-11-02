<?php

namespace App\Modules\Platform\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Platform\Models\BlogCategory;
use App\Modules\Platform\Models\BlogPost;
use App\Modules\Platform\Models\BlogTag;
use App\Modules\Platform\Support\BlogContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminBlogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Blog/Posts', [
            'posts' => BlogPost::query()->with(['author:id,name', 'category:id,name'])->latest()->paginate(20)
                ->through(fn (BlogPost $post) => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'status' => $post->status,
                    'featured' => $post->featured,
                    'published_at' => $post->published_at?->toIso8601String(),
                    'updated_at' => $post->updated_at->toIso8601String(),
                    'author' => $post->author?->name,
                    'category' => $post->category?->name,
                ]),
        ]);
    }

    public function create(): Response
    {
        return $this->editor(null);
    }

    public function edit(BlogPost $blogPost): Response
    {
        return $this->editor($blogPost->load('tags:id'));
    }

    public function preview(BlogPost $blogPost): Response
    {
        $blogPost->load(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug']);

        return Inertia::render('Blog/BlogShow', [
            'post' => [
                'title' => $blogPost->title,
                'slug' => $blogPost->slug,
                'excerpt' => $blogPost->excerpt,
                'content_html' => $blogPost->content_html,
                'cover_url' => $blogPost->coverUrl(),
                'cover_alt' => $blogPost->cover_image_alt,
                'published_at' => ($blogPost->published_at ?: $blogPost->created_at)->toIso8601String(),
                'updated_at' => $blogPost->updated_at->toIso8601String(),
                'author' => ['name' => $blogPost->author?->name ?: 'Equipo MisterioCode'],
                'category' => $blogPost->category?->only(['name', 'slug']),
                'tags' => $blogPost->tags->map->only(['name', 'slug'])->values(),
            ],
            'related' => [],
        ])->withViewData(['seo' => [
            'title' => 'Vista previa: '.$blogPost->title,
            'robots' => 'noindex, nofollow',
            'liteFonts' => true,
        ]]);
    }

    public function store(Request $request): RedirectResponse
    {
        $post = new BlogPost(['user_id' => $request->user()->id]);
        $this->write($request, $post);

        return redirect()->route('admin.blog.edit', $post)->with('status', "Art\u{00ed}culo guardado.");
    }

    public function update(Request $request, BlogPost $blogPost): RedirectResponse
    {
        $this->write($request, $blogPost);

        return redirect()->route('admin.blog.edit', $blogPost)->with('status', "Art\u{00ed}culo actualizado.");
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'alt' => ['required', 'string', 'max:255'],
        ]);

        $path = $data['image']->store('blog/content', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path), 'alt' => $data['alt']]);
    }

    private function editor(?BlogPost $post): Response
    {
        return Inertia::render('Admin/Blog/EditPost', [
            'post' => $post ? [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'content' => $post->content,
                'content_html' => $post->content_html,
                'cover_url' => $post->coverUrl(),
                'cover_alt' => $post->cover_image_alt,
                'category_id' => $post->blog_category_id,
                'tag_ids' => $post->tags->pluck('id')->values(),
                'status' => $post->status,
                'featured' => $post->featured,
                'seo_title' => $post->seo_title,
                'seo_description' => $post->seo_description,
                'canonical_url' => $post->canonical_url,
            ] : null,
            'categories' => BlogCategory::orderBy('name')->get(['id', 'name']),
            'tags' => BlogTag::orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function write(Request $request, BlogPost $post): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_posts', 'slug')->ignore($post->id)],
            'excerpt' => ['required', 'string', 'max:500'],
            'content' => ['nullable', 'json'],
            'content_html' => ['nullable', 'string', 'max:1000000'],
            'category_id' => ['nullable', Rule::exists('blog_categories', 'id')],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => [Rule::exists('blog_tags', 'id')],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cover_alt' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in([BlogPost::STATUS_DRAFT, BlogPost::STATUS_PUBLISHED])],
            'featured' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
        ], [
            'slug.regex' => "El slug solo puede usar min\u{00fa}sculas, n\u{00fa}meros y guiones.",
            'slug.unique' => "Ya existe un art\u{00ed}culo con ese slug.",
        ]);

        if ($request->hasFile('cover_image') && blank($data['cover_alt'] ?? null)) {
            throw ValidationException::withMessages([
                'cover_alt' => 'Describe la imagen destacada para lectores de pantalla y buscadores.',
            ]);
        }

        $slug = $data['slug'] ?: Str::slug($data['title']);
        if ($slug === '') $slug = 'articulo';
        if (BlogPost::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
            $slug .= '-'.Str::lower(Str::random(5));
        }

        $coverPath = $post->cover_image_path;
        if ($request->hasFile('cover_image')) {
            $coverPath = $data['cover_image']->store('blog/covers', 'public');
        }

        $html = BlogContent::sanitize((string) ($data['content_html'] ?? ''));
        $content = isset($data['content']) && $data['content'] !== null
            ? json_decode($data['content'], true, 512, JSON_THROW_ON_ERROR)
            : null;
        $publishing = $data['status'] === BlogPost::STATUS_PUBLISHED;

        $post->fill([
            'blog_category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'],
            'content' => $content,
            'content_html' => $html,
            'cover_image_path' => $coverPath,
            'cover_image_alt' => $data['cover_alt'] ?? $post->cover_image_alt,
            'status' => $data['status'],
            'featured' => (bool) ($data['featured'] ?? false),
            'published_at' => $publishing ? ($post->published_at ?: now()) : null,
            'seo_title' => $data['seo_title'] ?? null,
            'seo_description' => $data['seo_description'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
        ]);
        $post->save();
        $post->tags()->sync($data['tag_ids'] ?? []);
    }
}
