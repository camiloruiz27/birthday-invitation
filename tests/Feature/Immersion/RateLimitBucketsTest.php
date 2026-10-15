<?php

namespace Tests\Feature\Immersion;

use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Every limit has its own bucket.
 *
 * A numeric `throttle:N,M` with no prefix is counted under ONE key per IP (or per
 * user), whichever route it sits on. Found on the test site under load: the
 * change-polling route (240/min) and the question route (20/min) spent the same
 * counter, so a handful of players on one connection polling every few seconds
 * got "Too Many Attempts" the first time they asked a question, and the same
 * polling spent the login form's five attempts. A table sharing one internet
 * connection is the normal way to play this game.
 */
class RateLimitBucketsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Http::fake(['*' => Http::response(['reply' => 'No fui yo.'], 200)]);
    }

    /** @return array{0: Game, 1: array<int, string>} */
    private function tableOf(int $players): array
    {
        $game = Game::create(['name' => 'Mesa', 'status' => 'running', 'interrogation_enabled' => true]);
        $tokens = [];

        foreach (range(1, $players) as $i) {
            $tokens[] = $game->players()->create([
                'name' => "Jugador {$i}",
                'email' => "j{$i}@example.com",
                'access_token' => "limites-{$i}",
            ])->access_token;
        }

        return [$game, $tokens];
    }

    /** @return array<int, string> */
    private function suspects(): array
    {
        return array_keys(app(CaseRegistry::class)->get('steve-jacobs')->interrogableSuspects());
    }

    private function ask(string $token, string $suspect)
    {
        return $this->postJson(route('immersion.player.interrogation.ask', [$token, $suspect]), ['question' => '¿Dónde estaba?']);
    }

    public function test_polling_does_not_spend_the_question_allowance(): void
    {
        [, [$token]] = $this->tableOf(1);

        // Far more polls than the question route allows in a minute.
        foreach (range(1, 40) as $i) {
            $this->getJson(route('immersion.poll.player', $token))->assertOk();
        }

        $this->ask($token, $this->suspects()[0])->assertOk();
    }

    public function test_polling_does_not_spend_the_login_attempts(): void
    {
        [, [$token]] = $this->tableOf(1);

        foreach (range(1, 20) as $i) {
            $this->getJson(route('immersion.poll.player', $token))->assertOk();
        }

        // A wrong password answers with a validation error, not with "too many attempts".
        $this->post(route('login'), ['email' => 'nadie@example.com', 'password' => 'incorrecta'])
            ->assertSessionHasErrors();
    }

    public function test_each_player_has_their_own_question_allowance_even_on_one_connection(): void
    {
        config(['immersion.rate_limits.interrogation_ask' => '2,1']);

        [, [$ana, $beto]] = $this->tableOf(2);
        [$first, $second, $third] = $this->suspects();

        // Same IP for both (a test client has one): Ana spends hers...
        $this->ask($ana, $first)->assertOk();
        $this->ask($ana, $second)->assertOk();
        $this->ask($ana, $third)->assertStatus(429);

        // ...and Beto, on the same connection, is untouched.
        $this->ask($beto, $third)->assertOk();
    }

    public function test_a_malformed_limit_falls_back_instead_of_removing_it(): void
    {
        config(['immersion.rate_limits.interrogation_ask' => 'lots']);

        [, [$token]] = $this->tableOf(1);
        $suspects = $this->suspects();

        // The fallback is 20 a minute; the twenty-first is refused.
        foreach (range(1, 20) as $i) {
            $this->ask($token, $suspects[$i % count($suspects)])->assertStatus(200);
        }

        $this->ask($token, $suspects[0])->assertStatus(429);
    }
}
