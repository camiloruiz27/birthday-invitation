<?php

namespace App\Modules\Immersion\Services;

use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Support\CaseFileReader;
use App\Modules\Immersion\Support\CaseSuspects;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Llama al proyecto "mystery-case" del gateway lawxora-ai-service para el
 * turno de interrogatorio. Solo envia el testimonio de ESE sospechoso (nunca
 * la solucion del caso ni el testimonio de otros) para que el modelo no
 * pueda inventar ni mezclar hechos de otros personajes.
 */
class SuspectInterrogationService
{
    private const FALLBACK_REPLY = 'Ya dije todo lo que se sobre eso. No tengo nada mas que agregar a lo que ya declare.';

    public function ask(InterrogationSession $session, string $question): string
    {
        $suspect = CaseSuspects::find($session->suspect_slug);

        if (! $suspect) {
            return self::FALLBACK_REPLY;
        }

        $baseUrl = rtrim((string) env('IMMERSION_AI_SERVICE_URL', ''), '/');
        $projectId = (string) env('IMMERSION_AI_PROJECT_ID', 'mystery-case');
        $apiKey = (string) env('IMMERSION_AI_INTERNAL_API_KEY', '');

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
            $response = Http::timeout(35)
                ->withHeaders([
                    'x-project-id' => $projectId,
                    'x-internal-api-key' => $apiKey,
                ])
                ->post("{$baseUrl}/api/projects/{$projectId}/interrogate", [
                    'suspect_name' => $suspect['name'],
                    'suspect_role' => $suspect['role'],
                    'testimony' => CaseFileReader::raw($suspect['file']),
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
