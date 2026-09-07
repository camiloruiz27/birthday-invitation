<?php

namespace App\Modules\Platform\Console\Commands;

use App\Models\User;
use App\Modules\Immersion\Models\Game;
use Illuminate\Console\Command;

/**
 * Assigns an owner to games created before accounts existed.
 *
 * Games from the shared-password era have user_id = null and are therefore
 * unreachable through the web (GamePolicy denies unowned games rather than
 * letting the first visitor take them). This is the deliberate, auditable
 * step that hands them to a real account instead of deleting play data.
 */
class ClaimGames extends Command
{
    protected $signature = 'platform:claim-games
                            {email : Account that should own the games}
                            {--game=* : Specific game ids; omit to claim every unowned game}
                            {--dry-run : List what would be claimed without writing}';

    protected $description = 'Assign ownerless games (pre-accounts) to a user account.';

    public function handle(): int
    {
        $user = User::firstWhere('email', $this->argument('email'));

        if (! $user) {
            $this->error("No account found for {$this->argument('email')}.");

            return self::FAILURE;
        }

        $query = Game::query()->whereNull('user_id');

        if ($ids = $this->option('game')) {
            $query->whereIn('id', $ids);
        }

        $games = $query->orderBy('id')->get();

        if ($games->isEmpty()) {
            $this->info('No ownerless games to claim.');

            return self::SUCCESS;
        }

        foreach ($games as $game) {
            $this->line("  #{$game->id}  {$game->name}  [{$game->case_slug}]");
        }

        if ($this->option('dry-run')) {
            $this->info($games->count()." game(s) would be claimed by {$user->email}. Nothing written.");

            return self::SUCCESS;
        }

        Game::whereIn('id', $games->pluck('id'))->update(['user_id' => $user->id]);

        $this->info($games->count()." game(s) now owned by {$user->email}.");
        $this->comment("Make sure {$user->email} also holds access to the cases involved (platform:grant-access).");

        return self::SUCCESS;
    }
}
