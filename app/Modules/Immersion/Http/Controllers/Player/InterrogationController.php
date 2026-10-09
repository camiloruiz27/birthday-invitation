<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Ai\Contracts\InterrogationProvider;
use App\Modules\Immersion\Ai\InterrogationUnavailable;
use App\Modules\Immersion\Ai\NullInterrogationProvider;
use App\Modules\Immersion\Models\InterrogationMessage;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Support\AiCredits;
use App\Modules\Immersion\Support\GameCost;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class InterrogationController extends Controller
{
    public function __construct(
        private AiCredits $credits,
        private GameCost $cost,
    ) {
    }

    public function index(Player $player): Response
    {
        // Not part of this game at all: nothing to show.
        abort_unless($player->game->interrogation_enabled, 403, 'El interrogatorio no está habilitado para esta partida.');

        $case = $player->game->caseDefinition();

        // Part of the game but not open yet. A page that says so beats a bare
        // 403 for someone who follows a link early, and no suspect is sent:
        // meeting them is exactly what the envelope that opens this is for.
        if (! $player->game->interrogationsOpen()) {
            return Inertia::render('Player/InterrogationIndex', [
                'player' => $player->revealCredentials(),
                'game' => $player->game->forPlayerView(),
                'locked' => true,
                'suspects' => (object) [],
                'victim' => $case->victim(),
                'sessions' => (object) [],
                'maxQuestions' => $case->interrogationQuestions(),
            ]);
        }

        // Only the claiming player's NAME is shown to the others, so the
        // related player is serialized with its credentials still hidden.
        $sessions = InterrogationSession::where('game_id', $player->game_id)
            ->with('player:id,name')
            ->get()
            ->keyBy('suspect_slug');

        return Inertia::render('Player/InterrogationIndex', [
            'player' => $player->revealCredentials(),
            'game' => $player->game->forPlayerView(),
            'locked' => false,
            'suspects' => $case->suspectsForPlayer(),
            'victim' => $case->victim(),
            'sessions' => $sessions,
            'maxQuestions' => $case->interrogationQuestions(),
        ]);
    }

    public function show(Player $player, string $slug): Response
    {
        abort_unless($player->game->interrogationsOpen(), 403, 'El interrogatorio todavía no está habilitado para esta partida.');

        $case = $player->game->caseDefinition();
        $suspect = $case->suspectForPlayer($slug);
        abort_if(! $suspect, 404);

        $session = InterrogationSession::where('game_id', $player->game_id)
            ->where('suspect_slug', $slug)
            ->with(['messages', 'player:id,name'])
            ->first();

        $lockedBy = null;

        if ($session && ! $session->isOwnedBy($player)) {
            $lockedBy = $session->player->name;
        }

        // The ficha is the reward for finishing an interrogation, for the
        // player who ran it and for everyone else once it is over.
        $revealTestimony = $session?->isClosed() ?? false;

        return Inertia::render('Player/InterrogationChat', [
            'player' => $player->revealCredentials(),
            'game' => $player->game->forPlayerView(),
            'slug' => $slug,
            'suspect' => $suspect,
            'session' => $session ?? [
                'id' => null,
                'player_id' => null,
                'questions_used' => 0,
                'max_questions' => $case->interrogationQuestions(),
                'closed_at' => null,
                'messages' => [],
            ],
            'lockedBy' => $lockedBy,
            'ficha' => $revealTestimony ? $case->ficha($slug) : null,
        ]);
    }

    public function ask(Request $request, Player $player, string $slug): JsonResponse
    {
        abort_unless($player->game->interrogationsOpen(), 403, 'El interrogatorio todavía no está habilitado para esta partida.');

        $case = $player->game->caseDefinition();
        $suspect = $case->suspect($slug);
        abort_if(! $suspect, 404);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:600'],
        ]);

        $session = $this->claimOrFindSession($player, $slug, $case->interrogationQuestions());

        if (! $session->isOwnedBy($player)) {
            return response()->json([
                'message' => "Ya fue interrogado por {$session->player->name}.",
                'locked' => true,
                'locked_by' => $session->player->name,
            ], 403);
        }

        // Reserve the question slot BEFORE spending an AI call. A single
        // conditional UPDATE is what actually enforces the budget: two
        // concurrent asks from the same player (double click, two tabs) would
        // otherwise both read the same questions_used and both write the same
        // increment, letting the player exceed the limit.
        if (! $session->reserveQuestion()) {
            return response()->json([
                'message' => "Ya usaste tus {$session->max_questions} preguntas con esta persona.",
                'closed' => true,
            ], 422);
        }

        $questionCost = $this->cost->questionCost();
        $note = "Pregunta a {$suspect['name']}";

        // Pre-flight only: nothing is charged yet. The whole question ceiling
        // was frozen when the case started, so this should never fail — if it
        // does, something released the hold underneath a running game, and the
        // honest thing is to say so rather than spend a model call nobody can
        // pay for. The slot just taken is given back, so the player loses
        // nothing.
        if (! $this->credits->canSpend($player->game, $questionCost)) {
            $session->releaseQuestion();

            return response()->json([
                'message' => 'Esta partida se quedo sin creditos de IA. Avisa al Game Master.',
                'out_of_credits' => true,
            ], 402);
        }

        // The question is only paid for once there is a real answer to hand
        // over. A gateway that fails charges nothing and gives the slot back.
        try {
            $reply = app(InterrogationProvider::class)->ask($session, $data['question']);
        } catch (InterrogationUnavailable $exception) {
            $session->releaseQuestion();

            $failures = $this->recordFailure($session);

            Log::warning('immersion_interrogation_unavailable', [
                'session_id' => $session->id,
                'player_id' => $player->id,
                'consecutive_failures' => $failures,
                'message' => $exception->getMessage(),
            ]);

            // The first failures are covered for in character, so one hiccup
            // does not break the scene. A gateway that keeps failing is not
            // hidden behind it: past the limit the player is told.
            if ($failures > max(0, (int) config('immersion.ai.evasions_before_error'))) {
                return response()->json([
                    'message' => 'Estamos teniendo un problema con la inteligencia artificial y el sospechoso no esta respondiendo. No se te cobro ni se gasto tu pregunta: intentalo de nuevo en unos minutos o avisa al Game Master.',
                    'ai_unavailable' => true,
                ], 503);
            }

            return $this->evasion($session, $data['question']);
        }

        $this->clearFailures($session);

        // Messages are stored only now, so the history sent to the gateway on
        // the next turn holds closed turns and never the question it is also
        // being asked as `question`.
        try {
            [$playerMessage, $suspectMessage] = DB::transaction(function () use ($session, $data, $reply, $player, $questionCost, $note) {
                $playerMessage = InterrogationMessage::create([
                    'session_id' => $session->id,
                    'role' => 'player',
                    'content' => $data['question'],
                ]);

                $suspectMessage = InterrogationMessage::create([
                    'session_id' => $session->id,
                    'role' => 'suspect',
                    'content' => $reply,
                ]);

                // The answer already exists. If the hold was released in the
                // seconds since the pre-flight, it is handed over unbilled
                // instead of thrown away — the player did nothing wrong.
                if (! $this->credits->spend($player->game, $questionCost, $note)) {
                    Log::warning('immersion_question_unbilled', [
                        'session_id' => $session->id,
                        'game_id' => $player->game_id,
                        'credits' => $questionCost,
                    ]);
                }

                return [$playerMessage, $suspectMessage];
            });
        } catch (\Throwable $exception) {
            $session->releaseQuestion();

            throw $exception;
        }

        if ($session->questionsRemaining() === 0 && ! $session->isClosed()) {
            $session->closed_at = Carbon::now();
            $session->transcript_revealed = true;
            $session->save();
        }

        return response()->json([
            'player_message' => $playerMessage,
            'suspect_message' => $suspectMessage,
            'questions_used' => $session->questions_used,
            'questions_remaining' => $session->questionsRemaining(),
            'max_questions' => $session->max_questions,
            'closed' => $session->isClosed(),
            'ficha' => $session->isClosed() ? $case->ficha($slug) : null,
        ]);
    }

    /**
     * The in-character deflection for a turn the gateway could not answer.
     *
     * Deliberately not stored and not counted: it is not a real answer, so it
     * stays out of the transcript, out of the history sent to the model on the
     * next turn, and out of the player's budget. The counters in the response
     * are therefore the real ones, unchanged, and the same question can simply
     * be asked again.
     */
    private function evasion(InterrogationSession $session, string $question): JsonResponse
    {
        $session->refresh();

        $stamp = now()->toISOString();
        $key = 'evasion-'.uniqid();

        return response()->json([
            'player_message' => [
                'id' => $key.'-player',
                'role' => 'player',
                'content' => $question,
                'created_at' => $stamp,
            ],
            'suspect_message' => [
                'id' => $key.'-suspect',
                'role' => 'suspect',
                'content' => NullInterrogationProvider::REPLY,
                'created_at' => $stamp,
            ],
            'questions_used' => $session->questions_used,
            'questions_remaining' => $session->questionsRemaining(),
            'max_questions' => $session->max_questions,
            'closed' => $session->isClosed(),
            'ficha' => null,
            'ai_degraded' => true,
        ]);
    }

    /**
     * Failed turns in a row for this suspect. Kept in the cache rather than the
     * database: it only has to outlive a bad few minutes, and forgetting it
     * after half an hour is the right answer.
     */
    private function recordFailure(InterrogationSession $session): int
    {
        $key = $this->failureKey($session);

        Cache::add($key, 0, now()->addMinutes(30));

        return (int) Cache::increment($key);
    }

    private function clearFailures(InterrogationSession $session): void
    {
        Cache::forget($this->failureKey($session));
    }

    private function failureKey(InterrogationSession $session): string
    {
        return 'immersion:interrogation-failures:'.$session->id;
    }

    /**
     * Un sospechoso solo puede ser interrogado por el primer jugador que le
     * mande una pregunta real (no por el primero que solo abra el chat). Usa
     * el mismo patron de Jobs/DispatchTimelineEvent.php (transaccion corta +
     * lockForUpdate) para que dos jugadores preguntando casi al mismo tiempo
     * a un sospechoso nunca antes tocado no puedan reclamarlo ambos.
     */
    private function claimOrFindSession(Player $player, string $slug, int $maxQuestions): InterrogationSession
    {
        try {
            return DB::transaction(function () use ($player, $slug, $maxQuestions) {
                $session = InterrogationSession::where('game_id', $player->game_id)
                    ->where('suspect_slug', $slug)
                    ->lockForUpdate()
                    ->first();

                if ($session) {
                    return $session;
                }

                return InterrogationSession::create([
                    'game_id' => $player->game_id,
                    'player_id' => $player->id,
                    'suspect_slug' => $slug,
                    'started_at' => Carbon::now(),
                    // Anchor the case's budget on the session so that editing
                    // the case never alters a game in progress.
                    'max_questions' => $maxQuestions,
                ]);
            });
        } catch (QueryException $exception) {
            // Carrera perdida contra el unique(game_id, suspect_slug): otro
            // jugador lo reclamo en el mismo instante.
            return InterrogationSession::where('game_id', $player->game_id)
                ->where('suspect_slug', $slug)
                ->firstOrFail();
        }
    }
}
