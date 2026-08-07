@extends('couple.partials.layout')

@section('content')
    @if (! $consentAccepted)
        @include('couple.partials.consent')
    @else
        <section class="mt-6 rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Biblioteca privada</p>
            <h2 class="mt-2 text-3xl font-semibold text-stone-950">Conceptos, rituales y posiciones guiadas</h2>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
                Referencias conceptuales inspiradas en preguntas de conexion, sensate focus, conversaciones de deseo y el Kamasutra como ritual de presencia. No es una lista grafica; es una guia sobria para explorar con consentimiento.
            </p>
        </section>

        <section class="mt-6">
            <h3 class="text-2xl font-semibold text-stone-950">Conceptos para conectar</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach ($intimacyConcepts as $concept)
                    <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">{{ $concept['level'] }}</span>
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">{{ $concept['context'] }}</span>
                        </div>
                        <h4 class="mt-4 text-xl font-semibold text-stone-950">{{ $concept['title'] }}</h4>
                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $concept['description'] }}</p>
                        <p class="mt-4 rounded-md bg-stone-50 p-4 text-sm leading-6 text-stone-700">{{ $concept['practice'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="mt-8">
            <h3 class="text-2xl font-semibold text-stone-950">Posiciones y cercania fisica</h3>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach ($positionSuggestions as $position)
                    <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">Nivel {{ $position['level'] }}</span>
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">{{ $position['privacy'] }}</span>
                        </div>
                        <h4 class="mt-4 text-xl font-semibold text-stone-950">{{ $position['title'] }}</h4>
                        <p class="mt-2 text-sm font-semibold text-stone-700">{{ $position['focus'] }}</p>
                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $position['guide'] }}</p>
                        <p class="mt-4 rounded-md bg-stone-50 p-4 text-sm leading-6 text-stone-700">{{ $position['consent_cue'] }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
