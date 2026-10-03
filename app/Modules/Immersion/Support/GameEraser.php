<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\TimelineEvent;
use Illuminate\Support\Facades\Storage;

/**
 * Deletes a game and everything under it.
 *
 * Shared by the Game Master's "delete game" button and by account deletion,
 * which has to remove the players' names and emails along with the games they
 * were invited to.
 *
 * Players, their inbox events, interrogations and accusations go via cascade.
 * The generated audio lives on disk rather than in a table, so it is cleaned
 * up by hand — otherwise every deleted game would leave megabytes of orphaned
 * WAVs behind.
 *
 * The credit hold is released BEFORE the delete, because the cascade would take
 * the hold row with it and the frozen credits would never come back to the
 * wallet.
 */
class GameEraser
{
    public function __construct(private AiCredits $credits)
    {
    }

    public function erase(Game $game, ?string $note = null): void
    {
        $this->credits->release($game, $note ?? "Partida eliminada: \"{$game->name}\"");

        // Only recordings this game generated. Case audio is shipped with the
        // case and shared by every table of it — deleting one game must never
        // take a file out of the case package.
        $audioPaths = $game->timelineEvents()
            ->whereNotNull('audio_path')
            ->get()
            ->reject(fn (TimelineEvent $event) => $event->audioIsCaseAsset())
            ->pluck('audio_path')
            ->all();

        $game->delete();

        foreach ($audioPaths as $path) {
            Storage::disk('local')->delete($path);
        }
    }
}
