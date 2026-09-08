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
    | Games
    |--------------------------------------------------------------------------
    |
    | How many games one account may keep OF EACH CASE. Someone who owns four
    | cases can have six games of each; filling up on one says nothing about
    | the others.
    |
    | Every game counts, in any state — a finished one still occupies a slot.
    | Freeing a slot means deleting a game of that same case, which is why
    | deletion exists at all.
    |
    | Raising this is safe. Lowering it never deletes anything: accounts over
    | the new limit simply cannot create until they come back under it.
    |
    */

    'games' => [
        'max_per_case' => (int) env('IMMERSION_MAX_GAMES_PER_CASE', 6),
    ],

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
    | AI credits
    |--------------------------------------------------------------------------
    |
    | Everything that costs a real model call is metered in credits, held in a
    | per-account wallet. The engine meters itself here because only the engine
    | knows which mechanics call a model; selling top-ups is commerce and lives
    | in config/platform.php.
    |
    | The whole ceiling a game could possibly consume is RESERVED when the Game
    | Master starts it, and whatever went unused is released when the case is
    | closed. That is why the ending is chosen at creation: a game cannot start
    | owing capacity it may not have later, and the reveal is the one moment
    | that must never fail for lack of balance.
    |
    | With credits off, nothing is reserved, nothing is charged and no wallet is
    | touched — the switch exists so a private deployment can run the engine
    | without an economy at all.
    |
    */

    'credits' => [
        'enabled' => (bool) env('IMMERSION_CREDITS', true),

        'costs' => [
            // One question to one suspect: one real model call.
            'question' => (int) env('IMMERSION_COST_QUESTION', 1),

            // Per ending type. The classic reveal is authored content with no
            // model involved, so it is free and always affordable.
            'ending' => [
                'classic' => 0,
                'epilogue' => (int) env('IMMERSION_COST_EPILOGUE', 10),
                'confession_audio' => (int) env('IMMERSION_COST_CONFESSION', 15),
            ],
        ],

        /*
         | Does acquiring a case come with the credits to play it?
         |
         | How MANY is not configured here on purpose: it is derived from the
         | case, as every question its roster allows plus its most expensive
         | ending (Support\GameCost::maxForCase). For steve-jacobs that is
         | 9 x 5 questions + 15 for the confession audio = 60.
         |
         | A fixed figure would be wrong the moment a case ships with twelve
         | suspects, and "playable to the limit" is the promise — not "sixty".
         |
         | Granted once per entitlement, on creation only.
         */
        'included_with_case' => (bool) env('IMMERSION_CREDITS_WITH_CASE', true),

        /*
         | A running game holds its reservation until the case is closed or
         | paused. A table that does neither would freeze that capacity forever,
         | so holds on games with no activity for this many hours are released
         | by the scheduler. The game itself is left alone and can be re-armed
         | from its console.
         |
         | A week by default, because playing a case across two weekends is a
         | normal way to play and it should need no intervention. Pausing gives
         | the credits back immediately, so this only ever catches games that
         | were left running.
         */
        'stale_hold_hours' => (int) env('IMMERSION_STALE_HOLD_HOURS', 168),
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
