<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function user(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'gm@example.com',
            'password' => Hash::make('correct-horse-battery'),
        ], $attributes));
    }

    public function test_login_and_register_screens_render(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get(route('password.request'))->assertOk();
    }

    public function test_a_user_can_register_and_lands_signed_in(): void
    {
        $this->post(route('register'), [
            'name' => 'Isabella',
            'email' => 'isabella@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'isabella@example.com']);
    }

    public function test_registration_stores_a_hashed_password(): void
    {
        $this->post(route('register'), [
            'name' => 'Isabella',
            'email' => 'isabella@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ]);

        $user = User::firstWhere('email', 'isabella@example.com');

        $this->assertNotSame('correct-horse-battery', $user->password);
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
    }

    public function test_registration_grants_no_case_access(): void
    {
        $this->post(route('register'), [
            'name' => 'Isabella',
            'email' => 'isabella@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ]);

        $user = User::firstWhere('email', 'isabella@example.com');

        // Access comes from an entitlement, never from having an account.
        $this->assertSame(0, $user->entitlements()->count());
        $this->assertFalse($user->ownsCase('steve-jacobs'));
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        $this->user(['email' => 'taken@example.com']);

        $this->post(route('register'), [
            'name' => 'Otro',
            'email' => 'taken@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registration_requires_a_matching_confirmation(): void
    {
        $this->post(route('register'), [
            'name' => 'Isabella',
            'email' => 'isabella@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'something-else',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_a_user_can_sign_in(): void
    {
        $user = $this->user();

        $this->post(route('login'), [
            'email' => 'gm@example.com',
            'password' => 'correct-horse-battery',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        $this->user();

        $this->post(route('login'), [
            'email' => 'gm@example.com',
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_unknown_email_gives_the_same_error_as_a_wrong_password(): void
    {
        $this->user();

        $unknown = $this->post(route('login'), [
            'email' => 'nobody@example.com',
            'password' => 'whatever',
        ]);

        $wrongPassword = $this->post(route('login'), [
            'email' => 'gm@example.com',
            'password' => 'wrong',
        ]);

        // Identical messages: the form must not reveal which addresses exist.
        $this->assertSame(
            $unknown->getSession()->get('errors')->get('email'),
            $wrongPassword->getSession()->get('errors')->get('email'),
        );
    }

    public function test_a_user_can_sign_out(): void
    {
        $this->actingAs($this->user())
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_signed_in_users_are_kept_out_of_the_guest_screens(): void
    {
        $this->actingAs($this->user())
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_the_private_area_requires_authentication(): void
    {
        foreach (['dashboard', 'library', 'immersion.gm.games.index', 'immersion.gm.games.create', 'profile.edit'] as $routeName) {
            $this->get(route($routeName))->assertRedirect(route('login'));
        }
    }

    public function test_password_reset_request_does_not_reveal_whether_the_account_exists(): void
    {
        $this->user();

        $known = $this->post(route('password.email'), ['email' => 'gm@example.com']);
        $unknown = $this->post(route('password.email'), ['email' => 'nobody@example.com']);

        $known->assertSessionHas('status');
        $unknown->assertSessionHas('status');
        $this->assertSame(
            $known->getSession()->get('status'),
            $unknown->getSession()->get('status'),
        );
    }

    public function test_login_is_rate_limited(): void
    {
        $this->user();

        // The limit is read from config when routes are registered, so this
        // exercises the value the app actually ships with rather than one
        // injected mid-test.
        [$attempts] = explode(',', (string) config('platform.rate_limits.login'));

        for ($attempt = 0; $attempt < (int) $attempts; $attempt++) {
            $this->post(route('login'), ['email' => 'gm@example.com', 'password' => 'wrong'])
                ->assertStatus(302);
        }

        $this->post(route('login'), ['email' => 'gm@example.com', 'password' => 'wrong'])
            ->assertStatus(429);
    }
}
