<?php

namespace App\Modules\Immersion\Http\Controllers\GameMaster;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Jobs\GenerateEventAudio;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Support\AccusationScoreboard;
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

    public function create(Request $request, GameQuota $quota): Response
    {
        $user = $request->user();
        $library = $user->library();
        $quotas = $quota->forCases($user, $library->pluck('slug'));

        return Inertia::render('GameMaster/CreateGame', [
            // Each case carries its own quota, so the form can react as the
            // Game Master switches between them.
            'library' => $library->map(fn ($case) => [
                'slug' => $case->slug,
                'name' => $case->name,
                'min_players' => $case->min_players,
                'max_players' => $case->max_players,
                'quota' => $quotas[$case->slug],
            ])->values(),
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
            // before the case starts.
            'ending_type' => ['nullable', Rule::in([Game::ENDING_CLASSIC])],
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

        $user = $request->user();
        $mode = $data['mode'] ?? Game::MODE_GM_LED;

        // The whole creation runs under a lock on the owner's row: counting
        // games and then inserting one is a read-modify-write, and two
        // requests arriving together would otherwise both see five games and
        // both create a sixth.
        $game = DB::transaction(function () use ($user, $data, $case, $mode, $quota) {
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
                'ending_type' => $data['ending_type'] ?? Game::ENDING_CLASSIC,
                'interrogation_enabled' => (bool) ($data['interrogation_enabled'] ?? false),
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

    public function show(Request $request, Game $game): Response
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
            ],

            // In automatic mode the owner plays from their own inbox.
            'ownerPlayerToken' => fn () => $game->ownerPlayer()?->access_token,
        ]);
    }

    /**
     * Starts the run.
     *
     * A conditional UPDATE rather than check-then-write: starting twice would
     * reset the clock and desync the timeline from what it already sent, and
     * once starting also reserves AI credits a double click would debit twice.
     */
    public function start(Game $game): RedirectResponse
    {
        $started = Game::query()
            ->whereKey($game->getKey())
            ->whereNull('started_at')
            ->update([
                'status' => 'running',
                'started_at' => Carbon::now(),
                'paused_seconds_total' => 0,
            ]);

        if ($started === 0) {
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
     */
    public function destroy(Game $game): RedirectResponse
    {
        $name = $game->name;

        $audioPaths = $game->timelineEvents()
            ->whereNotNull('audio_path')
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

    public function finish(Game $game): RedirectResponse
    {
        if ($game->isFinished()) {
            return back()->with('status', 'Esta partida ya estaba terminada.');
        }

        $game->finish();

        return back()->with('status', 'Caso cerrado. Ya puedes revisar los interrogatorios y las acusaciones.');
    }

    public function pause(Game $game): RedirectResponse
    {
        if ($game->isRunning()) {
            $game->update([
                'status' => 'paused',
                'paused_at' => Carbon::now(),
            ]);
        }

        return back()->with('status', 'Partida pausada.');
    }

    public function resume(Game $game): RedirectResponse
    {
        if ($game->isPaused() && $game->paused_at) {
            $game->update([
                'status' => 'running',
                'paused_seconds_total' => $game->paused_seconds_total + $game->paused_at->diffInSeconds(Carbon::now()),
                'paused_at' => null,
            ]);
        }

        return back()->with('status', 'Partida reanudada.');
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
