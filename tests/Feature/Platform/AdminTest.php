<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Immersion\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function admin(): User
    {
        $user = $this->gameMaster(attributes: ['email' => 'admin@example.com']);

        // forceFill because is_admin is deliberately not mass-assignable.
        $user->forceFill(['is_admin' => true])->save();

        return $user->fresh();
    }

    public function test_the_admin_area_is_invisible_to_everyone_else(): void
    {
        // 404 for everyone who is not an administrator, signed in or not.
        // Not 403, and not a login redirect either: neither an ordinary
        // account nor a stranger has any business learning that an admin area
        // lives at this URL.
        $this->actingAs($this->gameMaster())
            ->get(route('admin.dashboard'))
            ->assertNotFound();

        $this->get(route('admin.dashboard'))->assertNotFound();
    }

    public function test_an_administrator_reaches_the_dashboard(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Dashboard')
                ->has('metrics.people')
                ->has('metrics.usage')
                ->has('metrics.catalog')
                ->has('metrics.ai')
                ->has('metrics.health')
            );
    }

    public function test_admin_cannot_be_granted_through_mass_assignment(): void
    {
        // Registration is the obvious attack surface for a privilege flag.
        $this->post(route('register'), [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'is_admin' => true,
        ]);

        $this->assertFalse(User::firstWhere('email', 'intruso@example.com')->is_admin);
    }

    public function test_the_console_command_grants_and_revokes(): void
    {
        $user = $this->gameMaster(attributes: ['email' => 'creador@example.com']);

        $this->artisan('platform:make-admin', ['email' => 'creador@example.com'])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);

        // A second admin exists, so this one can be demoted.
        $this->admin();

        $this->artisan('platform:make-admin', [
            'email' => 'creador@example.com',
            '--revoke' => true,
        ])->assertSuccessful();

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_the_command_refuses_to_leave_the_platform_without_an_admin(): void
    {
        $admin = $this->admin();

        $this->artisan('platform:make-admin', [
            'email' => $admin->email,
            '--revoke' => true,
        ])->assertFailed();

        $this->assertTrue($admin->fresh()->is_admin);
    }

    public function test_the_command_fails_clearly_for_an_unknown_account(): void
    {
        $this->artisan('platform:make-admin', ['email' => 'nadie@example.com'])
            ->assertFailed();
    }

    public function test_metrics_separate_accounts_from_invited_players(): void
    {
        $admin = $this->admin();
        [$game] = $this->gameOwnedBy($admin);

        // Two more invited players, one repeating an address across games.
        $game->players()->create([
            'name' => 'Ana', 'email' => 'ana@example.com', 'access_token' => 'tok-ana',
        ]);
        [$second] = $this->gameOwnedBy($admin);
        $second->players()->create([
            'name' => 'Ana', 'email' => 'ana@example.com', 'access_token' => 'tok-ana-2',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // One account, which is a Game Master because it runs games.
                ->where('metrics.people.users', 1)
                ->where('metrics.people.game_masters', 1)
                // Four player rows across two games, three distinct addresses.
                ->where('metrics.people.players', 4)
                ->where('metrics.people.players_unique', 3)
            );
    }

    public function test_metrics_report_the_most_played_case_and_average_duration(): void
    {
        $admin = $this->admin();

        [$finished] = $this->gameOwnedBy($admin, [
            'status' => 'running',
            'started_at' => now()->subMinutes(90),
        ]);
        $finished->finish();

        $this->gameOwnedBy($admin);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('metrics.topCases.0.slug', 'steve-jacobs')
                ->where('metrics.topCases.0.games', 2)
                ->where('metrics.usage.average_minutes', 90)
                ->where('metrics.usage.games', 2)
            );
    }

    public function test_duration_is_null_rather_than_zero_when_nothing_finished(): void
    {
        $admin = $this->admin();
        $this->gameOwnedBy($admin);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                // "No data yet" must not read as "average is zero minutes".
                ->where('metrics.usage.average_minutes', null)
                ->where('metrics.usage.accusation_rate', null)
            );
    }

    public function test_metrics_flag_games_nobody_owns(): void
    {
        $admin = $this->admin();

        $orphan = Game::create(['name' => 'Partida antigua', 'status' => 'draft']);
        $orphan->forceFill(['user_id' => null])->save();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('metrics.health.orphan_games', 1)
            );
    }
}
