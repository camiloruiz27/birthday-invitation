<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\CaseRating;
use App\Modules\Immersion\Models\Player;

/** The only rating information a player may see: their own. */
class PlayerRatingData
{
    public static function for(Player $player): ?array
    {
        $game = $player->game;

        if (! $game->endingRevealed() && ! $game->isFinished()) {
            return null;
        }

        $rating = CaseRating::query()
            ->where('game_id', $game->id)
            ->where('player_id', $player->id)
            ->first();

        return [
            'submitted' => $rating !== null,
            'rating_half_steps' => $rating?->rating_half_steps,
            'tags' => $rating?->tags ?? [],
            'tag_options' => CaseRating::TAGS,
        ];
    }
}
