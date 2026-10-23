<?php

namespace App\Modules\Express\Http\Controllers;

use App\Modules\Express\Models\Room;
use App\Modules\Express\Support\Clock;
use App\Modules\Express\Support\Contacts;
use App\Modules\Express\Support\ExpressCaseRegistry;
use App\Modules\Express\Support\ExpressEvents;
use App\Modules\Express\Support\ExpressException;
use App\Modules\Express\Support\PublicState;
use App\Modules\Express\Support\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The two entry doors: the host creating a room, and a friend joining one.
 * Both ask for a nickname and, if the visitor wants, one way to reach them
 * (with the authorisation Ley 1581 de 2012 asks for).
 */
class SetupController
{
    public function __construct(
        private readonly ExpressCaseRegistry $cases,
        private readonly RoomService $rooms,
    ) {
    }

    public function create(Request $request): Response
    {
        $event = ExpressEvents::resolve($request);
        $status = ExpressEvents::status($event);

        if (! $status['open']) {
            return Inertia::render('Express/Landing', [
                'event' => ['slug' => $event['slug'], 'name' => $event['name'], 'tagline' => $event['tagline']],
                'enabled' => false,
                'closed_reason' => $status['reason'],
            ]);
        }

        $cases = ExpressEvents::cases($event, $this->cases);

        return Inertia::render('Express/Create', [
            'event' => ['slug' => $event['slug'], 'name' => $event['name'], 'tagline' => $event['tagline']],
            'cases' => array_values(array_map(fn ($case) => [
                'slug' => $case->slug(),
                'title' => $case->title(),
                'tagline' => $case->tagline(),
                'genre' => $case->genre(),
                'hook' => $case->hook(),
                'hero' => $case->assets()['hero'],
            ], $cases)),
            'suggested' => $this->suggested($event['slug'], array_keys($cases)),
            'consent' => $this->consent(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $event = ExpressEvents::resolve($request);
        [$nickname, $contact] = $this->validated($request, ['case_slug' => ['required', 'string', 'max:80']]);

        try {
            $player = $this->rooms->createRoom($event, (string) $request->input('case_slug'), $nickname, $contact, $request);
        } catch (ExpressException $e) {
            throw ValidationException::withMessages([$e->key === 'invalid' ? 'case_slug' : 'nickname' => $e->getMessage()]);
        }

        return redirect()->route('express.room', $player->token);
    }

    public function join(Request $request, string $code): Response
    {
        $room = $this->room($code);
        $event = ExpressEvents::find($room->event_slug) ?? ExpressEvents::find(null);
        $request->session()->put('express.event', $event['slug']);
        $status = ExpressEvents::status($event);

        $elapsed = $room->started_at_ms !== null ? Clock::elapsed((int) $room->started_at_ms) : null;
        $players = $room->players()->count();

        $joinable = $status['open'] && PublicState::joinable(
            ['status' => $room->status, 'players' => array_fill(0, $players, 0)],
            $elapsed,
            (array) config('express.schedule'),
        );

        return Inertia::render('Express/Join', [
            'event' => ['slug' => $event['slug'], 'name' => $event['name'], 'tagline' => $event['tagline']],
            'room' => [
                'code' => $room->code,
                'case_title' => $this->cases->find($room->case_slug)?->title(),
                'status' => $room->status,
                'joinable' => $joinable,
                'reason' => $joinable ? null : $this->whyNot($room, $status, $players),
            ],
            'consent' => $this->consent(),
        ]);
    }

    public function joinStore(Request $request, string $code): RedirectResponse
    {
        $room = $this->room($code);
        [$nickname, $contact] = $this->validated($request);

        try {
            $player = $this->rooms->join($room, $nickname, $contact, $request);
        } catch (ExpressException $e) {
            throw ValidationException::withMessages(['nickname' => $e->getMessage()]);
        }

        return redirect()->route('express.room', $player->token);
    }

    private function room(string $code): Room
    {
        return Room::query()->where('code', strtoupper(trim($code)))->firstOrFail();
    }

    /**
     * Validates the shared fields and returns the clean nickname and contact.
     *
     * @param  array<string, array<int, string>>  $extra
     * @return array{0: string, 1: array{type: string, value: string}|null}
     */
    private function validated(Request $request, array $extra = []): array
    {
        $hasContact = in_array($request->input('contact_type'), ['email', 'whatsapp'], true);

        $request->validate([
            // The honeypot: a person never sees it, a bot fills every field.
            'website' => ['nullable', 'prohibited'],
            'contact_type' => ['nullable', 'in:none,email,whatsapp'],
            'contact' => [$hasContact ? 'required' : 'nullable', 'string', 'max:120'],
            'consent' => [$hasContact ? 'accepted' : 'nullable'],
            'adult' => [$hasContact ? 'accepted' : 'nullable'],
        ] + $extra, [
            'contact.required' => 'Escribe tu contacto o elige «Sin contacto».',
            'consent.accepted' => 'Para dejar tu contacto necesitamos tu autorización.',
            'adult.accepted' => 'Para dejar tu contacto debes ser mayor de 18 años.',
            'website.prohibited' => 'No pudimos procesar el formulario.',
        ]);

        $nickname = Contacts::nickname($request->input('nickname'));

        if (mb_strlen($nickname) < 2) {
            throw ValidationException::withMessages(['nickname' => 'Escribe un apodo de al menos 2 letras.']);
        }

        $contact = null;

        if ($hasContact) {
            $contact = Contacts::normalize($request->input('contact_type'), $request->input('contact'))
                ?? throw ValidationException::withMessages([
                    'contact' => $request->input('contact_type') === 'email'
                        ? 'Ese correo no parece válido.'
                        : 'Ese número no parece válido. Escríbelo con indicativo si no es de Colombia.',
                ]);
        }

        return [$nickname, $contact];
    }

    /** The case least played at this event, so neighbouring tables do not repeat. */
    private function suggested(string $eventSlug, array $slugs): ?string
    {
        if ($slugs === []) {
            return null;
        }

        $played = Room::query()
            ->where('event_slug', $eventSlug)
            ->selectRaw('case_slug, count(*) as total')
            ->groupBy('case_slug')
            ->pluck('total', 'case_slug');

        $counts = array_map(fn ($slug) => (int) ($played[$slug] ?? 0), $slugs);
        $least = array_keys($counts, min($counts));

        return $slugs[$least[array_rand($least)]];
    }

    /** @return array{version: string, text: string} */
    private function consent(): array
    {
        return [
            'version' => (string) config('express.consent.version'),
            'text' => (string) config('express.consent.text'),
        ];
    }

    /** @param  array{open: bool, reason: ?string}  $status */
    private function whyNot(Room $room, array $status, int $players): string
    {
        if (! $status['open']) {
            return (string) $status['reason'];
        }

        if ($players >= (int) config('express.max_players')) {
            return 'La sala ya está llena.';
        }

        return match ($room->status) {
            Room::RUNNING => 'La ronda ya empezó. Pídanle al anfitrión una nueva.',
            Room::FINISHED => 'Esta ronda ya terminó.',
            default => 'Esta sala ya no está disponible.',
        };
    }
}
