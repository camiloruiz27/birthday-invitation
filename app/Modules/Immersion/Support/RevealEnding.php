<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\Game;
use Illuminate\Support\Carbon;

/**
 * Publishes a game's ending.
 *
 * Happens exactly once per game, guarded by a conditional UPDATE. That matters
 * little today — the classic ending only flips a timestamp — but the advanced
 * endings send an email to every player and spend AI credits, and this is the
 * only thing standing between a double click and two rounds of both.
 *
 * Revealing does NOT finish the game. The premium ending hands the Game Master
 * an audio to play at the table before they close the case, and finishing is
 * where the credit hold will be released.
 */
class RevealEnding
{
    public const BY_AUTO = 'auto';

    public const BY_GAME_MASTER = 'gm';

    public function __construct(private AccusationScoreboard $scoreboard)
    {
    }

    /**
     * Called after every accusation. Reveals only once the whole table has
     * answered — which is what makes the classic ending feel automatic.
     */
    public function attemptAutomatic(Game $game): bool
    {
        if (! $this->isRevealable($game) || ! $game->everyoneAccused()) {
            return false;
        }

        return $this->markRevealed($game, self::BY_AUTO);
    }

    /**
     * The Game Master's escape hatch, for when someone at the table never
     * submits and the automatic trigger can therefore never fire.
     */
    public function force(Game $game): bool
    {
        if (! $this->isRevealable($game)) {
            return false;
        }

        return $this->markRevealed($game, self::BY_GAME_MASTER);
    }

    private function isRevealable(Game $game): bool
    {
        return ! $game->endingRevealed()
            && $game->accusationsUnlocked()
            && $game->caseDefinition()->hasSolution();
    }

    /**
     * The conditional UPDATE is the lock: whoever flips the timestamp first
     * owns the reveal, and everyone else no-ops.
     */
    private function markRevealed(Game $game, string $by): bool
    {
        $claimed = Game::query()
            ->whereKey($game->getKey())
            ->whereNull('ending_revealed_at')
            ->update([
                'ending_revealed_at' => Carbon::now(),
                'ending_revealed_by' => $by,
            ]);

        if ($claimed === 0) {
            return false;
        }

        $game->refresh();

        // Freeze each verdict now, while the manifest still says what it said
        // during this game.
        $this->scoreboard->persistVerdicts($game);

        return true;
    }
}
