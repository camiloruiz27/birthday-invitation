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
        $included = User::factory()->marketingConsent()->create(['email' => 'included@example.com']);
        $excluded = User::factory()->marketingConsent()->create(['email' => 'excluded@example.com']);
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

    public function test_only_administrators_can_see_the_offer_preview(): void
    {
        $member = User::factory()->create();

        $this->get(route('admin.email-marketing.preview'))->assertRedirect(route('login'));
        $this->actingAs($member)->get(route('admin.email-marketing.preview'))->assertNotFound();
    }

    public function test_the_preview_shows_the_mail_without_creating_a_code_or_sending_anything(): void
    {
        // Not Mail::fake(): the preview renders the mail through the real
        // mailer, which the fake does not provide. A listener counts sends.
        $sent = 0;
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSending::class, function () use (&$sent) {
            $sent++;
        });
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('admin.email-marketing.preview'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/html; charset=UTF-8');
        $response->assertSee('Vista previa', false);
        $response->assertSee('PRIMERACOMPRA-A1B2C3', false);
        $response->assertSee('50% menos en tu próximo caso', false);
        // The embedded-logo reference a browser cannot resolve is swapped for a URL.
        $response->assertDontSee('src="cid:', false);
        $response->assertSee('/brand/isotipo-email.png', false);

        $this->assertSame(0, $sent);
        $this->assertSame(0, \App\Modules\Platform\Models\PromoCode::query()->count());
        $this->assertNull($admin->fresh()->first_game_offer_sent_at);
    }

    public function test_the_panel_reports_the_automatic_offer_and_who_is_waiting_for_it(): void
    {
        config(['platform.first_game_offer.enabled' => false, 'platform.first_game_offer.delay_days' => 5]);
        $admin = $this->admin();
        $waiting = User::factory()->marketingConsent()->create();
        \App\Modules\Immersion\Models\Game::create([
            'user_id' => $waiting->id, 'name' => 'Vieja', 'status' => 'finished', 'finished_at' => now()->subDays(9),
        ]);
        // Closed yesterday: not due yet.
        $recent = User::factory()->marketingConsent()->create();
        \App\Modules\Immersion\Models\Game::create([
            'user_id' => $recent->id, 'name' => 'Reciente', 'status' => 'finished', 'finished_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)->get(route('admin.email-marketing'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('autoOffer.enabled', false)
                ->where('autoOffer.discount_percent', 50)
                ->where('autoOffer.delay_days', 5)
                ->where('autoOffer.pending', 1));
    }

    public function test_csv_leaves_out_anyone_who_never_authorised_offers(): void
    {
        $admin = $this->admin();
        User::factory()->marketingConsent()->create(['email' => 'si@example.com']);
        User::factory()->create(['email' => 'nunca-autorizo@example.com']);

        $csv = $this->actingAs($admin)->get(route('admin.email-marketing.export'))->streamedContent();

        $this->assertStringContainsString('si@example.com', $csv);
        $this->assertStringNotContainsString('nunca-autorizo@example.com', $csv);

        $this->actingAs($admin)
            ->get(route('admin.email-marketing', ['marketing' => 'no_consent']))
            ->assertInertia(fn (Assert $page) => $page
                ->where('summary.without_consent', 1)
                ->has('contacts', 1)
                ->where('contacts.0.consented', false));
    }

    public function test_csv_contains_only_valid_non_excluded_contacts_and_records_the_export(): void
    {
        $admin = $this->admin();
        User::factory()->marketingConsent()->create(['name' => 'Ana Pérez', 'email' => 'ana@example.com']);
        User::factory()->marketingConsent()->create(['name' => 'Correo Malo', 'email' => 'correo-invalido']);
        $excluded = User::factory()->marketingConsent()->create(['name' => 'No Contactar', 'email' => 'no@example.com']);
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
