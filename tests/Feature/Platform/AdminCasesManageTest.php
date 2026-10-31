<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class AdminCasesManageTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['platform.simulated_checkout' => true]);
    }

    private function admin(): User
    {
        $user = $this->gameMaster(attributes: ['email' => 'admin@example.com']);
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    public function test_only_an_administrator_can_see_or_change_case_publication(): void
    {
        $case = $this->catalogCase('steve-jacobs');

        $this->get(route('admin.cases'))->assertRedirect(route('login'));
        $this->patch(route('admin.cases.toggle', $case))->assertRedirect(route('login'));

        $member = $this->gameMaster(attributes: ['email' => 'member@example.com']);
        $this->actingAs($member)->get(route('admin.cases'))->assertNotFound();
        $this->actingAs($member)->patch(route('admin.cases.toggle', $case))->assertNotFound();
    }

    public function test_an_administrator_sees_sale_and_content_availability_for_every_case(): void
    {
        $admin = $this->admin();
        $published = $this->catalogCase('steve-jacobs');
        $draft = MysteryCase::firstWhere('slug', 'la-feria-de-las-profecias');
        $draft->update(['published_at' => null]);
        $missing = MysteryCase::create(['slug' => 'missing-case', 'name' => 'Sin contenido', 'published_at' => null]);

        $this->actingAs($admin)
            ->get(route('admin.cases'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Cases')
                ->has('cases')
            );

        $payload = $this->actingAs($admin)->get(route('admin.cases'))->viewData('page')['props']['cases'];
        $bySlug = collect($payload)->keyBy('slug');

        $this->assertFalse($bySlug[$draft->slug]['published']);
        $this->assertTrue($bySlug[$draft->slug]['playable']);
        $this->assertFalse($bySlug[$missing->slug]['published']);
        $this->assertFalse($bySlug[$missing->slug]['playable']);
        $this->assertTrue($bySlug[$published->slug]['published']);
        $this->assertTrue($bySlug[$published->slug]['playable']);
    }

    public function test_an_administrator_can_publish_then_take_a_case_off_sale_without_removing_owned_access(): void
    {
        $admin = $this->admin();
        $case = $this->catalogCase('steve-jacobs');
        $case->update(['published_at' => null]);
        $buyer = $this->userWithoutAccess();

        $this->actingAs($admin)
            ->patch(route('admin.cases.toggle', $case))
            ->assertRedirect(route('admin.cases'));

        $this->assertTrue($case->fresh()->isPublished());
        $this->get(route('cases.show', $case->slug))->assertOk();
        $this->get(route('ads.case', $case->slug))->assertOk();

        $this->actingAs($buyer)
            ->post(route('cases.acquire', $case->slug))
            ->assertRedirect(route('dashboard'));
        $this->assertTrue($buyer->fresh()->ownsCase($case->slug));

        $this->actingAs($admin)
            ->patch(route('admin.cases.toggle', $case->fresh()))
            ->assertRedirect(route('admin.cases'));

        $this->assertFalse($case->fresh()->isPublished());
        $this->get(route('cases.show', $case->slug))->assertNotFound();
        $this->get(route('ads.case', $case->slug))->assertNotFound();
        $this->actingAs($buyer)->post(route('cases.acquire', $case->slug))->assertNotFound();
        $this->actingAs($buyer)
            ->get(route('library'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('cases.0.slug', $case->slug)
                ->where('cases.0.playable', true)
            );
    }

    public function test_an_administrator_cannot_publish_a_case_without_its_manifest(): void
    {
        $case = MysteryCase::create(['slug' => 'missing-case', 'name' => 'Sin contenido', 'published_at' => null]);

        $this->actingAs($this->admin())
            ->patch(route('admin.cases.toggle', $case))
            ->assertRedirect(route('admin.cases'))
            ->assertSessionHasErrors('case');

        $this->assertFalse($case->fresh()->isPublished());
    }
}
