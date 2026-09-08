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
        /*
         | A player is watching the interrogation happen, so it stays short: a
         | suspect who takes a minute to answer has already broken the scene.
         */
        'interrogation_timeout' => (int) env('IMMERSION_AI_INTERROGATION_TIMEOUT', 35),

        /*
         | Nobody is watching these two — both run on the queue — so they are
         | allowed to be slow rather than to fail.
         |
         | Text-to-speech is by far the slowest thing here: 105 seconds of
         | confession took about 150 to synthesise, and the confession has no
         | length limit by design. Retries on 503 sit inside that window too.
         | Kept under GenerateEndingAudio::$timeout.
         */
        'tts_timeout' => (int) env('IMMERSION_AI_TTS_TIMEOUT', 300),

        /*
         | The epilogue is usually 2-3 seconds, but the chat model degrades
         | badly under load: 97 seconds has been measured for a generation that
         | succeeded, against a 90 second limit that had already hung up. The
         | gateway did the work, charged for it, and the answer was thrown away.
         |
         | Nobody is waiting on this — it runs on the queue — so it is allowed
         | to be slow rather than to waste a generation. Must stay under
         | SendEpilogue::$timeout.
         */
        'epilogue_timeout' => (int) env('IMMERSION_AI_EPILOGUE_TIMEOUT', 240),

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

            /*
             | Per ending type, per game — never per player. A table of eight
             | pays what a table of three pays, so inviting one more person is
             | never a cost decision.
             |
             | The epilogue is the premium one because of what it DELIVERS:
             | every player gets something written for them, against a single
             | recording the table hears once. It is not the expensive one to
             | produce — measured against Google's prices a confession costs
             | roughly 4.6x an epilogue for six players, because text is cheap
             | and two minutes of synthesised speech is not. At about 220 COP
             | for the most expensive possible game, cost is not what these
             | numbers are for.
             |
             | The classic reveal is authored content with no model involved,
             | so it is free and always affordable — which is what keeps a case
             | playable forever once the included credits are gone.
             */
            'ending' => [
                'classic' => 0,
                'epilogue' => (int) env('IMMERSION_COST_EPILOGUE', 40),
                'confession_audio' => (int) env('IMMERSION_COST_CONFESSION', 25),
            ],
        ],

        /*
         | Does acquiring a case come with credits to play it?
         |
         | How MANY is derived from the case, never written here as a figure
         | that would stop matching the next one (Support\GameCost::maxForCase):
         |
         |   (preguntas del elenco x partidas_incluidas) + su final mas caro
         |
         | For steve-jacobs: (9 x 5) + 40 = 85. One full game, and the buyer
         | picks which ending it gets — the amount covers the pricier one, so
         | the choice is real rather than nominal.
         |
         | Afterwards the case stays playable forever in its classic form,
         | which costs nothing. Anything with AI needs a top-up.
         |
         | Granted once per entitlement, on creation only.
         */
        'included_with_case' => (bool) env('IMMERSION_CREDITS_WITH_CASE', true),

        /*
         | How many full games the included credits are sized for.
         */
        'included_games' => (int) env('IMMERSION_INCLUDED_GAMES', 1),

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
