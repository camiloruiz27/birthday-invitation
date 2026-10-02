<?php

namespace Tests\Feature\Platform;

use App\Models\User;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Support\CreatesGameMasters;
use Tests\TestCase;

/**
 * Closing an account erases the person and keeps the books (see
 * DeleteAccount): the privacy policy promises both.
 */
class AccountDeletionTest extends TestCase
{
    use CreatesGameMasters, RefreshDatabase;

    private function accountWithHistory(): User
    {
        $user = $this->gameMaster(null, [
            'name' => 'Isabella Figueroa',
            'email' => 'isabella@example.com',
            'password' => Hash::make('correct-horse-battery'),
        ]);

        $this->gameOwnedBy($user);
        app(AiCredits::class)->walletFor($user);

        Order::create([
            'user_id' => $user->id,
            'type' => Order::TYPE_CREDIT_PACKAGE,
            'amount' => 9900,
            'currency' => 'COP',
            'status' => Order::STATUS_APPROVED,
            'reference' => 'MC-TEST-1',
        ]);

        return $user;
    }

    public function test_deleting_the_account_erases_the_personal_data(): void
    {
        $user = $this->accountWithHistory();

        $this->assertSame(1, Game::count());
        $this->assertSame(1, Player::count());

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'correct-horse-battery'])
            ->assertRedirect(route('home'));

        $this->assertGuest();

        // The games, and with them the names and emails of the players the
        // Game Master had invited — they used to outlive the account.
        $this->assertSame(0, Game::count());
        $this->assertSame(0, Player::count());
        $this->assertSame(0, Entitlement::where('user_id', $user->id)->count());
        $this->assertSame(0, DB::table('ai_credit_wallets')->where('user_id', $user->id)->count());
    }

    public function test_the_user_row_is_anonymised_not_removed(): void
    {
        $user = $this->accountWithHistory();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'correct-horse-battery']);

        $user = $user->fresh();

        $this->assertNotNull($user);
        $this->assertSame('Cuenta eliminada', $user->name);
        $this->assertStringNotContainsString('isabella', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertFalse($user->is_admin);
    }

    public function test_payment_records_survive_the_account(): void
    {
        $user = $this->accountWithHistory();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'correct-horse-battery']);

        // The accounting record the policy says is kept (Ley 962 de 2005).
        // It used to cascade away with the user row.
        $order = Order::firstWhere('reference', 'MC-TEST-1');

        $this->assertNotNull($order);
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame(9900, $order->amount);
    }

    public function test_a_deleted_account_cannot_sign_in_again_and_frees_the_email(): void
    {
        $user = $this->accountWithHistory();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'correct-horse-battery']);

        $this->post(route('login'), [
            'email' => 'isabella@example.com',
            'password' => 'correct-horse-battery',
        ]);
        $this->assertGuest();

        // The address can be registered again, as a new person.
        $this->post(route('register'), [
            'name' => 'Isabella Nueva',
            'email' => 'isabella@example.com',
            'password' => 'another-long-password',
            'password_confirmation' => 'another-long-password',
            'accept_terms' => true,
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(2, User::count());
    }

    public function test_a_wrong_password_deletes_nothing(): void
    {
        $user = $this->accountWithHistory();

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'not-my-password'])
            ->assertSessionHasErrors('password');

        $this->assertAuthenticatedAs($user);
        $this->assertSame('Isabella Figueroa', $user->fresh()->name);
        $this->assertSame(1, Game::count());
    }

    public function test_it_only_touches_the_account_that_asked(): void
    {
        $user = $this->accountWithHistory();
        $other = $this->gameMaster(null, ['email' => 'otro@example.com']);
        $this->gameOwnedBy($other);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'correct-horse-battery']);

        $this->assertSame('otro@example.com', $other->fresh()->email);
        $this->assertSame(1, Game::count());
        $this->assertSame(1, Game::where('user_id', $other->id)->count());
    }

    public function test_the_profile_page_offers_the_deletion(): void
    {
        $this->actingAs($this->gameMaster())
            ->get(route('profile.edit'))
            ->assertOk();
    }
}
