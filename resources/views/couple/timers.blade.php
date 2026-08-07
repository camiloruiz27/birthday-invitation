@extends('couple.partials.layout')

@section('content')
    @if (! $consentAccepted)
        @include('couple.partials.consent')
    @else
        <section class="mt-6 rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Ritmo y turnos</p>
                    <h2 class="mt-2 text-3xl font-semibold text-stone-950">Cronometros con sonido</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-600">Usenlos para conversaciones, turnos de juego, pausas y cambios de intensidad.</p>
                </div>
                <button class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 px-4 py-3 text-sm font-bold transition" type="button" data-sound-toggle>
                    Sonido activado
                </button>
            </div>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-2">
            <article class="rounded-lg border border-stone-300 bg-white p-6 text-center shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Clasico</p>
                <div class="mt-6 font-mono text-7xl font-semibold tracking-normal text-stone-950" data-classic-display>00:00</div>
                <p class="mt-3 text-sm font-semibold text-stone-500" data-classic-state>Listo para iniciar</p>
                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                    <button class="rounded-md bg-stone-950 px-4 py-3 text-sm font-bold text-white hover:bg-stone-800" type="button" data-classic-start>Iniciar</button>
                    <button class="rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-classic-pause>Pausar</button>
                    <button class="rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-classic-reset>Reiniciar</button>
                </div>
            </article>

            <article class="rounded-lg border border-stone-300 bg-white p-6 text-center shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Express</p>
                <div class="mt-4 inline-flex rounded-full border border-stone-300 px-4 py-2 text-sm font-bold text-stone-700">
                    <span data-express-phase>Turno</span>
                    <span class="mx-2 text-stone-300">/</span>
                    <span data-express-round>Ronda 1</span>
                </div>
                <div class="mt-5 font-mono text-7xl font-semibold tracking-normal text-stone-950" data-express-display>45</div>
                <p class="mt-3 text-sm leading-6 text-stone-600">45 segundos de turno y 10 segundos para cambiar. El sonido marca cada cambio.</p>
                <div class="mt-7 grid gap-3 sm:grid-cols-3">
                    <button class="rounded-md bg-stone-950 px-4 py-3 text-sm font-bold text-white hover:bg-stone-800" type="button" data-express-start>Iniciar</button>
                    <button class="rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-express-pause>Pausar</button>
                    <button class="rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-express-reset>Reiniciar</button>
                </div>
            </article>
        </section>

        <section class="mt-6 rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
            <h3 class="text-xl font-semibold text-stone-950">Uso sugerido</h3>
            <div class="mt-4 grid gap-3 md:grid-cols-3">
                <p class="rounded-md bg-stone-50 p-4 text-sm leading-6 text-stone-600">Nivel 2: tres minutos para responder una pregunta sin interrumpirse.</p>
                <p class="rounded-md bg-stone-50 p-4 text-sm leading-6 text-stone-600">Nivel 3: turnos cortos de cumplidos, mirada o contacto suave.</p>
                <p class="rounded-md bg-stone-50 p-4 text-sm leading-6 text-stone-600">Nivel 4-5: privado, con palabra de pausa y cierre de cuidado.</p>
            </div>
        </section>
    @endif
@endsection
