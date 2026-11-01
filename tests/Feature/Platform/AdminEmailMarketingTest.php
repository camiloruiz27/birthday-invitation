<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Platform\Models\MarketingExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class AdminEmailMarketingTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->forceFill(['is_admin' => true])->save();

        return $admin->fresh();
    }

    public function test_only_administrators_can_access_email_marketing(): void
    {
        $member = User::factory()->create();

        $this->get(route('admin.email-marketing'))->assertRedirect(route('login'));
        $this->actingAs($member)->get(route('admin.email-marketing'))->assertNotFound();
        $this->actingAs($member)->get(route('admin.email-marketing.export'))->assertNotFound();
    }

    public function test_admin_sees_contact_summary_filters_and_recent_published_cases(): void
    {
        $admin = $this->admin();
        $included = User::factory()->create(['email' => 'included@example.com']);
        $excluded = User::factory()->create(['email' => 'excluded@example.com']);
        $excluded->forceFill(['marketing_opted_out_at' => now()])->save();
        $this->catalogCase('steve-jacobs');

        $this->actingAs($admin)
            ->get(route('admin.email-marketing', ['marketing' => 'included']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/EmailMarketing')
                ->where('summary.active_clients', 2)
                ->where('summary.excluded_contacts', 1)
                ->where('filters.marketing', 'included')
                ->has('contacts', 1)
                ->has('recentCases', 1)
            );

        $this->assertDatabaseHas('users', ['id' => $included->id]);
    }

    public function test_admin_can_exclude_and_restore_a_contact(): void
    {
        $admin = $this->admin();
        $contact = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.email-marketing.exclusion', $contact))
            ->assertSessionHas('status');
        $this->assertNotNull($contact->fresh()->marketing_opted_out_at);

        $this->actingAs($admin)
            ->patch(route('admin.email-marketing.exclusion', $contact))
            ->assertSessionHas('status');
        $this->assertNull($contact->fresh()->marketing_opted_out_at);
    }

    public function test_csv_contains_only_valid_non_excluded_contacts_and_records_the_export(): void
    {
        $admin = $this->admin();
        User::factory()->create(['name' => 'Ana Pérez', 'email' => 'ana@example.com']);
        User::factory()->create(['name' => 'Correo Malo', 'email' => 'correo-invalido']);
        $excluded = User::factory()->create(['name' => 'No Contactar', 'email' => 'no@example.com']);
        $excluded->forceFill(['marketing_opted_out_at' => now()])->save();

        $response = $this->actingAs($admin)->get(route('admin.email-marketing.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertDownload('brevo-contactos-'.now()->toDateString().'.csv');
        $csv = $response->streamedContent();
        $this->assertStringContainsString('ana@example.com', $csv);
        $this->assertStringNotContainsString('correo-invalido', $csv);
        $this->assertStringNotContainsString('no@example.com', $csv);
        $this->assertDatabaseHas('marketing_exports', ['exported_by' => $admin->id, 'contacts_count' => 1]);
        $this->assertSame(1, MarketingExport::query()->count());
    }
}
