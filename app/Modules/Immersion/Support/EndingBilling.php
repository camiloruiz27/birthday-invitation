<?php

namespace App\Modules\Immersion\Support;

use App\Modules\Immersion\Models\Game;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Charges a game's paid ending, once, after it has actually been delivered.
 *
 * The price is per game, but delivery is not: the epilogue is one job per
 * player and the confession recording a job with retries. Whoever succeeds
 * first calls this, and the conditional UPDATE decides who gets to charge —
 * the same pattern RevealEnding uses to claim the reveal — so a table of six
 * epilogues, or a retried job, never bills the ending twice.
 *
 * Never throws. The ending has already reached the table by the time this
 * runs, and a billing hiccup must not turn a delivered email into a failed job
 * that gets retried and resent.
 */
class EndingBilling
{
    public function __construct(
        private AiCredits $credits,
        private GameCost $cost,
    ) {
    }

    public function chargeOnce(Game $game): void
    {
        try {
            $price = $this->cost->endingCost((string) $game->ending_type);

            DB::transaction(function () use ($game, $price) {
                $claimed = Game::query()
                    ->whereKey($game->getKey())
                    ->whereNull('ending_charged_at')
                    ->update(['ending_charged_at' => Carbon::now()]);

                if ($claimed === 0) {
                    return;
                }

                // The ending was already handed over. If the hold was released
                // in the meantime it goes out unbilled: the table is not asked
                // to pay for something the system failed to collect in time.
                if (! $this->credits->spend($game, $price, "Final: {$game->ending_type}")) {
                    Log::warning('immersion_ending_unbilled', [
                        'game_id' => $game->id,
                        'ending_type' => $game->ending_type,
                        'credits' => $price,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            // The transaction rolled the marker back, so a later success can
            // still charge.
            Log::error('immersion_ending_charge_failed', [
                'game_id' => $game->id,
                'ending_type' => $game->ending_type,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
