<?php

namespace App\Modules\Immersion\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Genera (y cachea) el audio TTS de un evento de la linea de tiempo llamando
 * al proyecto "mystery-case" del gateway lawxora-ai-service, con el mismo
 * patron defensivo que App\Services\CoupleAiService: si el gateway no esta
 * configurado o falla, el correo simplemente sale sin audio adjunto en vez
 * de romper el envio de la linea de tiempo.
 */
class GeminiAudioService
{
    public function synthesize(int $eventId, string $script): ?string
    {
        $relativePath = "audio/{$eventId}.wav";

        if (Storage::disk('local')->exists($relativePath)) {
            return $relativePath;
        }

        $baseUrl = rtrim((string) config('immersion.ai.base_url'), '/');
        $projectId = (string) config('immersion.ai.project_id');
        $apiKey = (string) config('immersion.ai.api_key');

        if ($baseUrl === '' || $apiKey === '' || str_starts_with($apiKey, 'CHANGE_ME')) {
            Log::warning('immersion_tts_not_configured', ['event_id' => $eventId]);

            return null;
        }

        try {
            $response = Http::timeout((int) config('immersion.ai.tts_timeout'))
                ->withHeaders([
                    'x-project-id' => $projectId,
                    'x-internal-api-key' => $apiKey,
                ])
                ->post("{$baseUrl}/api/projects/{$projectId}/tts", [
                    'script' => $script,
                ]);

            if (! $response->successful()) {
                Log::warning('immersion_tts_failed_response', [
                    'event_id' => $eventId,
                    'status' => $response->status(),
                ]);

                return null;
            }

            Storage::disk('local')->put($relativePath, $response->body());

            return $relativePath;
        } catch (\Throwable $exception) {
            Log::warning('immersion_tts_exception', [
                'event_id' => $eventId,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }
}
