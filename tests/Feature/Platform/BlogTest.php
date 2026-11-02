<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Models\BlogCategory;
use App\Modules\Platform\Models\BlogPost;
use App\Modules\Platform\Models\BlogTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    private function blogPost(array $attributes = []): BlogPost
    {
        $category = BlogCategory::firstOrCreate(['slug' => 'guias'], ['name' => 'GuÃ­as']);

        return BlogPost::create(array_merge([
            'user_id' => User::factory()->create()->id,
            'blog_category_id' => $category->id,
            'title' => 'CÃ³mo preparar una noche de misterio',
            'slug' => 'como-preparar-una-noche-de-misterio',
            'excerpt' => 'Una guÃ­a clara para organizar una experiencia compartida.',
            'content' => ['type' => 'doc', 'content' => []],
            'content_html' => '<h2>Preguntas frecuentes</h2><h3>Â¿QuÃ© necesito?</h3><p>Un equipo con ganas de investigar.</p>',
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subMinute(),
        ], $attributes));
    }

    public function test_public_blog_only_exposes_published_posts_and_their_archives(): void
    {
        $published = $this->blogPost();
        $draft = $this->blogPost([
            'title' => 'Borrador interno',
            'slug' => 'borrador-interno',
            'status' => BlogPost::STATUS_DRAFT,
            'published_at' => null,
        ]);
        $tag = BlogTag::create(['name' => 'Equipos', 'slug' => 'equipos']);
        $published->tags()->attach($tag);
        $draft->tags()->attach($tag);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Blog/BlogIndex')
                ->has('posts.data', 1)
                ->where('posts.data.0.slug', $published->slug)
            );
        $this->get(route('blog.show', $published->slug))->assertOk();
        $this->get(route('blog.show', $draft->slug))->assertNotFound();
        $this->get(route('blog.category', 'guias'))->assertOk();
        $this->get(route('blog.tag', 'equipos'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('posts.data', 1));
    }

    public function test_only_administrators_can_manage_posts_and_saved_html_is_sanitized(): void
    {
        $payload = [
            'title' => 'El mejor plan para una noche de misterio',
            'slug' => 'plan-noche-de-misterio',
            'excerpt' => 'Resumen para buscadores.',
            'content' => json_encode(['type' => 'doc', 'content' => []]),
            'content_html' => '<p>Contenido seguro.</p><script>alert("xss")</script><a href="javascript:alert(1)">Enlace</a>',
            'status' => BlogPost::STATUS_PUBLISHED,
        ];

        $this->post(route('admin.blog.store'), $payload)->assertRedirect(route('login'));
        $member = User::factory()->create();
        $this->actingAs($member)->post(route('admin.blog.store'), $payload)->assertNotFound();

        $this->actingAs($this->admin())
            ->post(route('admin.blog.store'), $payload)
            ->assertRedirect();

        $post = BlogPost::firstWhere('slug', 'plan-noche-de-misterio');
        $this->assertNotNull($post);
        $this->assertTrue($post->isPublished());
        $this->assertStringNotContainsString('<script', $post->content_html);
        $this->assertStringNotContainsString('javascript:', $post->content_html);
    }

    public function test_article_head_schema_feed_and_sitemap_only_reference_published_content(): void
    {
        config(['platform.indexable' => true]);
        $published = $this->blogPost();
        $draft = $this->blogPost(['title' => 'No publicar', 'slug' => 'no-publicar', 'status' => BlogPost::STATUS_DRAFT, 'published_at' => null]);

        $html = $this->get(route('blog.show', $published->slug))->assertOk()->getContent();
        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringContainsString('FAQPage', $html);
        $this->assertStringContainsString('rel="canonical"', $html);

        $this->get(route('blog.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee($published->slug)
            ->assertDontSee($draft->slug);
        $this->get(route('blog.rss'))
            ->assertOk()
            ->assertSee($published->slug)
            ->assertDontSee($draft->slug);
    }
}
