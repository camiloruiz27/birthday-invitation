@extends('couple.partials.layout')

@section('content')
    @if (! $consentAccepted)
        @include('couple.partials.consent')
    @else
        <section class="mt-6 grid gap-6 lg:grid-cols-[0.95fr_1.05fr]">
            <form method="POST" action="{{ route('couple-experience.ai.suggest') }}" class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                @csrf
                <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Asistente IA</p>
                <h2 class="mt-2 text-3xl font-semibold text-stone-950">Que hacemos ahora?</h2>
                <p class="mt-3 text-sm leading-6 text-stone-600">Elige el contexto y la IA devuelve una frase, una pregunta, un juego corto, una actividad y una forma de cerrar.</p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-semibold text-stone-800">Dia
                        <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3" name="day" data-context-day>
                            @foreach ($days as $day)
                                <option value="{{ $day['day'] }}">Dia {{ $day['day'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-stone-800">Nivel
                        <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3" name="level" data-context-level>
                            @foreach ([2, 3, 4, 5] as $level)
                                <option value="{{ $level }}">Nivel {{ $level }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-stone-800">Lugar
                        <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3" name="location" data-context-location>
                            @foreach ($locations as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-stone-800">Estado
                        <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3" name="mood">
                            @foreach ($moods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-stone-800 sm:col-span-2">Intencion
                        <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3" name="intention">
                            @foreach ($intentions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-semibold text-stone-800 sm:col-span-2">Limites o contexto de hoy
                        <textarea class="mt-2 min-h-28 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-sm" name="limits" placeholder="Ej: estamos cansados, solo queremos algo suave, nada publico, queremos subir nivel despacio."></textarea>
                    </label>
                </div>

                <input type="hidden" name="activity_key" data-context-activity>

                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    <button class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-roulette>
                        <i data-lucide="rotate-cw" class="h-4 w-4"></i>
                        Ruleta
                    </button>
                    <button class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-dice>
                        <i data-lucide="dices" class="h-4 w-4"></i>
                        Dados
                    </button>
                    <button class="inline-flex items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-3 text-sm font-bold text-white hover:bg-stone-800" type="submit">
                        <i data-lucide="send" class="h-4 w-4"></i>
                        Sugerir
                    </button>
                </div>

                <div class="mt-4 hidden rounded-md border border-stone-300 bg-stone-50 p-4 text-sm leading-6 text-stone-700" data-game-output></div>
            </form>

            <div class="space-y-4">
                <section class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <h3 class="text-xl font-semibold text-stone-950">Juegos rapidos</h3>
                    <div class="mt-4 grid gap-3">
                        @foreach ($gameModes as $game)
                            <article class="rounded-md border border-stone-200 bg-stone-50 p-4">
                                <h4 class="font-semibold text-stone-950">{{ $game['title'] }}</h4>
                                <p class="mt-1 text-sm leading-6 text-stone-600">{{ $game['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <h3 class="text-xl font-semibold text-stone-950">Regla de privacidad</h3>
                    <p class="mt-2 text-sm leading-6 text-stone-600">Niveles 2 y 3 pueden funcionar en publico si son discretos. Niveles 4 y 5 solo deben usarse en espacios privados o claramente seguros para ambos.</p>
                </section>
            </div>
        </section>
    @endif
@endsection
