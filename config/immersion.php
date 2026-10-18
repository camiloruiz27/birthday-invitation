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
         | How many failed turns in a row a suspect covers for with the in-character
         | deflection (free, and the question is given back) before the player is
         | told the AI has a problem. One hiccup should not break the scene; a
         | gateway that keeps failing should not be hidden behind it forever.
         */
        'evasions_before_error' => (int) env('IMMERSION_AI_EVASIONS_BEFORE_ERROR', 2),

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

        // Grading reads every player's accusation in one call, so it can take
        // longer than an epilogue. Must stay under GradeAccusations::$timeout.
        'grading_timeout' => (int) env('IMMERSION_AI_GRADING_TIMEOUT', 180),
        'grading_enabled' => (bool) env('IMMERSION_AI_GRADING', true),

        // How many questions to suspects may be waiting on the gateway at once
        // (see AiGate). Each holds one of the hosting plan's 60 PHP workers for
        // as long as the gateway takes; past this a question is turned away,
        // unspent, with a "try again in a few seconds". 0 = no limit.
        'max_concurrent' => (int) env('IMMERSION_AI_MAX_CONCURRENT', 20),

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
    | Default question budget per person, and the ceiling of the ordinary
    | questions a case may give anyone (3 to this value; see Cases/README.md).
    | Each session stores the limit it was created with
    | (immersion_interrogation_sessions.max_questions), so changing this value
    | never alters games already in progress. A case overrides it per case
    | (`limits.interrogation_questions`) and per person (`interrogation.stages`).
    |
    */

    'interrogation' => [
        'max_questions' => (int) env('IMMERSION_MAX_QUESTIONS', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Players per game
    |--------------------------------------------------------------------------
    |
    | `max` is the table's ceiling, the Game Master included when they play
    | (automatic mode). It is enforced on the server and mirrored by the form's
    | "add player" button. A case's own min_players/max_players are only a
    | recommendation shown to the buyer, never a limit. `initial_rows` is how
    | many empty slots the new-game form opens with.
    |
    */

    'players' => [
        'max' => 12,
        'initial_rows' => 3,
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
            // Grading the table's accusations once the ending is revealed: one
            // model call per game, whatever the ending. Free by default; set a
            // price here and it is reserved with the rest when the game starts.
            'grading' => (int) env('IMMERSION_COST_GRADING', 0),

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
    | Outgoing mail
    |--------------------------------------------------------------------------
    |
    | Every envelope of a case is one email per player, and a table of three
    | playing a case of ten envelopes is thirty-odd messages before counting
    | access links, epilogues and the account mail (verification, receipts) that
    | leaves through the same mailbox. Hostinger limits a mailbox per rolling 24
    | hours (100 on the free one, 1,000 on Business Starter, 3,000 on Standard
    | and Premium), so the engine counts what it sends and holds back instead of
    | discovering the limit when a player's envelope bounces.
    |
    | Set daily_limit to what the mailbox actually allows (hPanel > Emails >
    | the mailbox > view limits). 0 turns the guard off.
    |
    */

    'mail' => [
        // Which game emails leave (see GameMailPolicy). The game delivers
        // everything inside the platform, so by default only the "accusations
        // are open" envelope is emailed:
        //   unlock  only that one (default)
        //   all     every envelope, as the game used to
        //   off     none
        'timeline' => env('IMMERSION_MAIL_TIMELINE', 'unlock'),

        // The epilogue (the message from the person a player accused) is
        // written and shown on the Solution page either way; this only decides
        // whether it is also emailed.
        'epilogue' => (bool) env('IMMERSION_MAIL_EPILOGUE', false),

        'daily_limit' => (int) env('IMMERSION_MAIL_DAILY_LIMIT', 100),

        // Stay a little under the ceiling for what can wait: access links and
        // epilogues go out only while less than this share of the day is used,
        // so envelopes (which a live table is waiting on) always have room.
        'reserve_share' => (float) env('IMMERSION_MAIL_RESERVE_SHARE', 0.8),

        // A message a minute-by-minute limit would bounce is spread out
        // instead. 0 turns the limiter off.
        'per_minute' => (int) env('IMMERSION_MAIL_PER_MINUTE', 10),

        // An audio larger than this is linked from the inbox instead of
        // attached: a multi-megabyte attachment per player per envelope is what
        // makes a mailbox slow, and the recording is one click away anyway.
        'attach_audio_max_kb' => (int) env('IMMERSION_MAIL_ATTACH_AUDIO_MAX_KB', 3000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduler tick
    |--------------------------------------------------------------------------
    |
    | With nothing running and nothing queued, the once-a-minute tick leaves
    | without opening a database connection (see TickActivity), and only looks
    | every few minutes as a safety net. Off means it runs every minute, always.
    |
    */

    'snapshots' => [
        // What a table of players reads (the envelopes already sent, who has
        // claimed which suspect) is kept ready in the cache and rebuilt by
        // whoever changes it, so reading it opens no database connection (see
        // GameSnapshot). Off means every page reads the database, as before.
        'enabled' => (bool) env('IMMERSION_SNAPSHOTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Early release
    |--------------------------------------------------------------------------
    |
    | After an envelope arrives each player can say they read everything; when
    | every player who has joined says so, the next envelope is sent without
    | waiting for its minute and the game clock moves up by what was saved (see
    | EarlyRelease). For tables with fast readers. false removes the button.
    |
    */
    'early_release' => [
        'enabled' => (bool) env('IMMERSION_EARLY_RELEASE', true),
    ],

    'tick' => [
        'idle_skip' => (bool) env('IMMERSION_TICK_IDLE_SKIP', true),

        // One line with the moment (epoch seconds) the cron should next start
        // PHP, read by cron-scheduler.sh before it boots anything. null turns it
        // off (the script then runs the tick every minute, as before).
        'next_run_file' => env('IMMERSION_TICK_NEXT_RUN_FILE', storage_path('framework/next-run')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queues
    |--------------------------------------------------------------------------
    |
    | One worker drains every queue in this order each minute, so a slow job
    | (text-to-speech, grading, an AI epilogue: minutes) never sits in front of
    | the mail a table is waiting for.
    |
    */

    'queues' => [
        'mail' => 'emails',
        'slow' => 'slow',
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

        // "Ya lei toda la informacion", per player: attempts,minutes.
        'envelope_ready' => env('IMMERSION_THROTTLE_READY', '10,1'),

        // The "has anything changed" polling, per IP. A table of twelve polling every
        // twelve seconds is ~60 a minute; raise it for a load test, where every player
        // comes from one address.
        'poll' => env('IMMERSION_THROTTLE_POLL', '240,1'),
    ],

];
