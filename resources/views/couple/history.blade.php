@extends('couple.partials.layout')

@section('content')
    @if (! $consentAccepted)
        @include('couple.partials.consent')
    @else
        <section class="mt-6 rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Historial compartido</p>
            <h2 class="mt-2 text-3xl font-semibold text-stone-950">Lo que han guardado juntos</h2>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">Aqui quedan las respuestas y sugerencias aceptadas. Descartar una sugerencia la elimina de la base de datos.</p>
        </section>

        <section class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <div class="space-y-4">
                <h3 class="text-2xl font-semibold text-stone-950">Sugerencias IA</h3>
                @forelse ($aiSuggestions as $suggestion)
                    @php($guide = $suggestion->suggestion ?? [])
                    <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">Dia {{ $suggestion->day }}</span>
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">Nivel {{ $suggestion->level }}</span>
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">{{ $suggestion->status }}</span>
                            <span class="text-xs font-semibold text-stone-500">{{ $suggestion->user->name ?? 'Usuario' }}</span>
                        </div>

                        <div class="mt-4 grid gap-3">
                            @foreach ([
                                'Frase para iniciar' => 'opening_phrase',
                                'Pregunta de conexion' => 'connection_question',
                                'Juego corto' => 'short_game',
                                'Actividad principal' => 'main_activity',
                                'Cierre o pausa' => 'closing_or_pause',
                                'Privacidad' => 'privacy_level',
                                'Nota de cuidado' => 'safety_note',
                            ] as $label => $key)
                                @if (! empty($guide[$key]))
                                    <div class="rounded-md bg-stone-50 p-4">
                                        <p class="text-xs font-bold uppercase tracking-widest text-stone-500">{{ $label }}</p>
                                        <p class="mt-1 text-sm leading-6 text-stone-700">{{ $guide[$key] }}</p>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            <form method="POST" action="{{ route('couple-experience.ai.status', $suggestion->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="completed">
                                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-3 text-sm font-bold text-white hover:bg-stone-800" type="submit">
                                    <i data-lucide="check" class="h-4 w-4"></i>
                                    Completar
                                </button>
                            </form>
                            <form method="POST" action="{{ route('couple-experience.ai.status', $suggestion->id) }}">
                                @csrf
                                <input type="hidden" name="status" value="dismissed">
                                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="submit">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                    Descartar y borrar
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-stone-300 bg-white p-6 text-sm text-stone-600 shadow-sm">Todavia no hay sugerencias guardadas.</div>
                @endforelse
            </div>

            <div class="space-y-4">
                <h3 class="text-2xl font-semibold text-stone-950">Respuestas</h3>
                @forelse ($answers as $answer)
                    <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">Dia {{ $answer->day }}</span>
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">Nivel {{ $answer->level }}</span>
                            <span class="text-xs font-semibold text-stone-500">{{ $answer->user->name ?? 'Usuario' }}</span>
                        </div>
                        <p class="mt-4 text-sm font-semibold leading-6 text-stone-950">{{ $answer->prompt }}</p>
                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-600">{{ $answer->answer }}</p>
                    </article>
                @empty
                    <div class="rounded-lg border border-stone-300 bg-white p-6 text-sm text-stone-600 shadow-sm">Todavia no hay respuestas guardadas.</div>
                @endforelse
            </div>
        </section>
    @endif
@endsection
