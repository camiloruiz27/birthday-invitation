<?php

namespace App\Modules\Immersion\Ai;

use App\Modules\Immersion\Ai\Contracts\ConfessionProvider;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Asks the gateway to weave the table's questions into the authored confession.
 *
 * Only the questions put to the CULPRIT are sent. Someone else's interrogation
 * is another player's line of investigation, and the culprit has no way of
 * knowing what was asked in a room they were not in.
 *
 * The authored script is always the fallback: a confession that failed to be
 * personalised is still a complete confession.
 */
class GatewayConfessionProvider implements ConfessionProvider
{
    /** Enough to choose from without burying the script. */
    private const MAX_QUESTIONS = 12;

    public function script(Game $game): ?string
    {
        $case = $game->caseDefinition();
        $authored = $case->confessionScript();
        $culprit = $case->culpritSlug();

        if (! $authored || ! $culprit) {
            return $authored;
        }

        $questions = $this->questionsPutToTheCulprit($game, $culprit);

        // Nothing to weave in: no call, no cost, same ending.
        if ($questions === []) {
            return $authored;
        }

        $baseUrl = rtrim((string) config('immersion.ai.base_url'), '/');
        $projectId = (string) config('immersion.ai.project_id');
        $apiKey = (string) config('immersion.ai.api_key');

        if ($baseUrl === '' || $apiKey === '' || str_starts_with($apiKey, 'CHANGE_ME')) {
            Log::warning('immersion_confession_not_configured', ['game_id' => $game->id]);

            return $authored;
        }

        try {
            $response = Http::timeout((int) config('immersion.ai.epilogue_timeout', 240))
                ->withHeaders([
                    'x-project-id' => $projectId,
                    'x-internal-api-key' => $apiKey,
                ])
                ->post("{$baseUrl}/api/projects/{$projectId}/confession", [
                    'suspect_name' => $case->suspect($culprit)['name'],
                    'case_code' => $case->code(),
                    'victim_name' => $case->victim()['name'],
                    'script' => $authored,
                    'questions' => $questions,
                ]);

            if (! $response->successful()) {
                Log::warning('immersion_confession_failed_response', [
                    'game_id' => $game->id,
                    'status' => $response->status(),
                ]);

                return $authored;
            }

            $script = $this->cleanForSpeech((string) ($response->json('script') ?? ''));

            return $script !== '' ? $script : $authored;
        } catch (\Throwable $exception) {
            Log::warning('immersion_confession_exception', [
                'game_id' => $game->id,
                'message' => $exception->getMessage(),
            ]);

            return $authored;
        }
    }

    /**
     * Removes characters that are invisible on screen but are not silence to a
     * speech synthesiser.
     *
     * The model intermittently syllabifies its whole answer with soft hyphens —
     * "de{U+00AD}tec{U+00AD}ti{U+00AD}ve" looks like one word and is read as
     * something else entirely. Seen in two runs out of four.
     *
     * The gateway strips these too. Doing it again here is deliberate: this
     * text is stored on the game and read out by the Game Master when the
     * synthesis fails, so it must be clean even when talking to a gateway that
     * has not been redeployed yet.
     */
    private function cleanForSpeech(string $text): string
    {
        // Soft hyphen, zero-width space / non-joiner / joiner, BOM.
        $text = preg_replace('/[\x{00AD}\x{200B}\x{200C}\x{200D}\x{FEFF}]/u', '', $text);

        // Non-breaking and narrow no-break spaces become ordinary spaces.
        $text = preg_replace('/[\x{00A0}\x{202F}]/u', ' ', (string) $text);

        return trim((string) preg_replace('/[ \t]+/', ' ', (string) $text));
    }

    /**
     * @return string[]
     */
    private function questionsPutToTheCulprit(Game $game, string $culpritSlug): array
    {
        $session = InterrogationSession::where('game_id', $game->id)
            ->where('suspect_slug', $culpritSlug)
            ->first();

        if (! $session) {
            return [];
        }

        return $session->messages()
            ->where('role', 'player')
            ->orderBy('id')
            ->limit(self::MAX_QUESTIONS)
            ->pluck('content')
            ->map(fn (?string $q) => trim((string) $q))
            ->filter()
            ->values()
            ->all();
    }
}
