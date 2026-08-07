<?php

namespace App\Http\Controllers;

use App\Models\CoupleAiSuggestion;
use App\Models\CoupleExperienceAnswer;
use App\Models\CoupleExperienceConsent;
use App\Models\CoupleExperienceProgress;
use App\Services\CoupleAiService;
use App\Support\CoupleActivityLibrary;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CoupleExperienceController extends Controller
{
    public function index(): View
    {
        return view('couple.dashboard', $this->viewData('Inicio'));
    }

    public function assistant(): View
    {
        return view('couple.assistant', $this->viewData('Asistente IA'));
    }

    public function activities(): View
    {
        return view('couple.activities', $this->viewData('Actividades'));
    }

    public function timers(): View
    {
        return view('couple.timers', $this->viewData('Cronometros'));
    }

    public function library(): View
    {
        return view('couple.library', $this->viewData('Biblioteca'));
    }

    public function levelSix(): View
    {
        return view('couple.level-six', $this->viewData('Nivel 6'));
    }

    public function history(): View
    {
        return view('couple.history', $this->viewData('Historial'));
    }

    private function viewData(string $pageTitle): array
    {
        $user = Auth::user();
        $sharedViewData = [
            'title' => $pageTitle . ' | Aniversario en Cartagena',
            'days' => CoupleActivityLibrary::days(),
            'activities' => CoupleActivityLibrary::groupedByDay(),
            'locations' => CoupleActivityLibrary::locations(),
            'moods' => CoupleActivityLibrary::moods(),
            'intentions' => CoupleActivityLibrary::intentions(),
            'gameModes' => CoupleActivityLibrary::gameModes(),
            'intimacyConcepts' => CoupleActivityLibrary::intimacyConcepts(),
            'positionSuggestions' => CoupleActivityLibrary::positionSuggestions(),
            'levelSixRituals' => CoupleActivityLibrary::levelSixRituals(),
            'levelSixGuides' => CoupleActivityLibrary::levelSixGuides(),
            'levelSixRouletteOptions' => CoupleActivityLibrary::levelSixRouletteOptions(),
        ];

        if (! $user) {
            return array_merge($sharedViewData, [
                'user' => null,
                'consentAccepted' => false,
                'progress' => collect(),
                'answers' => collect(),
                'aiSuggestions' => collect(),
            ]);
        }

        return array_merge($sharedViewData, [
            'user' => $user,
            'consentAccepted' => CoupleExperienceConsent::where('user_id', $user->id)->exists(),
            'progress' => CoupleExperienceProgress::where('user_id', $user->id)
                ->get()
                ->keyBy('activity_key'),
            'answers' => CoupleExperienceAnswer::with('user')
                ->latest()
                ->limit(30)
                ->get(),
            'aiSuggestions' => CoupleAiSuggestion::with('user')
                ->latest()
                ->limit(12)
                ->get(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            return back()
                ->withErrors(['email' => 'No pudimos iniciar sesion con esos datos.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('couple-experience.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('couple-experience.index');
    }

    public function consent(Request $request): RedirectResponse
    {
        $request->validate([
            'accept_rules' => ['accepted'],
        ]);

        CoupleExperienceConsent::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['accepted_at' => Carbon::now()]
        );

        return redirect()
            ->route('couple-experience.index')
            ->with('status', 'Consentimiento guardado. La experiencia esta lista.');
    }

    public function progress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity_key' => ['required', 'string'],
            'status' => ['required', 'in:completed,skipped'],
        ]);

        $activity = CoupleActivityLibrary::findActivity($data['activity_key']);
        abort_if(! $activity, 404);

        CoupleExperienceProgress::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'activity_key' => $activity['key'],
            ],
            [
                'day' => $activity['day'],
                'level' => $activity['level'],
                'status' => $data['status'],
            ]
        );

        return back()
            ->with('status', $data['status'] === 'completed' ? 'Reto completado.' : 'Reto pasado.');
    }

    public function answer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'activity_key' => ['required', 'string'],
            'answer' => ['required', 'string', 'max:3000'],
        ]);

        $activity = CoupleActivityLibrary::findActivity($data['activity_key']);
        abort_if(! $activity, 404);

        CoupleExperienceAnswer::create([
            'user_id' => $request->user()->id,
            'day' => $activity['day'],
            'level' => $activity['level'],
            'activity_key' => $activity['key'],
            'prompt' => $activity['prompt'],
            'answer' => $data['answer'],
        ]);

        return redirect()
            ->route('couple-experience.history')
            ->with('status', 'Respuesta guardada para verla juntos.');
    }

    public function suggest(Request $request, CoupleAiService $ai): RedirectResponse
    {
        abort_if(! CoupleExperienceConsent::where('user_id', $request->user()->id)->exists(), 403);

        $locations = CoupleActivityLibrary::locations();
        $moods = CoupleActivityLibrary::moods();
        $intentions = CoupleActivityLibrary::intentions();

        $data = $request->validate([
            'day' => ['required', 'integer', 'between:1,4'],
            'level' => ['required', 'integer', 'between:2,5'],
            'location' => ['required', 'string', 'in:' . implode(',', array_keys($locations))],
            'mood' => ['required', 'string', 'in:' . implode(',', array_keys($moods))],
            'intention' => ['required', 'string', 'in:' . implode(',', array_keys($intentions))],
            'activity_key' => ['nullable', 'string'],
            'limits' => ['nullable', 'string', 'max:1000'],
        ]);

        $activity = ! empty($data['activity_key'])
            ? CoupleActivityLibrary::findActivity($data['activity_key'])
            : null;

        $history = CoupleExperienceAnswer::with('user')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (CoupleExperienceAnswer $answer) => [
                'user' => $answer->user->name ?? 'Usuario',
                'day' => $answer->day,
                'level' => $answer->level,
                'prompt' => $answer->prompt,
                'answer' => $answer->answer,
            ])
            ->values()
            ->all();

        $payload = [
            'day' => (int) $data['day'],
            'level' => (int) $data['level'],
            'location' => $data['location'],
            'location_label' => $locations[$data['location']],
            'mood' => $data['mood'],
            'mood_label' => $moods[$data['mood']],
            'intention' => $data['intention'],
            'intention_label' => $intentions[$data['intention']],
            'limits' => $data['limits'] ?? '',
            'current_activity' => $activity,
            'recent_history' => $history,
        ];

        $suggestion = $ai->suggest($payload);

        CoupleAiSuggestion::create([
            'user_id' => $request->user()->id,
            'day' => $payload['day'],
            'level' => $payload['level'],
            'location' => $payload['location'],
            'mood' => $payload['mood'],
            'intention' => $payload['intention'],
            'prompt_payload' => $payload,
            'suggestion' => $suggestion,
            'status' => 'accepted',
        ]);

        return redirect()
            ->route('couple-experience.history')
            ->with('status', 'Sugerencia generada y guardada.');
    }

    public function suggestionStatus(Request $request, $suggestionId): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:accepted,completed,dismissed'],
        ]);

        $suggestion = CoupleAiSuggestion::findOrFail($suggestionId);

        if ($data['status'] === 'dismissed') {
            $suggestion->delete();

            return redirect()
                ->route('couple-experience.history')
                ->with('status', 'Sugerencia descartada y eliminada.');
        }

        $suggestion->update(['status' => $data['status']]);

        return redirect()
            ->route('couple-experience.history')
            ->with('status', 'Sugerencia marcada como completada.');
    }
}
