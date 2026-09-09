<?php

namespace App\Modules\Immersion\Http\Controllers\GameMaster;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Jobs\GenerateEventAudio;
use App\Modules\Immersion\Models\Accusation;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\AccusationScoreboard;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Immersion\Support\GameCost;
use App\Modules\Immersion\Support\GameQuota;
use App\Modules\Immersion\Support\RevealEnding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GameMasterController extends Controller
{
    /**
     * Every action below is scoped to the signed-in Game Master: the listing
     * filters by owner, and the single-game actions authorize through
     * GamePolicy. Route model binding alone would happily hand over someone
     * else's game.
     */
    public function index(Request $request, GameQuota $quota): Response
    {
        $user = $request->user();

        return Inertia::render('GameMaster/Games', [
            // Whether "new game" is offered at all: full on one case is not
            // full on the library.
            'canCreate' => fn () => $quota->hasRoomForAny(
                $user,
                $user->library()->pluck('slug')
            ),
            'games' => fn () => $request->user()->games()
                ->withCount('players')
                ->latest()
                ->get()
                ->map(fn (Game $game) => [
                    'id' => $game->id,
                    'name' => $game->name,
                    'status' => $game->status,
                    'case_slug' => $game->case_slug,
                    'players_count' => $game->players_count,
                    'started_at' => $game->started_at,
                    'created_at' => $game->created_at,
                ])
                ->values(),
            'hasLibrary' => fn () => $user->entitlements()->active()->exists(),
        ]);
    }

    public function create(Request $request, GameQuota $quota, AiCredits $credits, GameCost $cost, CaseRegistry $cases): Response
    {
        $user = $request->user();
        $library = $user->library();
        $quotas = $quota->forCases($user, $library->pluck('slug'));

        return Inertia::render('GameMaster/CreateGame', [
            // Each case carries its own quota, so the form can react as the
            // Game Master switches between them.
            'library' => $library->map(function ($case) use ($quotas, $cases, $cost) {
                $definition = $cases->find($case->slug);

                return [
                    'slug' => $case->slug,
                    'name' => $case->name,
                    'min_players' => $case->min_players,
                    'max_players' => $case->max_players,
                    'quota' => $quotas[$case->slug],

                    // The interrogation ceiling is a property of the case, not
                    // of the table: one question budget per suspect, claimed by
                    // whoever gets there first.
                    'max_questions' => $definition ? $cost->maxQuestions($definition) : 0,

                    // Which endings this case can actually deliver. A case with
                    // no confession script must not be sold the premium ending.
                    'endings' => $definition ? $definition->supportedEndings() : [],
                ];
            })->values(),

            // Priced here rather than in the form, so the estimate the Game
            // Master reads is the same arithmetic that will charge them.
            'credits' => $credits->enabled() ? [
                'available' => $credits->walletFor($user)->available(),
                'question' => $cost->questionCost(),
                'endings' => $cost->endingPrices(),
            ] : null,
        ]);
    }

    public function store(Request $request, CaseRegistry $cases, GameQuota $quota): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'case_slug' => ['nullable', 'string', Rule::in($cases->slugs())],
            'mode' => ['nullable', Rule::in([Game::MODE_GM_LED, Game::MODE_AUTOMATIC])],

            // Chosen up front, not toggled mid-run: the advanced endings and
            // the interrogation cost AI capacity that has to be reserved
            // before the case starts. Which endings are actually on offer
            // depends on the case's content and is checked below.
            'ending_type' => ['nullable', Rule::in([
                Game::ENDING_CLASSIC,
                Game::ENDING_EPILOGUE,
                Game::ENDING_CONFESSION_AUDIO,
            ])],
            'interrogation_enabled' => ['nullable', 'boolean'],

            'players' => ['required', 'array', 'min:1'],
            'players.*.name' => ['required', 'string', 'max:255'],
            'players.*.email' => ['required', 'email'],
        ]);

        $case = $cases->get($data['case_slug'] ?? (string) config('immersion.default_case'));

        // You can only run a case you actually own. Checked on the server:
        // hiding the option in the form is not a restriction.
        if (! $request->user()->ownsCase($case->slug)) {
            throw ValidationException::withMessages([
                'case_slug' => 'No tienes acceso a este caso todavia.',
            ]);
        }

        $endingType = $data['ending_type'] ?? Game::ENDING_CLASSIC;
        $interrogation = (bool) ($data['interrogation_enabled'] ?? false);

        // An ending is a capability of the case's content, not of the build.
        // Checked on the server: the form hides the ones this case cannot do,
        // but hiding an option is not a restriction.
        if ($endingType !== Game::ENDING_CLASSIC && ! $case->supportsEnding($endingType)) {
            throw ValidationException::withMessages([
                'ending_type' => 'Este caso todavia no trae ese final.',
            ]);
        }

        // The confession now quotes what the table asked the culprit, so it
        // has nothing to work with when nobody interrogated anyone. Refused at
        // creation rather than degrading at the reveal: the Game Master can
        // still fix it here, and at the reveal they cannot.
        if ($endingType === Game::ENDING_CONFESSION_AUDIO && ! $interrogation) {
            throw ValidationException::withMessages([
                'ending_type' => 'La confesion en audio necesita el interrogatorio: el culpable menciona las preguntas que le hicieron.',
            ]);
        }

        $user = $request->user();
        $mode = $data['mode'] ?? Game::MODE_GM_LED;

        // The whole creation runs under a lock on the owner's row: counting
        // games and then inserting one is a read-modify-write, and two
        // requests arriving together would otherwise both see five games and
        // both create a sixth.
        $game = DB::transaction(function () use ($user, $data, $case, $mode, $endingType, $interrogation, $quota) {
            User::whereKey($user->id)->lockForUpdate()->first();

            // The quota is per case: being full on this one says nothing about
            // the rest of the library. Throwing rolls the transaction back, so
            // a rejected creation leaves nothing behind.
            if ($quota->isFull($user, $case->slug)) {
                throw ValidationException::withMessages([
                    'case_slug' => "Ya tienes {$quota->limit()} partidas de este caso. Borra una de este caso para crear otra.",
                ]);
            }

            $game = Game::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'case_slug' => $case->slug,
                'case_version' => $case->version(),
                'mode' => $mode,
                'ending_type' => $endingType,
                'interrogation_enabled' => $interrogation,
                'status' => 'draft',
            ]);

            // In automatic mode the owner plays too, so they need a player row
            // and an inbox of their own, like everyone else.
            if ($mode === Game::MODE_AUTOMATIC) {
                $game->players()->create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'access_token' => Str::uuid(),
                    'is_owner' => true,
                ]);
            }

            foreach ($data['players'] as $playerData) {
                $game->players()->create([
                    'name' => $playerData['name'],
                    'email' => $playerData['email'],
                    'access_token' => Str::uuid(),
                ]);
            }

            foreach ($case->timeline() as $event) {
                $game->timelineEvents()->create($event);
            }

            return $game;
        });

        // Straight into the new game's console: creating a game is a step
        // towards running it, not an end in itself.
        return redirect()
            ->route('immersion.gm.game.show', $game->id)
            ->with('status', "Partida creada con {$game->players()->count()} jugadores. Copia los enlaces y compártelos con tu equipo.");
    }

    public function loadDefaultTimeline(Game $game): RedirectResponse
    {
        if ($game->timelineEvents()->exists()) {
            return back()->with('status', 'Esta partida ya tiene una linea de tiempo.');
        }

        foreach ($game->caseDefinition()->timeline() as $event) {
            $game->timelineEvents()->create($event);
        }

        return back()->with('status', 'Linea de tiempo por defecto cargada.');
    }

    public function show(Request $request, Game $game, AiCredits $credits, GameCost $cost): Response
    {
        $spoilersAllowed = $request->user()->can('viewSpoilers', $game);

        return Inertia::render('GameMaster/Game', [
            'game' => function () use ($game, $spoilersAllowed) {
                $game->load('players');

                // The console hands out the per-player inbox links, so it is
                // the one place that needs their tokens and emails.
                $game->players->each->revealCredentials();

                // Event titles are themselves spoilers ("Sobre 3 — Redes,
                // teorías y recibos"), so an owner who is playing gets counts
                // instead of the timeline.
                if ($spoilersAllowed) {
                    $game->load('timelineEvents.deliveredToPlayer');
                }

                return $game;
            },

            'timelineSummary' => fn () => [
                'total' => $game->timelineEvents()->count(),
                'sent' => $game->timelineEvents()->whereNotNull('sent_at')->count(),
            ],

            'can' => [
                'direct' => $request->user()->can('direct', $game),
                'viewSpoilers' => $spoilersAllowed,
                'reveal' => $request->user()->can('reveal', $game),
            ],

            'ending' => fn () => [
                'type' => $game->ending_type,
                'revealed_at' => $game->ending_revealed_at,
                'pending_accusations' => $game->pendingAccusationsCount(),
                'players' => $game->players()->count(),

                // Confession audio: the console polls this while it generates,
                // and only offers the player once the file exists. The script
                // rides along so the Game Master can read it out if the
                // synthesis failed — and only ever reaches this page, which
                // already requires owning the game.
                'audio_status' => $game->ending_audio_status,
                'audio_script' => $game->ending_audio_script,

                // Epilogues: how many of the table's messages have gone out,
                // so the Game Master can tell "still writing" from "failed".
                'epilogues' => $game->ending_type === Game::ENDING_EPILOGUE ? [
                    'total' => $game->accusations()->count(),
                    'sent' => $game->accusations()->whereNotNull('epilogue_sent_at')->count(),
                    'failed' => $game->accusations()->where('epilogue_status', Accusation::EPILOGUE_FAILED)->count(),
                ] : null,
            ],

            // What this game costs in AI credits and where that stands. Shown
            // before it starts so a shortfall is discovered by the Game Master
            // setting up, not by a table already sitting at the board.
            'credits' => function () use ($game, $credits, $cost) {
                if (! $credits->enabled()) {
                    return null;
                }

                $hold = $credits->holdFor($game);

                return [
                    'cost' => $cost->for($game),
                    'available' => $credits->walletFor($game->user_id)->available(),
                    'shortfall' => $credits->shortfallFor($game),

                    // Three states, not two. "No hold" and "hold given back"
                    // look the same from the wallet but mean opposite things to
                    // the Game Master: one is a game that never needed capacity,
                    // the other is a game that needs it back before the table
                    // can interrogate again.
                    'armed' => $hold !== null && ! $hold->isReleased(),
                    'hold' => $hold ? [
                        'amount' => $hold->amount,
                        'spent' => $hold->spent,
                        'remaining' => max(0, $hold->amount - $hold->spent),
                    ] : null,
                ];
            },

            // In automatic mode the owner plays from their own inbox.
            'ownerPlayerToken' => fn () => $game->ownerPlayer()?->access_token,
        ]);
    }

    /**
     * Starts the case, and freezes the AI capacity it could need.
     *
     * The reservation comes first and the clock second. A game that starts and
     * then fails to reserve would be running with no way to pay for the ending
     * it was configured with, and there is no taking a started case back from
     * a table that is already reading its first envelope.
     */
    public function start(Game $game, AiCredits $credits): RedirectResponse
    {
        if ($game->started_at) {
            return back()->with('status', 'Esta partida ya fue iniciada.');
        }

        if (! $credits->reserve($game)) {
            $missing = $credits->shortfallFor($game);

            return back()->withErrors([
                'credits' => "Te faltan {$missing} creditos de IA para iniciar esta partida. Recarga o crea la partida sin interrogatorio.",
            ]);
        }

        $started = Game::query()
            ->whereKey($game->getKey())
            ->whereNull('started_at')
            ->update([
                'status' => 'running',
                'started_at' => Carbon::now(),
                'paused_seconds_total' => 0,
            ]);

        if ($started === 0) {
            // Lost the race against a second click. The hold is unique per
            // game, so the winner's reservation stands and this one made none.
            return back()->with('status', 'Esta partida ya fue iniciada.');
        }

        return back()->with('status', 'Caso iniciado. La linea de tiempo empezara a correr sola.');
    }

    /**
     * Deletes a game and everything under it.
     *
     * This is how a Game Master frees a slot against the quota, so it has to
     * really remove things: players, their inbox events, interrogations and
     * accusations all go via cascade. The generated audio lives on disk rather
     * than in a table, so it is cleaned up by hand — otherwise every deleted
     * game would leave megabytes of orphaned WAVs behind.
     *
     * The credit hold is released BEFORE the delete, for the same reason: the
     * cascade would take the hold row with it and the frozen credits would
     * never come back to the wallet.
     */
    public function destroy(Game $game, AiCredits $credits): RedirectResponse
    {
        $name = $game->name;

        $credits->release($game, "Partida eliminada: \"{$name}\"");

        // Only recordings this game generated. Case audio is shipped with the
        // case and shared by every table of it — deleting one game must never
        // take a file out of the case package.
        $audioPaths = $game->timelineEvents()
            ->whereNotNull('audio_path')
            ->get()
            ->reject(fn (TimelineEvent $event) => $event->audioIsCaseAsset())
            ->pluck('audio_path')
            ->all();

        $game->delete();

        foreach ($audioPaths as $path) {
            Storage::disk('local')->delete($path);
        }

        return redirect()
            ->route('immersion.gm.games.index')
            ->with('status', "Partida \"{$name}\" eliminada. Los enlaces de sus jugadores dejaron de funcionar.");
    }

    /**
     * Closes the case and gives back the AI capacity it did not use.
     *
     * Closing is the only moment the reservation can be settled: until then the
     * table could still ask another question or trigger the ending, and a
     * refund handed out early would be a refund of credits still needed.
     */
    public function finish(Game $game, AiCredits $credits): RedirectResponse
    {
        if ($game->isFinished()) {
            return back()->with('status', 'Esta partida ya estaba terminada.');
        }

        $game->finish();

        $refunded = $credits->release($game, "Caso cerrado: \"{$game->name}\"");

        return back()->with('status', $refunded > 0
            ? "Caso cerrado. Se te devolvieron {$refunded} creditos de IA que no se usaron."
            : 'Caso cerrado. Ya puedes revisar los interrogatorios y las acusaciones.');
    }

    /**
     * Stops the clock, and hands back the AI capacity while nobody is playing.
     *
     * Pausing is what a table does when it is continuing another day, and a
     * case spread over two weekends should not keep a week's worth of credits
     * frozen in between. Resuming re-freezes what is left.
     */
    public function pause(Game $game, AiCredits $credits): RedirectResponse
    {
        if (! $game->isRunning()) {
            return back()->with('status', 'Partida pausada.');
        }

        $game->update([
            'status' => 'paused',
            'paused_at' => Carbon::now(),
        ]);

        $returned = $credits->release($game, "Partida en pausa: \"{$game->name}\"");

        return back()->with('status', $returned > 0
            ? "Partida pausada. Te devolvimos {$returned} creditos mientras tanto; se vuelven a reservar al reanudar."
            : 'Partida pausada.');
    }

    /**
     * Restarts the clock, re-freezing only what the game has left to spend.
     *
     * Ordered so a resume that cannot pay does not happen at all: a running
     * case whose suspects refuse to answer is worse than one still paused,
     * because only the paused one still tells the Game Master why.
     */
    public function resume(Game $game, AiCredits $credits): RedirectResponse
    {
        if (! $game->isPaused() || ! $game->paused_at) {
            return back()->with('status', 'Partida reanudada.');
        }

        if (! $credits->rearm($game)) {
            return back()->withErrors([
                'credits' => "Te faltan {$credits->shortfallFor($game)} creditos de IA para reanudar esta partida. Recarga y vuelve a intentarlo.",
            ]);
        }

        $game->update([
            'status' => 'running',
            'paused_seconds_total' => $game->paused_seconds_total + $game->paused_at->diffInSeconds(Carbon::now()),
            'paused_at' => null,
        ]);

        return back()->with('status', 'Partida reanudada.');
    }

    /**
     * Re-freezes the capacity of a game that gave it back without being paused.
     *
     * The case the pause button does not cover: a table that simply walked away
     * with the game still running, whose reservation the stale-hold sweeper
     * returned days later. There is no resume to hook onto, and re-arming from
     * inside a player's question would silently debit the Game Master's wallet
     * for a table they may not be running any more — so it is a button.
     */
    public function rearmCredits(Game $game, AiCredits $credits): RedirectResponse
    {
        if ($credits->isArmed($game) || ! $credits->holdFor($game)) {
            return back()->with('status', 'Esta partida ya tiene su capacidad de IA reservada.');
        }

        if (! $credits->rearm($game)) {
            return back()->withErrors([
                'credits' => "Te faltan {$credits->shortfallFor($game)} creditos de IA para reactivar esta partida.",
            ]);
        }

        return back()->with('status', 'Capacidad de IA reactivada. Los interrogatorios vuelven a funcionar.');
    }

    public function forceNext(Game $game): RedirectResponse
    {
        $event = $game->timelineEvents()->pending()->first();

        if (! $event) {
            return back()->with('status', 'No hay mas eventos pendientes.');
        }

        // Queued, not dispatchSync: this event may carry text-to-speech and a
        // round of SMTP, which must not run inside the browser request.
        DispatchTimelineEvent::dispatch($event->id);

        return back()->with('status', "Evento \"{$event->title}\" en camino. Puede tardar un momento en salir.");
    }

    /**
     * Queues audio generation instead of doing it here.
     *
     * Text-to-speech can take most of a minute; holding the browser open for
     * that was the reason this action felt broken. The console polls the
     * event's audio_status for the outcome.
     */
    public function retryAudio(Game $game, TimelineEvent $event): JsonResponse
    {
        abort_if((int) $event->game_id !== (int) $game->id, 404);
        abort_unless($event->isAudio(), 400);

        if ($event->audio_path && Storage::disk('local')->exists($event->audio_path)) {
            $event->update(['audio_status' => TimelineEvent::AUDIO_READY]);

            return response()->json([
                'status' => 'ready',
                'message' => 'Este evento ya tiene su audio guardado.',
            ]);
        }

        $event->update(['audio_status' => TimelineEvent::AUDIO_PENDING]);

        GenerateEventAudio::dispatch($event->id);

        return response()->json([
            'status' => 'pending',
            'message' => 'Generando el audio. Puede tardar un minuto; el correo se reenvía solo cuando esté listo.',
        ]);
    }

    public function results(Request $request, Game $game, AccusationScoreboard $scoreboard): Response
    {
        $case = $game->caseDefinition();

        return Inertia::render('GameMaster/Results', [
            // players.accusation, not accusations.player: the page walks the
            // roster and reads each player's accusation, so loading the
            // sibling relation left every row looking unanswered.
            'game' => fn () => tap($game)->load(['players.accusation']),

            'scoreboard' => fn () => $scoreboard->for($game),

            // The short version only. The Game Master comparing a table of
            // accusations does not need the whole essay here.
            'solution' => fn () => $case->hasSolution() ? [
                'culprit_slug' => $case->culpritSlug(),
                'culprit_name' => $case->suspect($case->culpritSlug())['name'],
                'headline' => $case->solution()['headline'],
                'weapon' => $case->solution()['method'],
                'motive' => $case->solution()['motive'],
            ] : null,

            'reveal' => fn () => [
                'revealed_at' => $game->ending_revealed_at,
                'by' => $game->ending_revealed_by,
                'pending' => $game->pendingAccusationsCount(),
                'players' => $game->players()->count(),
                'can' => $request->user()->can('reveal', $game),
            ],
        ]);
    }

    /**
     * Streams the confession audio to the Game Master.
     *
     * Deliberately not reachable with a player token. The premium ending is
     * meant to be heard once, out loud, by everyone at the same time — a link
     * each player could open on their own phone would be a different, worse
     * mechanic. GamePolicy::control is what enforces it.
     */
    public function endingAudio(Game $game): BinaryFileResponse
    {
        abort_unless($game->ending_audio_path, 404);
        abort_unless(Storage::disk('local')->exists($game->ending_audio_path), 404);

        return response()
            ->file(Storage::disk('local')->path($game->ending_audio_path), [
                'Content-Type' => 'audio/wav',
                // The recording never changes and is only reachable by the
                // account that owns the game.
                'Cache-Control' => 'private, max-age=86400',
            ])
            ->setAutoLastModified();
    }

    /**
     * Publishes the ending to the whole table.
     *
     * Deliberately does not finish the game: the premium ending hands the Game
     * Master an audio to play before they close the case, and finishing is
     * where the AI credit hold will be released.
     */
    public function revealEnding(Game $game, RevealEnding $reveal): RedirectResponse
    {
        if (! $reveal->force($game)) {
            return back()->with('status', 'La solucion ya estaba revelada.');
        }

        return back()->with('status', 'Solucion revelada. Todos los jugadores ya pueden verla.');
    }

    /**
     * Only before the case starts.
     *
     * Turning the mechanic on mid-run would mean AI usage that was never
     * reserved, which is why the choice moved to the creation form.
     */
    public function toggleInterrogation(Game $game): RedirectResponse
    {
        if ($game->started_at) {
            return back()->with('status', 'El interrogatorio se elige antes de iniciar el caso.');
        }

        $game->update(['interrogation_enabled' => ! $game->interrogation_enabled]);

        return back()->with('status', $game->interrogation_enabled
            ? 'Interrogatorio (Mecanica 7) habilitado para los jugadores.'
            : 'Interrogatorio (Mecanica 7) deshabilitado.');
    }

    public function interrogations(Game $game): Response
    {
        return Inertia::render('GameMaster/Interrogations', [
            'game' => $game,
            'sessions' => fn () => InterrogationSession::where('game_id', $game->id)
                ->with(['player:id,name', 'messages'])
                ->orderBy('suspect_slug')
                ->get(),
        ]);
    }
}
