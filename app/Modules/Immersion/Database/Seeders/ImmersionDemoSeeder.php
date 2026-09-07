<?php

namespace App\Modules\Immersion\Database\Seeders;

use App\Models\User;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Models\Game;
use App\Modules\Platform\Actions\GrantCaseAccess;
use App\Modules\Platform\Models\Entitlement;
use App\Modules\Platform\Models\MysteryCase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * A ready-to-play sample game for local work.
 *
 * It seeds the whole chain a real game needs — an account, access to the case,
 * and only then the game — because a game with no owner is unreachable through
 * the web by design (see GamePolicy). Idempotent: running it twice reuses the
 * demo account instead of piling up duplicates.
 */
class ImmersionDemoSeeder extends Seeder
{
    private const EMAIL = 'gm@example.test';

    private const PASSWORD = 'password';

    public function run(): void
    {
        $case = app(CaseRegistry::class)->default();

        $owner = $this->demoGameMaster();
        $this->grantAccess($owner, $case->slug);

        $game = Game::create([
            'user_id' => $owner->id,
            'name' => 'Partida de prueba - '.$case->name(),
            'case_slug' => $case->slug,
            'case_version' => $case->version(),
            'mode' => Game::MODE_GM_LED,
            'status' => 'draft',
        ]);

        foreach ($this->demoPlayers() as $player) {
            $game->players()->create([
                'name' => $player['name'],
                'email' => $player['email'],
                'access_token' => Str::uuid(),
            ]);
        }

        foreach ($case->timeline() as $event) {
            $game->timelineEvents()->create($event);
        }

        $this->command?->info('Game Master de prueba: '.self::EMAIL.' / '.self::PASSWORD);
        $this->command?->info('Partida creada en /partidas/'.$game->id);
    }

    private function demoGameMaster(): User
    {
        return User::firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Game Master de prueba',
                'password' => Hash::make(self::PASSWORD),
                'email_verified_at' => now(),
            ]
        );
    }

    /**
     * The catalog is derived from the installed manifests, so make sure it has
     * been synced before handing out access to a case that may not be in it.
     */
    private function grantAccess(User $owner, string $caseSlug): void
    {
        if (! MysteryCase::where('slug', $caseSlug)->exists()) {
            Artisan::call('platform:sync-cases');
        }

        $case = MysteryCase::firstWhere('slug', $caseSlug);

        if ($case) {
            app(GrantCaseAccess::class)->grant($owner, $case, Entitlement::SOURCE_GRANT);
        }
    }

    /**
     * @return array<int, array{name: string, email: string}>
     */
    private function demoPlayers(): array
    {
        return [
            ['name' => 'Jugador 1', 'email' => 'jugador1@example.test'],
            ['name' => 'Jugador 2', 'email' => 'jugador2@example.test'],
            ['name' => 'Jugador 3', 'email' => 'jugador3@example.test'],
            ['name' => 'Jugador 4', 'email' => 'jugador4@example.test'],
            ['name' => 'Jugador 5', 'email' => 'jugador5@example.test'],
            ['name' => 'Jugador 6', 'email' => 'jugador6@example.test'],
        ];
    }
}
