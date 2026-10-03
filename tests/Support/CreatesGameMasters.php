<?php

namespace Tests\Support;

use App\Models\User;
use App\Modules\Immersion\Models\Game;
use App\Modules\Platform\Actions\GrantCaseAccess;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Support\Facades\Artisan;

/**
 * Test setup for the post-accounts world: a Game Master is a real account
 * that holds an entitlement to a case and owns the games it created.
 */
trait CreatesGameMasters
{
    /**
     * A signed-up Game Master who owns the given case (default: the case new
     * games are created from).
     */
    protected function gameMaster(?string $caseSlug = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        $this->grantCase($user, $caseSlug ?? (string) config('immersion.default_case'));

        return $user;
    }

    /**
     * An account with no case access at all, for testing that ownership is
     * actually required.
     */
    protected function userWithoutAccess(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function grantCase(User $user, string $caseSlug): Entitlement
    {
        return app(GrantCaseAccess::class)->grant(
            $user,
            $this->catalogCase($caseSlug),
            Entitlement::SOURCE_GRANT
        );
    }

    /**
     * The catalog row for a case, syncing the catalog from the installed
     * manifests on first use so tests do not hand-write product data.
     *
     * The returned case is always PUBLISHED, and on first use it is the only
     * one. The installed manifests are the real product catalog — ten cases
     * today, some deliberately unpublished (steve-jacobs is) — and a test about
     * checkout or the landing page must not depend on what is on sale this
     * week. Syncing still supplies the manifest's content and price; what the
     * helper pins is which cases are visible.
     */
    protected function catalogCase(string $slug): MysteryCase
    {
        if (MysteryCase::query()->doesntExist()) {
            Artisan::call('platform:sync-cases');

            MysteryCase::where('slug', '!=', $slug)->update(['published_at' => null]);
        }

        $case = MysteryCase::firstWhere('slug', $slug)
            ?? MysteryCase::create(['slug' => $slug, 'name' => $slug]);

        if (! $case->isPublished()) {
            $case->update(['published_at' => now()]);
        }

        return $case;
    }

    /**
     * A game owned by $user, with one player.
     *
     * The player token is derived from the game id: access_token is unique
     * across the whole table, so a fixed literal breaks the moment a test
     * needs two games.
     *
     * @return array{0: Game, 1: \App\Modules\Immersion\Models\Player}
     */
    protected function gameOwnedBy(User $user, array $attributes = []): array
    {
        $game = Game::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Game',
            'status' => 'draft',
        ], $attributes));

        $player = $game->players()->create([
            'name' => 'Player One',
            'email' => "player{$game->id}@example.com",
            'access_token' => "test-token-{$game->id}",
        ]);

        return [$game, $player];
    }
}
