<?php

namespace App\Modules\Immersion\Policies;

use App\Models\User;
use App\Modules\Immersion\Models\Game;

/**
 * A game belongs to the account that created it. Hiding a button in React is
 * not authorization — every Game Master route runs through here.
 */
class GamePolicy
{
    /**
     * Viewing a game's console.
     */
    public function view(User $user, Game $game): bool
    {
        return $this->owns($user, $game);
    }

    /**
     * Starting, pausing, resuming, ending — the controls that exist in both
     * modes because someone still has to say when the case begins and ends.
     */
    public function control(User $user, Game $game): bool
    {
        return $this->owns($user, $game);
    }

    /**
     * Deleting a game, which is how a slot is freed against the quota.
     *
     * Allowed in both modes and at any point in a run: it is the owner's data,
     * and the confirmation in the interface is what makes it deliberate.
     */
    public function delete(User $user, Game $game): bool
    {
        return $this->owns($user, $game);
    }

    /**
     * Directing: forcing the next event and toggling mechanics by hand.
     *
     * Only in gm_led mode. In automatic mode the owner is a player, so
     * reaching into the timeline would be peeking at — and rewriting — their
     * own game.
     */
    public function direct(User $user, Game $game): bool
    {
        return $this->owns($user, $game) && ! $game->isAutomatic();
    }

    /**
     * Reading the interrogation transcripts and everyone's accusations.
     *
     * These are the two things that give the case away. A directing Game
     * Master is meant to see them; an owner who is playing may not, until the
     * case is over.
     */
    public function viewSpoilers(User $user, Game $game): bool
    {
        if (! $this->owns($user, $game)) {
            return false;
        }

        return ! $game->isAutomatic() || $game->isFinished();
    }

    /**
     * An unowned game (created before accounts existed) is nobody's to reach
     * through the web: it has to be claimed on the command line first, which
     * is a deliberate, auditable step rather than a race to open the URL.
     */
    private function owns(User $user, Game $game): bool
    {
        return $game->user_id !== null
            && (int) $game->user_id === (int) $user->id;
    }
}
