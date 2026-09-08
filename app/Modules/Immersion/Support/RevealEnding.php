<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Jobs\GenerateEndingAudio;
use App\Modules\Immersion\Jobs\SendEpilogue;
use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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

    public function __construct(
        private AccusationScoreboard $scoreboard,
        private AiCredits $credits,
        private GameCost $cost,
    ) {
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

        $this->deliverAdvancedEnding($game);

        return true;
    }

    /**
     * Kicks off whatever the chosen ending adds on top of the classic reveal.
     *
     * Runs after the timestamp is already flipped, and never undoes it. The
     * solution is published the moment the reveal is claimed; the epilogue and
     * the audio are extras that arrive minutes later, and a table whose gateway
     * is down still has a finished case rather than a hung one.
     *
     * Everything expensive is queued. Revealing happens in a browser request,
     * and generating six epilogues inline would time it out.
     */
    private function deliverAdvancedEnding(Game $game): void
    {
        if ($game->ending_type === Game::ENDING_CLASSIC) {
            return;
        }

        $case = $game->caseDefinition();

        // The case may have lost the content this ending needs since the game
        // was created. Degrading to the classic reveal is the right answer:
        // the table still gets its ending.
        if (! $case->supportsEnding((string) $game->ending_type)) {
            Log::warning('immersion_ending_unsupported_by_case', [
                'game_id' => $game->id,
                'ending_type' => $game->ending_type,
                'case_slug' => $game->case_slug,
            ]);

            return;
        }

        // The ending's cost was frozen when the game started, so this should
        // always succeed. It can still fail if the reservation was returned in
        // between — a long pause, or the stale-hold sweeper — and in that case
        // the extras are skipped rather than taken for free.
        $price = $this->cost->endingCost((string) $game->ending_type);

        if (! $this->credits->spend($game, $price, "Final: {$game->ending_type}")) {
            Log::warning('immersion_ending_not_funded', [
                'game_id' => $game->id,
                'ending_type' => $game->ending_type,
                'credits' => $price,
            ]);

            return;
        }

        match ($game->ending_type) {
            Game::ENDING_EPILOGUE => $this->queueEpilogues($game),
            Game::ENDING_CONFESSION_AUDIO => $this->queueConfessionAudio($game),
            default => null,
        };
    }

    /**
     * One job per accusation. Players who never accused get nothing, because
     * there is no one for a character to be answering.
     */
    private function queueEpilogues(Game $game): void
    {
        foreach ($game->accusations()->get() as $accusation) {
            $accusation->update(['epilogue_status' => Accusation::EPILOGUE_PENDING]);

            SendEpilogue::dispatch($accusation->id);
        }
    }

    private function queueConfessionAudio(Game $game): void
    {
        $game->update(['ending_audio_status' => Game::AUDIO_PENDING]);

        GenerateEndingAudio::dispatch($game->id);
    }
}
