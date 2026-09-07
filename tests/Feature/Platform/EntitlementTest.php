<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Actions\GrantCaseAccess;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class EntitlementTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    public function test_granting_access_is_idempotent(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $action = app(GrantCaseAccess::class);

        // A retried purchase webhook must not create a second right.
        $action->grant($user, $case);
        $action->grant($user, $case);

        $this->assertSame(1, Entitlement::count());
        $this->assertTrue($user->ownsCase('steve-jacobs'));
    }

    public function test_access_can_be_revoked(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');
        $action = app(GrantCaseAccess::class);

        $action->grant($user, $case);
        $action->revoke($user, $case);

        $this->assertFalse($user->fresh()->ownsCase('steve-jacobs'));
    }

    public function test_an_expired_entitlement_does_not_grant_access(): void
    {
        $user = $this->userWithoutAccess();
        $case = $this->catalogCase('steve-jacobs');

        app(GrantCaseAccess::class)->grant($user, $case, Entitlement::SOURCE_PURCHASE, now()->subDay());

        // The row exists but the right has lapsed.
        $this->assertSame(1, Entitlement::count());
        $this->assertFalse($user->ownsCase('steve-jacobs'));
    }

    public function test_a_permanent_entitlement_has_no_expiry(): void
    {
        $user = $this->gameMaster();

        $entitlement = $user->entitlements()->first();

        $this->assertNull($entitlement->expires_at);
        $this->assertTrue($entitlement->isActive());
    }

    public function test_owns_case_accepts_a_slug_or_a_model(): void
    {
        $user = $this->gameMaster();
        $case = MysteryCase::firstWhere('slug', 'steve-jacobs');

        $this->assertTrue($user->ownsCase('steve-jacobs'));
        $this->assertTrue($user->ownsCase($case));
        $this->assertFalse($user->ownsCase('some-other-case'));
    }

    public function test_the_library_lists_only_owned_cases(): void
    {
        $user = $this->gameMaster();

        MysteryCase::create(['slug' => 'not-owned', 'name' => 'No comprado']);

        $this->assertSame(['steve-jacobs'], $user->library()->pluck('slug')->all());
    }

    public function test_the_library_is_empty_for_a_new_account(): void
    {
        $this->assertTrue($this->userWithoutAccess()->library()->isEmpty());
    }

    public function test_grant_access_command_grants_and_revokes(): void
    {
        $user = $this->userWithoutAccess(['email' => 'gm@example.com']);
        $this->catalogCase('steve-jacobs');

        $this->artisan('platform:grant-access', ['email' => 'gm@example.com', 'case' => 'steve-jacobs'])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->ownsCase('steve-jacobs'));

        $this->artisan('platform:grant-access', [
            'email' => 'gm@example.com',
            'case' => 'steve-jacobs',
            '--revoke' => true,
        ])->assertSuccessful();

        $this->assertFalse($user->fresh()->ownsCase('steve-jacobs'));
    }

    public function test_grant_access_command_fails_clearly_on_bad_input(): void
    {
        $this->catalogCase('steve-jacobs');

        $this->artisan('platform:grant-access', ['email' => 'nobody@example.com', 'case' => 'steve-jacobs'])
            ->assertFailed();

        $this->userWithoutAccess(['email' => 'gm@example.com']);

        $this->artisan('platform:grant-access', ['email' => 'gm@example.com', 'case' => 'not-a-case'])
            ->assertFailed();
    }

    public function test_deleting_a_user_removes_their_entitlements(): void
    {
        $user = $this->gameMaster();

        $this->assertSame(1, Entitlement::count());

        $user->delete();

        $this->assertSame(0, Entitlement::count());
    }
}
