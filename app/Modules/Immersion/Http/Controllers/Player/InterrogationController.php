<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\InterrogationMessage;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Services\SuspectInterrogationService;
use App\Modules\Immersion\Support\CaseFileReader;
use App\Modules\Immersion\Support\CaseSuspects;
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

        $sessions = InterrogationSession::where('game_id', $player->game_id)
            ->with('player')
            ->get()
            ->keyBy('suspect_slug');

        return Inertia::render('Player/InterrogationIndex', [
            'player' => $player,
            'suspects' => CaseSuspects::all(),
            'victim' => CaseSuspects::victim(),
            'sessions' => $sessions,
            'maxQuestions' => InterrogationSession::MAX_QUESTIONS,
        ]);
    }

    public function show(Player $player, string $slug): Response
    {
        abort_unless($player->game->interrogation_enabled, 403, 'El interrogatorio todavia no esta habilitado para esta partida.');
        $suspect = CaseSuspects::find($slug);
        abort_if(! $suspect, 404);

        $session = InterrogationSession::where('game_id', $player->game_id)
            ->where('suspect_slug', $slug)
            ->with(['messages', 'player'])
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
            'player' => $player,
            'slug' => $slug,
            'suspect' => $suspect,
            'session' => $session ?? [
                'id' => null,
                'player_id' => null,
                'questions_used' => 0,
                'closed_at' => null,
                'messages' => [],
            ],
            'lockedBy' => $lockedBy,
            'originalTestimonyHtml' => $revealTestimony
                ? CaseFileReader::renderFile($suspect['file'])
                : null,
        ]);
    }

    public function ask(Request $request, Player $player, string $slug): JsonResponse
    {
        abort_unless($player->game->interrogation_enabled, 403, 'El interrogatorio todavia no esta habilitado para esta partida.');
        $suspect = CaseSuspects::find($slug);
        abort_if(! $suspect, 404);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:600'],
        ]);

        $session = $this->claimOrFindSession($player, $slug);

        if (! $session->isOwnedBy($player)) {
            return response()->json([
                'message' => "Ya fue interrogado por {$session->player->name}.",
                'locked' => true,
                'locked_by' => $session->player->name,
            ], 403);
        }

        if ($session->isClosed()) {
            return response()->json([
                'message' => 'Ya usaste tus 5 preguntas con esta persona.',
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

        $session->questions_used++;

        if ($session->questions_used >= InterrogationSession::MAX_QUESTIONS) {
            $session->closed_at = Carbon::now();
            $session->transcript_revealed = true;
        }

        $session->save();

        return response()->json([
            'player_message' => $playerMessage,
            'suspect_message' => $suspectMessage,
            'questions_used' => $session->questions_used,
            'questions_remaining' => $session->questionsRemaining(),
            'closed' => $session->isClosed(),
            'original_testimony_html' => $session->isClosed()
                ? CaseFileReader::renderFile($suspect['file'])
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
    private function claimOrFindSession(Player $player, string $slug): InterrogationSession
    {
        try {
            return DB::transaction(function () use ($player, $slug) {
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
