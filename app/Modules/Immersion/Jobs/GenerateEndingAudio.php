<?php

namespace App\Modules\Immersion\Jobs;

use App\Modules\Immersion\Ai\Contracts\SpeechProvider;
use App\Modules\Immersion\Models\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Reads the culprit's authored confession aloud, for the Game Master to play.
 *
 * No new gateway endpoint: the script is written in the case manifest, so this
 * is plain text-to-speech. The model performs the ending, it does not compose
 * it — the same rule as everywhere else.
 *
 * It is not mailed to anyone. The Game Master gets it in the console and plays
 * it at the table, which is the whole point of the premium ending: everyone
 * hears it together, once.
 */
class GenerateEndingAudio implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 30;

    /** Comfortably longer than the gateway's own TTS timeout. */
    public int $timeout = 180;

    public function __construct(public int $gameId)
    {
    }

    public function uniqueId(): string
    {
        return (string) $this->gameId;
    }

    public function handle(SpeechProvider $speech): void
    {
        $game = Game::find($this->gameId);

        if (! $game) {
            return;
        }

        if ($game->ending_audio_path) {
            $game->update(['ending_audio_status' => Game::AUDIO_READY]);

            return;
        }

        $case = $game->caseDefinition();
        $script = $case->confessionScript();

        if (! $script) {
            $game->update(['ending_audio_status' => Game::AUDIO_FAILED]);

            return;
        }

        $path = $speech->synthesize(
            "ending-{$game->id}",
            $script,
            $case->confessionVoice()
        );

        if (! $path) {
            $game->update(['ending_audio_status' => Game::AUDIO_FAILED]);

            return;
        }

        $game->update([
            'ending_audio_path' => $path,
            'ending_audio_status' => Game::AUDIO_READY,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Game::where('id', $this->gameId)->update(['ending_audio_status' => Game::AUDIO_FAILED]);
    }
}
