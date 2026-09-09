<?php

namespace App\Modules\Immersion\Ai;

use App\Modules\Immersion\Ai\Contracts\InterrogationProvider;
use App\Modules\Immersion\Models\InterrogationSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Llama al proyecto "mystery-case" del gateway lawxora-ai-service para el
 * turno de interrogatorio. Solo envia el testimonio de ESE sospechoso (nunca
 * la solucion del caso ni el testimonio de otros) para que el modelo no
 * pueda inventar ni mezclar hechos de otros personajes.
 */
class GatewayInterrogationProvider implements InterrogationProvider
{
    // Same words the no-AI provider uses, so a failed turn is indistinguishable
    // from a suspect who simply refuses to add anything.
    private const FALLBACK_REPLY = NullInterrogationProvider::REPLY;

    public function ask(InterrogationSession $session, string $question): string
    {
        $case = $session->game->caseDefinition();
        $suspect = $case->suspect($session->suspect_slug);

        if (! $suspect) {
            return self::FALLBACK_REPLY;
        }

        $baseUrl = rtrim((string) config('immersion.ai.base_url'), '/');
        $projectId = (string) config('immersion.ai.project_id');
        $apiKey = (string) config('immersion.ai.api_key');

        if ($baseUrl === '' || $apiKey === '' || str_starts_with($apiKey, 'CHANGE_ME')) {
            Log::warning('immersion_interrogation_not_configured', ['session_id' => $session->id]);

            return self::FALLBACK_REPLY;
        }

        $history = $session->messages()
            ->get()
            ->map(fn ($message) => [
                'role' => $message->role,
                'content' => $message->content,
            ])
            ->values()
            ->all();

        try {
            $response = Http::timeout((int) config('immersion.ai.interrogation_timeout'))
                ->withHeaders([
                    'x-project-id' => $projectId,
                    'x-internal-api-key' => $apiKey,
                ])
                ->post("{$baseUrl}/api/projects/{$projectId}/interrogate", [
                    'suspect_name' => $suspect['name'],
                    'suspect_role' => $suspect['role'],
                    'testimony' => $case->content()->raw($suspect['file']),
                    'history' => $history,
                    'question' => $question,
                ]);

            if (! $response->successful()) {
                Log::warning('immersion_interrogation_failed_response', [
                    'session_id' => $session->id,
                    'status' => $response->status(),
                ]);

                return self::FALLBACK_REPLY;
            }

            $reply = (string) ($response->json('reply') ?? '');

            return $reply !== '' ? $reply : self::FALLBACK_REPLY;
        } catch (\Throwable $exception) {
            Log::warning('immersion_interrogation_exception', [
                'session_id' => $session->id,
                'message' => $exception->getMessage(),
            ]);

            return self::FALLBACK_REPLY;
        }
    }
}
