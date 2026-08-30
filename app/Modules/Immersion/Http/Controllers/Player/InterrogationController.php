<?php

namespace App\Modules\Immersion\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Models\InterrogationMessage;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Services\SuspectInterrogationService;
use App\Modules\Immersion\Support\CaseFileReader;
use App\Modules\Immersion\Support\CaseSuspects;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InterrogationController extends Controller
{
    public function index(Player $player): View
    {
        abort_unless($player->game->interrogation_enabled, 403, 'El interrogatorio todavia no esta habilitado para esta partida.');

        $sessions = InterrogationSession::where('player_id', $player->id)->get()->keyBy('suspect_slug');

        return view('immersion::player.interrogation-index', [
            'player' => $player,
            'suspects' => CaseSuspects::all(),
            'victim' => CaseSuspects::victim(),
            'sessions' => $sessions,
            'maxQuestions' => InterrogationSession::MAX_QUESTIONS,
        ]);
    }

    public function show(Player $player, string $slug): View
    {
        abort_unless($player->game->interrogation_enabled, 403, 'El interrogatorio todavia no esta habilitado para esta partida.');
        $suspect = CaseSuspects::find($slug);
        abort_if(! $suspect, 404);

        $session = InterrogationSession::firstOrCreate(
            ['player_id' => $player->id, 'suspect_slug' => $slug],
            ['game_id' => $player->game_id, 'started_at' => Carbon::now()]
        );

        $session->load('messages');

        return view('immersion::player.interrogation-chat', [
            'player' => $player,
            'slug' => $slug,
            'suspect' => $suspect,
            'session' => $session,
            'originalTestimonyHtml' => $session->isClosed()
                ? CaseFileReader::renderFile($suspect['file'])
                : null,
        ]);
    }

    public function store(Request $request, Player $player, string $slug): RedirectResponse
    {
        abort_unless($player->game->interrogation_enabled, 403, 'El interrogatorio todavia no esta habilitado para esta partida.');
        $suspect = CaseSuspects::find($slug);
        abort_if(! $suspect, 404);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:600'],
        ]);

        $session = InterrogationSession::firstOrCreate(
            ['player_id' => $player->id, 'suspect_slug' => $slug],
            ['game_id' => $player->game_id, 'started_at' => Carbon::now()]
        );

        if ($session->isClosed()) {
            return redirect()
                ->route('immersion.player.interrogation.show', [$player->access_token, $slug])
                ->with('status', 'Ya usaste tus 5 preguntas con esta persona.');
        }

        InterrogationMessage::create([
            'session_id' => $session->id,
            'role' => 'player',
            'content' => $data['question'],
        ]);

        $reply = app(SuspectInterrogationService::class)->ask($session, $data['question']);

        InterrogationMessage::create([
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

        return redirect()->route('immersion.player.interrogation.show', [$player->access_token, $slug]);
    }
}
