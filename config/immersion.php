<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Game Master access
    |--------------------------------------------------------------------------
    |
    | Shared password for the Game Master panel. Read through config (not
    | env() at runtime) so that `php artisan config:cache` does not silently
    | turn it into null and lock the Game Master out in production.
    |
    */

    'game_master_password' => env('IMMERSION_GM_PASSWORD', ''),

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
    | attempts,minutes pairs for Laravel's throttle middleware. The Game
    | Master password is shared and long-lived, so login must be throttled;
    | asking a suspect costs a real AI call, so it must be throttled too.
    |
    */

    'rate_limits' => [
        'game_master_login' => env('IMMERSION_THROTTLE_GM_LOGIN', '10,1'),
        'interrogation_ask' => env('IMMERSION_THROTTLE_ASK', '20,1'),
    ],

];
