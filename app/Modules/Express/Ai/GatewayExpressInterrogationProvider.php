<?php

namespace App\Modules\Express\Ai;

use App\Modules\Express\Support\ExpressCase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Asks the "mystery-case" project of the lawxora-ai-service gateway to play a
 * suspect (POST /api/projects/mystery-case/interrogate-express). The gateway
 * and its credentials are the ones the paid game already uses
 * (config immersion.ai.*).
 *
 * What is sent is ONE suspect's persona sheet, what they have already
 * declared, what the table has in hand and the last turns of this
 * conversation — never the case's solution and never another suspect's sheet,
 * so the model cannot blurt what it was never told.
 *
 * No answer means an exception; the room then falls back to the author's own
 * text. A filler reply would pass off a service not rendered as an answer.
 */
final class GatewayExpressInterrogationProvider implements ExpressInterrogationProvider
{
    public function available(): bool
    {
        $apiKey = (string) config('immersion.ai.api_key');

        return (bool) config('express.ai.enabled')
            && (string) config('immersion.ai.base_url') !== ''
            && $apiKey !== ''
            && ! str_starts_with($apiKey, 'CHANGE_ME');
    }

    public function ask(ExpressCase $case, array $suspect, array $history, string $question, ?string $topicId, array $evidence): string
    {
        $baseUrl = rtrim((string) config('immersion.ai.base_url'), '/');
        $projectId = (string) config('immersion.ai.project_id');
        $apiKey = (string) config('immersion.ai.api_key');

        try {
            $response = Http::timeout((int) config('express.ai.timeout'))
                ->withHeaders([
                    'x-project-id' => $projectId,
                    'x-internal-api-key' => $apiKey,
                ])
                ->post("{$baseUrl}/api/projects/{$projectId}/interrogate-express", [
                    'suspect_name' => $suspect['name'],
                    'suspect_role' => $suspect['role'],
                    'case_title' => $case->title(),
                    'scenario' => $case->scenario(),
                    'incident' => $case->incident(),
                    'persona' => $case->persona($suspect['id']),
                    'canonical_topics' => array_map(fn (array $topic) => [
                        'topic' => $topic['id'],
                        'answer' => $topic['answer'],
                    ], $suspect['topics']),
                    'topic_hint' => $topicId,
                    'evidence_delivered' => $evidence,
                    'history' => $history,
                    'question' => $question,
                ]);
        } catch (Throwable $exception) {
            Log::warning('express_interrogation_exception', ['message' => $exception->getMessage()]);

            throw new ExpressAiUnavailable('No se pudo contactar al gateway de IA.', 0, $exception);
        }

        if (! $response->successful()) {
            Log::warning('express_interrogation_failed_response', [
                'status' => $response->status(),
                'code' => $response->json('code'),
            ]);

            throw new ExpressAiUnavailable("El gateway respondió {$response->status()}.");
        }

        $reply = trim((string) ($response->json('reply') ?? ''));

        if ($reply === '') {
            Log::warning('express_interrogation_empty_reply');

            throw new ExpressAiUnavailable('El gateway devolvió una respuesta vacía.');
        }

        return $reply;
    }
}
