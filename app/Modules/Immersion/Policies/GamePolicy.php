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
     * Viewing a game's control panel, its results and its interrogations.
     */
    public function view(User $user, Game $game): bool
    {
        return $this->owns($user, $game);
    }

    /**
     * Starting, pausing, resuming, forcing events, toggling mechanics —
     * anything that changes the run.
     */
    public function control(User $user, Game $game): bool
    {
        return $this->owns($user, $game);
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
