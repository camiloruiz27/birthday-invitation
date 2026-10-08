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
 *
 * Si el gateway no puede dar una respuesta real lanza InterrogationUnavailable:
 * devolver un texto de relleno haria pasar un servicio no prestado por una
 * respuesta, y el jugador pagaria por ella.
 */
class GatewayInterrogationProvider implements InterrogationProvider
{
    public function ask(InterrogationSession $session, string $question): string
    {
        $case = $session->game->caseDefinition();
        $suspect = $case->suspect($session->suspect_slug);

        if (! $suspect) {
            Log::warning('immersion_interrogation_unknown_suspect', ['session_id' => $session->id]);

            throw new InterrogationUnavailable('Sospechoso desconocido para este caso.');
        }

        $baseUrl = rtrim((string) config('immersion.ai.base_url'), '/');
        $projectId = (string) config('immersion.ai.project_id');
        $apiKey = (string) config('immersion.ai.api_key');

        if ($baseUrl === '' || $apiKey === '' || str_starts_with($apiKey, 'CHANGE_ME')) {
            Log::warning('immersion_interrogation_not_configured', ['session_id' => $session->id]);

            throw new InterrogationUnavailable('El gateway de IA no esta configurado.');
        }

        // La pregunta actual viaja aparte en `question`: el historial son solo
        // los turnos ya cerrados de esta sesion.
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
                    'case_code' => $case->code(),
                    'authority' => $case->authority(),
                    'victim_name' => $case->victim()['name'],
                    'evidence_delivered' => $session->game->deliveredEvidenceCodes(),
                ]);
        } catch (\Throwable $exception) {
            Log::warning('immersion_interrogation_exception', [
                'session_id' => $session->id,
                'message' => $exception->getMessage(),
            ]);

            throw new InterrogationUnavailable('No se pudo contactar al gateway de IA.', 0, $exception);
        }

        if (! $response->successful()) {
            Log::warning('immersion_interrogation_failed_response', [
                'session_id' => $session->id,
                'status' => $response->status(),
                'code' => $response->json('code'),
            ]);

            throw new InterrogationUnavailable("El gateway respondio {$response->status()}.");
        }

        $reply = trim((string) ($response->json('reply') ?? ''));

        if ($reply === '') {
            Log::warning('immersion_interrogation_empty_reply', ['session_id' => $session->id]);

            throw new InterrogationUnavailable('El gateway devolvio una respuesta vacia.');
        }

        return $reply;
    }
}
