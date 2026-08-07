@extends('couple.partials.layout')

@section('content')
    @if (! $consentAccepted)
        @include('couple.partials.consent')
    @else
        @php
            $completed = $progress->where('status', 'completed')->count();
            $skipped = $progress->where('status', 'skipped')->count();
            $total = collect($activities)->sum(fn ($day) => count($day['activities']));
            $percent = $total > 0 ? min(100, round(($completed / $total) * 100)) : 0;
        @endphp

        <section class="mt-6 grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm sm:p-8">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Tablero privado</p>
                <h2 class="mt-3 max-w-2xl text-4xl font-semibold leading-tight text-stone-950">Un viaje con menos ruido y mas intencion.</h2>
                <p class="mt-4 max-w-2xl text-sm leading-6 text-stone-600">
                    Entra por la seccion que necesiten en el momento: una sugerencia rapida, un juego por nivel, un cronometro o el historial de lo que han vivido.
                </p>
                <div class="mt-8">
                    <div class="flex items-center justify-between text-sm font-semibold text-stone-700">
                        <span>Progreso de actividades</span>
                        <span>{{ $percent }}%</span>
                    </div>
                    <div class="mt-2 h-3 overflow-hidden rounded-full bg-stone-200">
                        <div class="h-full rounded-full bg-stone-950" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1">
                <div class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-stone-500">Completadas</p>
                    <p class="mt-2 text-4xl font-semibold text-stone-950">{{ $completed }}</p>
                </div>
                <div class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-stone-500">Pasadas</p>
                    <p class="mt-2 text-4xl font-semibold text-stone-950">{{ $skipped }}</p>
                </div>
                <div class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-stone-500">Respuestas</p>
                    <p class="mt-2 text-4xl font-semibold text-stone-950">{{ $answers->count() }}</p>
                </div>
            </div>
        </section>

        <section class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['route' => 'couple-experience.assistant', 'icon' => 'wand-2', 'title' => 'Asistente IA', 'text' => 'Para cuando quieren una guia segun lugar, energia e intencion.'],
                ['route' => 'couple-experience.activities', 'icon' => 'dices', 'title' => 'Actividades', 'text' => 'Juegos por dia, nivel, lugar y privacidad.'],
                ['route' => 'couple-experience.timers', 'icon' => 'timer', 'title' => 'Cronometros', 'text' => 'Clasico y express con sonido para cambios de turno.'],
                ['route' => 'couple-experience.library', 'icon' => 'message-circle-heart', 'title' => 'Biblioteca', 'text' => 'Conceptos, rituales y posiciones guiadas con consentimiento.'],
                ['route' => 'couple-experience.level-six', 'icon' => 'sparkles', 'title' => 'Nivel 6', 'text' => 'Zona privada para acuerdos, fantasia y sensualidad intensa.'],
                ['route' => 'couple-experience.history', 'icon' => 'brain', 'title' => 'Historial', 'text' => 'Respuestas compartidas y sugerencias guardadas.'],
            ] as $card)
                <a class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-stone-950" href="{{ route($card['route']) }}">
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-stone-950 text-white">
                        <i data-lucide="{{ $card['icon'] }}" class="h-5 w-5"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-stone-950">{{ $card['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-stone-600">{{ $card['text'] }}</p>
                </a>
            @endforeach
        </section>

        <section class="mt-6 grid gap-4 lg:grid-cols-4">
            @foreach ($activities as $day)
                <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Dia {{ $day['day'] }}</p>
                    <h3 class="mt-2 text-lg font-semibold text-stone-950">{{ $day['title'] }}</h3>
                    <p class="mt-2 text-sm leading-6 text-stone-600">{{ $day['setting'] }}</p>
                </article>
            @endforeach
        </section>
    @endif
@endsection
