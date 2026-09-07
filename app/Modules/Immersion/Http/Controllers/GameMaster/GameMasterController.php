<?php

namespace App\Modules\Immersion\Http\Controllers\GameMaster;

use App\Http\Controllers\Controller;
use App\Modules\Immersion\Cases\CaseRegistry;
use App\Modules\Immersion\Jobs\DispatchTimelineEvent;
use App\Modules\Immersion\Mail\CaseTimelineMail;
use App\Modules\Immersion\Models\Game;
use App\Modules\Immersion\Models\InterrogationSession;
use App\Modules\Immersion\Models\TimelineEvent;
use App\Modules\Immersion\Services\GeminiAudioService;
use App\Modules\Immersion\Support\TimelineRecipients;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
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
    public function index(Request $request): Response
    {
        return Inertia::render('GameMaster/Dashboard', [
            'games' => fn () => $request->user()->games()->latest()->get(),
            'library' => fn () => $request->user()->library()->map(fn ($case) => [
                'slug' => $case->slug,
                'name' => $case->name,
            ])->values(),
        ]);
    }

    public function store(Request $request, CaseRegistry $cases): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'case_slug' => ['nullable', 'string', Rule::in($cases->slugs())],
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

        $game = Game::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'case_slug' => $case->slug,
            'case_version' => $case->version(),
            'status' => 'draft',
        ]);

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

        return redirect()
            ->route('immersion.gm.dashboard')
            ->with('status', "Partida \"{$game->name}\" creada con {$game->players()->count()} jugadores y su linea de tiempo.");
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

    public function show(Game $game): Response
    {
        return Inertia::render('GameMaster/Game', [
            'game' => function () use ($game) {
                $game->load(['players', 'timelineEvents.deliveredToPlayer', 'accusations.player']);

                // The Game Master panel hands out the per-player inbox links,
                // so it is the one place that needs their tokens and emails.
                $game->players->each->revealCredentials();

                return $game;
            },
        ]);
    }

    public function start(Game $game): RedirectResponse
    {
        // Starting an already-started game would reset the clock and desync
        // the timeline from the events it has already sent.
        if ($game->started_at) {
            return back()->with('status', 'Esta partida ya fue iniciada.');
        }

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

    public function retryAudio(Game $game, TimelineEvent $event): JsonResponse
    {
        abort_if((int) $event->game_id !== (int) $game->id, 404);
        abort_unless($event->isAudio(), 400);

        if ($event->audio_path && Storage::disk('local')->exists($event->audio_path)) {
            return response()->json([
                'status' => 'ok',
                'message' => 'Este evento ya tiene su audio guardado.',
                'event' => $event,
            ]);
        }

        $audioPath = app(GeminiAudioService::class)->synthesize($event->id, (string) $event->audio_script);

        if (! $audioPath) {
            return response()->json([
                'status' => 'error',
                'message' => 'El servicio de audio sigue sin responder. Revisa que lawxora-ai-service (npm start) este corriendo e intenta de nuevo.',
                'event' => $event,
            ]);
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

        return response()->json([
            'status' => 'ok',
            'message' => "Audio generado. Correo reenviado (con el audio) a {$sent} destinatario(s).",
            'event' => $event,
        ]);
    }

    public function results(Game $game): Response
    {
        return Inertia::render('GameMaster/Results', [
            'game' => fn () => tap($game)->load(['accusations.player', 'players']),
        ]);
    }

    public function toggleInterrogation(Game $game): RedirectResponse
    {
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
