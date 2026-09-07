<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\InterrogationMessage;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Services\SuspectInterrogationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class InterrogationController extends Controller
{
    public function index(Player $player): Response
    {
        abort_unless($player->game->interrogation_enabled, 403, 'El interrogatorio todavia no esta habilitado para esta partida.');

        // Only the claiming player's NAME is shown to the others, so the
        // related player is serialized with its credentials still hidden.
        $sessions = InterrogationSession::where('game_id', $player->game_id)
            ->with('player:id,name')
            ->get()
            ->keyBy('suspect_slug');

        $case = $player->game->caseDefinition();

        return Inertia::render('Player/InterrogationIndex', [
            'player' => $player->revealCredentials(),
            'suspects' => $case->suspects(),
            'victim' => $case->victim(),
            'sessions' => $sessions,
            'maxQuestions' => $case->interrogationQuestions(),
        ]);
    }

    public function show(Player $player, string $slug): Response
    {
        abort_unless($player->game->interrogation_enabled, 403, 'El interrogatorio todavia no esta habilitado para esta partida.');

        $case = $player->game->caseDefinition();
        $suspect = $case->suspect($slug);
        abort_if(! $suspect, 404);

        $session = InterrogationSession::where('game_id', $player->game_id)
            ->where('suspect_slug', $slug)
            ->with(['messages', 'player:id,name'])
            ->first();

        $lockedBy = null;
        $revealTestimony = false;

        if ($session && ! $session->isOwnedBy($player)) {
            $lockedBy = $session->player->name;
            $revealTestimony = true;
        } elseif ($session) {
            $revealTestimony = $session->isClosed();
        }

        return Inertia::render('Player/InterrogationChat', [
            'player' => $player->revealCredentials(),
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
            'originalTestimonyHtml' => $revealTestimony
                ? $case->content()->renderFile($suspect['file'])
                : null,
        ]);
    }

    public function ask(Request $request, Player $player, string $slug): JsonResponse
    {
        abort_unless($player->game->interrogation_enabled, 403, 'El interrogatorio todavia no esta habilitado para esta partida.');

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

        $playerMessage = InterrogationMessage::create([
            'session_id' => $session->id,
            'role' => 'player',
            'content' => $data['question'],
        ]);

        $reply = app(SuspectInterrogationService::class)->ask($session, $data['question']);

        $suspectMessage = InterrogationMessage::create([
            'session_id' => $session->id,
            'role' => 'suspect',
            'content' => $reply,
        ]);

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
            'original_testimony_html' => $session->isClosed()
                ? $case->content()->renderFile($suspect['file'])
                : null,
        ]);
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
