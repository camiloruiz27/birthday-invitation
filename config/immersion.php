<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default mystery case
    |--------------------------------------------------------------------------
    |
    | Slug of the case assigned to new games until the catalog lets the owner
    | pick one. Must match a directory under app/Modules/Immersion/Cases/ that
    | contains a case.php manifest.
    |
    */

    'default_case' => env('IMMERSION_DEFAULT_CASE', 'steve-jacobs'),

    /*
    |--------------------------------------------------------------------------
    | AI gateway
    |--------------------------------------------------------------------------
    |
    | The interrogation and text-to-speech features call the "mystery-case"
    | project of the lawxora-ai-service gateway. When base_url or api_key are
    | missing, both features degrade to a no-AI fallback instead of failing
    | the game.
    |
    */

    'ai' => [
        'base_url' => env('IMMERSION_AI_SERVICE_URL', ''),
        'project_id' => env('IMMERSION_AI_PROJECT_ID', 'mystery-case'),
        'api_key' => env('IMMERSION_AI_INTERNAL_API_KEY', ''),
        'interrogation_timeout' => (int) env('IMMERSION_AI_INTERROGATION_TIMEOUT', 35),
        'tts_timeout' => (int) env('IMMERSION_AI_TTS_TIMEOUT', 60),

        // Each capability can be switched off on its own. Off means the null
        // provider: suspects deflect in character, and voice-note emails go
        // out without a recording. The case stays playable either way.
        'interrogation_enabled' => (bool) env('IMMERSION_AI_INTERROGATION', true),
        'speech_enabled' => (bool) env('IMMERSION_AI_SPEECH', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Interrogation
    |--------------------------------------------------------------------------
    |
    | Default question budget per suspect. Each session stores the limit it
    | was created with (immersion_interrogation_sessions.max_questions), so
    | changing this value never alters games already in progress. A future
    | case definition can override it per case.
    |
    */

    'interrogation' => [
        'max_questions' => (int) env('IMMERSION_MAX_QUESTIONS', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate limits
    |--------------------------------------------------------------------------
    |
    | attempts,minutes pairs for Laravel's throttle middleware. Asking a
    | suspect costs a real AI call, so it is capped per token regardless of
    | the per-session question budget. Account rate limits live in
    | config/platform.php.
    |
    */

    'rate_limits' => [
        'interrogation_ask' => env('IMMERSION_THROTTLE_ASK', '20,1'),
    ],

];
