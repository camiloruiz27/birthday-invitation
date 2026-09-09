<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\Player;

/**
 * Who accused whom, and who got it right.
 *
 * Pure calculation, no side effects. It only ever asks the case for
 * `culpritSlug()` — never `solution()` — so the reveal's prose is never in
 * memory here and cannot leak into a payload by accident.
 *
 * Only the culprit is scored. The weapon and the motive are free text; judging
 * them would need either AI or string matching, and string matching would mark
 * a correct answer wrong for using a synonym. They are shown so the table can
 * compare them by eye.
 */
class AccusationScoreboard
{
    /**
     * One row per player, in roster order. `correct` is null when the player
     * has not accused yet, or when the accusation predates suspect slugs.
     *
     * @return array<int, array{player_id: int, player_name: string, is_owner: bool, suspect_slug: ?string, suspect_name: ?string, weapon: ?string, motive: ?string, correct: ?bool}>
     */
    public function for(Game $game): array
    {
        $culprit = $game->caseDefinition()->culpritSlug();

        $accusations = $game->accusations()->get()->keyBy('player_id');

        return $game->players()
            ->orderBy('id')
            ->get()
            ->map(function (Player $player) use ($accusations, $culprit) {
                /** @var Accusation|null $accusation */
                $accusation = $accusations->get($player->id);

                return [
                    'player_id' => $player->id,
                    'player_name' => $player->name,
                    'is_owner' => (bool) $player->is_owner,
                    'suspect_slug' => $accusation?->suspect_slug,
                    'suspect_name' => $accusation?->suspect_name,
                    'weapon' => $accusation?->weapon,
                    'motive' => $accusation?->motive,
                    'correct' => $this->verdict($accusation, $culprit),
                ];
            })
            ->all();
    }

    public function correctCount(Game $game): int
    {
        return count(array_filter(
            $this->for($game),
            fn (array $row) => $row['correct'] === true
        ));
    }

    /**
     * Freezes each accusation's verdict onto the row itself.
     *
     * Called once, when the ending is revealed. Persisting it means a later
     * edit to the case manifest cannot silently rescore a game that is already
     * over — `Game::caseDefinition()` always reads the manifest currently on
     * disk, so a derived verdict would drift.
     */
    public function persistVerdicts(Game $game): void
    {
        $culprit = $game->caseDefinition()->culpritSlug();

        foreach ($game->accusations()->get() as $accusation) {
            $accusation->update([
                'was_correct' => $this->verdict($accusation, $culprit),
            ]);
        }
    }

    /**
     * Null means "not assessable", never "wrong": either there is no
     * accusation, or it predates suspect slugs, or the case has no culprit
     * written yet.
     */
    private function verdict(?Accusation $accusation, ?string $culpritSlug): ?bool
    {
        if (! $accusation || $accusation->suspect_slug === null || $culpritSlug === null) {
            return null;
        }

        return $accusation->suspect_slug === $culpritSlug;
    }
}
