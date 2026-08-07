<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CoupleAiService
{
    public function suggest(array $payload): array
    {
        $baseUrl = rtrim((string) env('COUPLE_AI_SERVICE_URL', ''), '/');
        $projectId = (string) env('COUPLE_AI_PROJECT_ID', 'couple');
        $apiKey = (string) env('COUPLE_AI_INTERNAL_API_KEY', '');

        if ($baseUrl === '' || $apiKey === '' || str_starts_with($apiKey, 'CHANGE_ME')) {
            return $this->fallback($payload, 'Configura COUPLE_AI_SERVICE_URL y COUPLE_AI_INTERNAL_API_KEY para activar la IA.');
        }

        try {
            $response = Http::timeout(35)
                ->withHeaders([
                    'x-project-id' => $projectId,
                    'x-internal-api-key' => $apiKey,
                ])
                ->post("{$baseUrl}/api/projects/{$projectId}/activity-guide", $payload);

            if (! $response->successful()) {
                return $this->fallback($payload, 'La IA no respondio correctamente; se uso una guia local.');
            }

            $data = $response->json();

            return [
                'source' => 'ai',
                'opening_phrase' => (string) ($data['opening_phrase'] ?? ''),
                'connection_question' => (string) ($data['connection_question'] ?? ''),
                'short_game' => (string) ($data['short_game'] ?? ''),
                'main_activity' => (string) ($data['main_activity'] ?? ''),
                'closing_or_pause' => (string) ($data['closing_or_pause'] ?? ''),
                'privacy_level' => (string) ($data['privacy_level'] ?? 'privado'),
                'safety_note' => (string) ($data['safety_note'] ?? 'Pueden pasar, pausar o bajar intensidad cuando quieran.'),
            ];
        } catch (\Throwable $exception) {
            return $this->fallback($payload, 'La IA no esta disponible ahora; se uso una guia local.');
        }
    }

    private function fallback(array $payload, string $note): array
    {
        $level = (int) ($payload['level'] ?? 2);
        $location = (string) ($payload['location_label'] ?? 'este lugar');
        $mood = (string) ($payload['mood_label'] ?? 'conectados');

        if ($level >= 4) {
            return [
                'source' => 'fallback',
                'opening_phrase' => 'Antes de subir intensidad, digamos cada uno que si queremos, que tal vez y que no hoy.',
                'connection_question' => 'Que necesitas para sentirte deseado, cuidado y libre de pausar?',
                'short_game' => 'Juego de si, tal vez, no: cada uno propone tres ideas y el otro las clasifica sin justificar de inmediato.',
                'main_activity' => "Busquen un espacio privado. Empiecen con respiracion, mirada y acuerdos claros antes de cualquier actividad sexual.",
                'closing_or_pause' => 'Cierren con agua, abrazo o una frase de cuidado. Si algo no fluye, bajen a nivel 2.',
                'privacy_level' => 'privado',
                'safety_note' => $note,
            ];
        }

        return [
            'source' => 'fallback',
            'opening_phrase' => "Estamos en {$location}; hagamos algo pequeño para volver a nosotros.",
            'connection_question' => "Que te gustaria recibir de mi ahora que estamos {$mood}?",
            'short_game' => 'Tres turnos: una gratitud, un cumplido y una invitacion suave para el resto del dia.',
            'main_activity' => 'Elijan una carta nivel 2 o 3, haganla sin celular y terminen preguntando que les gusto.',
            'closing_or_pause' => 'Cierren con una frase corta: esto me acerco a ti porque...',
            'privacy_level' => 'publico_discreto',
            'safety_note' => $note,
        ];
    }
}
