<?php

namespace App\Modules\Immersion\Ai;

use App\Modules\Immersion\Ai\Contracts\EpilogueProvider;
use App\Modules\Immersion\Models\Accusation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Asks the "mystery-case" gateway for one player's epilogue.
 *
 * What gets sent is decided entirely here, from the authored manifest, and the
 * two branches send deliberately different material:
 *
 * - Accused the culprit → the method, the motive and the key evidence.
 * - Accused an innocent → ONLY that suspect's authored exoneration, plus the
 *   culprit's name. An innocent character has no business knowing how the
 *   crime was committed, and sending them the solution would let the model
 *   leak it into a message the player reads before anyone explains it.
 *
 * `content/solucion.md` is never sent in either case.
 */
class GatewayEpilogueProvider implements EpilogueProvider
{
    public function write(Accusation $accusation): ?string
    {
        $game = $accusation->game;
        $case = $game->caseDefinition();

        $suspect = $accusation->suspect_slug ? $case->suspect($accusation->suspect_slug) : null;
        $culpritSlug = $case->culpritSlug();

        // Accusations from before suspect slugs existed cannot be answered:
        // there is no way to know who is supposed to be writing.
        if (! $suspect || ! $culpritSlug) {
            return null;
        }

        $baseUrl = rtrim((string) config('immersion.ai.base_url'), '/');
        $projectId = (string) config('immersion.ai.project_id');
        $apiKey = (string) config('immersion.ai.api_key');

        if ($baseUrl === '' || $apiKey === '' || str_starts_with($apiKey, 'CHANGE_ME')) {
            Log::warning('immersion_epilogue_not_configured', ['accusation_id' => $accusation->id]);

            return null;
        }

        $correct = $accusation->suspect_slug === $culpritSlug;
        $solution = $case->solution();

        // A culprit nobody can question has no voice to write in. When the case
        // names someone who can, THEY tell the winners what happened, from
        // outside: it is the speaker's testimony the prompt gets, not the
        // culprit's, and the culprit is just a name in the story.
        $narrator = null;

        if ($correct && ! $case->isInterrogable($accusation->suspect_slug)) {
            $narratorSlug = $case->epilogueSpeakerSlug();
            $narrator = $narratorSlug ? $case->suspect($narratorSlug) : null;
        }

        $speaker = $narrator ?? $suspect;

        $payload = [
            'suspect_name' => $speaker['name'],
            'suspect_role' => $speaker['role'],

            // Where the character's voice comes from. The same testimony the
            // interrogation already sends, so this exposes nothing new — and
            // without it the prompt asks the model to match a temperament it
            // has never been shown.
            'testimony' => $case->content()->raw($speaker['file']),

            // The case identifies itself: nothing about one case may be baked
            // into a prompt the whole platform shares.
            'case_code' => $case->code(),
            'authority' => $case->authority(),
            'victim_name' => $case->victim()['name'],
            'incident' => $case->incident(),

            'player_name' => $accusation->player->name,
            'correct' => $correct,
            'accusation_weapon' => $accusation->weapon,
            'accusation_motive' => $accusation->motive,
        ];

        if ($narrator) {
            $payload['narrator'] = true;
            $payload['culprit_name'] = $suspect['name'];
        }

        if ($correct) {
            $payload += [
                'method' => $solution['method'],
                'motive' => $solution['motive'],
                'evidence' => $solution['key_evidence'],
            ];
        } else {
            $payload += [
                'exoneration' => $case->exonerationFor($accusation->suspect_slug),
                'culprit_name' => $case->suspect($culpritSlug)['name'],
            ];
        }

        try {
            $response = Http::timeout((int) config('immersion.ai.epilogue_timeout', 90))
                ->withHeaders([
                    'x-project-id' => $projectId,
                    'x-internal-api-key' => $apiKey,
                ])
                ->post("{$baseUrl}/api/projects/{$projectId}/epilogue", $payload);

            if (! $response->successful()) {
                Log::warning('immersion_epilogue_failed_response', [
                    'accusation_id' => $accusation->id,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $message = trim((string) ($response->json('message') ?? ''));

            return $message !== '' ? $message : null;
        } catch (\Throwable $exception) {
            Log::warning('immersion_epilogue_exception', [
                'accusation_id' => $accusation->id,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
