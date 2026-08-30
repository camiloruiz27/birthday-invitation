<?php

namespace App\Modules\Immersion\Http\Controllers\GameMaster;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\Player;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Services\GeminiAudioService;
use App\Modules\Immersion\Support\DefaultTimeline;
use App\Modules\Immersion\Support\TimelineRecipients;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GameMasterController extends Controller
{
    public function index(): View
    {
        return view('immersion::game-master.dashboard', [
            'games' => Game::query()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'players' => ['required', 'array', 'min:1'],
            'players.*.name' => ['required', 'string', 'max:255'],
            'players.*.email' => ['required', 'email'],
        ]);

        $game = Game::create([
            'name' => $data['name'],
            'status' => 'draft',
        ]);

        foreach ($data['players'] as $playerData) {
            $game->players()->create([
                'name' => $playerData['name'],
                'email' => $playerData['email'],
                'access_token' => Str::uuid(),
            ]);
        }

        foreach (DefaultTimeline::events() as $event) {
            $game->timelineEvents()->create($event);
        }

        return redirect()
            ->route('immersion.gm.dashboard')
            ->with('status', "Partida \"{$game->name}\" creada con {$game->players()->count()} jugadores y su linea de tiempo.");
    }

    public function loadDefaultTimeline(Game $game): RedirectResponse
    {
        if ($game->timelineEvents()->exists()) {
            return back()->with('status', 'Esta partida ya tiene una linea de tiempo.');
        }

        foreach (DefaultTimeline::events() as $event) {
            $game->timelineEvents()->create($event);
        }

        return back()->with('status', 'Linea de tiempo por defecto cargada.');
    }

    public function show(Game $game): View
    {
        $game->load(['players', 'timelineEvents.deliveredToPlayer', 'accusations.player']);

        return view('immersion::game-master.game', [
            'game' => $game,
        ]);
    }

    public function start(Game $game): RedirectResponse
    {
        $game->update([
            'status' => 'running',
            'started_at' => Carbon::now(),
            'paused_seconds_total' => 0,
        ]);

        return back()->with('status', 'Caso iniciado. La linea de tiempo empezara a correr sola.');
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

        DispatchTimelineEvent::dispatchSync($event->id);

        return back()->with('status', "Evento \"{$event->title}\" disparado manualmente.");
    }

    public function retryAudio(Game $game, TimelineEvent $event): RedirectResponse
    {
        abort_if($event->game_id !== $game->id, 404);
        abort_unless($event->isAudio(), 400);

        if ($event->audio_path && Storage::disk('local')->exists($event->audio_path)) {
            return back()->with('status', 'Este evento ya tiene su audio guardado.');
        }

        $audioPath = app(GeminiAudioService::class)->synthesize($event->id, (string) $event->audio_script);

        if (! $audioPath) {
            return back()->with('status', 'El servicio de audio sigue sin responder. Revisa que lawxora-ai-service (npm start) este corriendo e intenta de nuevo.');
        }

        $event->audio_path = $audioPath;
        $event->save();

        $sent = 0;

        foreach (TimelineRecipients::resolve($event) as $recipient) {
            try {
                Mail::to($recipient->email)->send(new CaseTimelineMail($event, $recipient));
                $sent++;
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return back()->with('status', "Audio generado. Correo reenviado (con el audio) a {$sent} destinatario(s).");
    }

    public function results(Game $game): View
    {
        $game->load(['accusations.player', 'players']);

        return view('immersion::game-master.results', [
            'game' => $game,
        ]);
    }

    public function toggleInterrogation(Game $game): RedirectResponse
    {
        $game->update(['interrogation_enabled' => ! $game->interrogation_enabled]);

        return back()->with('status', $game->interrogation_enabled
            ? 'Interrogatorio (Mecanica 7) habilitado para los jugadores.'
            : 'Interrogatorio (Mecanica 7) deshabilitado.');
    }

    public function interrogations(Game $game): View
    {
        $sessions = InterrogationSession::where('game_id', $game->id)
            ->with(['player', 'messages'])
            ->orderBy('suspect_slug')
            ->get();

        return view('immersion::game-master.interrogations', [
            'game' => $game,
            'sessions' => $sessions,
        ]);
    }
}
