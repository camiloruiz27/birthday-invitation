@extends('couple.partials.layout')

@section('content')
    @if (! $consentAccepted)
        @include('couple.partials.consent')
    @else
        <section class="mt-6 rounded-lg border border-stone-300 bg-stone-950 p-6 text-white shadow-sm sm:p-8">
            <div class="max-w-4xl">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-300">Zona privada · Nivel 6</p>
                <h2 class="mt-3 text-4xl font-semibold leading-tight">Sensualidad intensa, directa y cuidada.</h2>
                <p class="mt-4 text-sm leading-6 text-stone-300">
                    Esta area es solo para espacios privados. Esta disenada para hablar de deseo sin rodeos, tomar decisiones juntos,
                    subir intensidad por pasos y cerrar con cuidado. La guia es adulta y accionable, sin contenido grafico.
                </p>
            </div>
        </section>

        <section class="mt-6 grid gap-4 lg:grid-cols-4">
            <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-stone-950">1. Privacidad</h3>
                <p class="mt-2 text-sm leading-6 text-stone-600">Cierren la puerta, apaguen distracciones y confirmen que nadie esta incomodo o presionado.</p>
            </article>
            <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-stone-950">2. Acuerdo</h3>
                <p class="mt-2 text-sm leading-6 text-stone-600">Cada uno dice: si quiero, tal vez, no quiero, y como pido pausa sin tener que explicar.</p>
            </article>
            <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-stone-950">3. Intensidad</h3>
                <p class="mt-2 text-sm leading-6 text-stone-600">Suban por escalones. Antes de avanzar: seguimos, bajamos o pausamos?</p>
            </article>
            <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                <h3 class="text-lg font-semibold text-stone-950">4. Cierre</h3>
                <p class="mt-2 text-sm leading-6 text-stone-600">Terminen con cuidado: agua, abrazo, palabras y una pregunta honesta sobre como se sintio.</p>
            </article>
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
            <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Frases listas</p>
                <h3 class="mt-2 text-2xl font-semibold text-stone-950">Para decir sin improvisar</h3>
                <div class="mt-5 space-y-3">
                    @foreach ([
                        'Quiero que esta noche sea intensa, pero tambien segura para los dos.',
                        'Dime si quieres que siga, que baje el ritmo o que cambie de energia.',
                        'Me gusta cuando me guias con palabras claras.',
                        'Hoy quiero sentirme deseado/a, escuchado/a y libre de pausar.',
                        'Antes de subir intensidad, dime un si claro y un limite claro.',
                    ] as $phrase)
                        <p class="rounded-md bg-stone-50 p-4 text-sm leading-6 text-stone-700">{{ $phrase }}</p>
                    @endforeach
                </div>
            </div>

            <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Guia intensa</p>
                <h3 class="mt-2 text-2xl font-semibold text-stone-950">Que hacer paso a paso</h3>
                <div class="mt-5 grid gap-3">
                    @foreach ($levelSixGuides as $guide)
                        <article class="rounded-md border border-stone-200 bg-stone-50 p-4">
                            <h4 class="font-semibold text-stone-950">{{ $guide['title'] }}</h4>
                            <p class="mt-2 text-sm leading-6 text-stone-600">{{ $guide['instruction'] }}</p>
                            <p class="mt-3 text-sm font-semibold leading-6 text-stone-800">{{ $guide['check'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="mt-8 rounded-lg border border-stone-300 bg-white p-6 shadow-sm" data-level-six-roulette>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Ruleta privada</p>
                    <h3 class="mt-2 text-3xl font-semibold text-stone-950">Ruleta por turnos</h3>
                    <p class="mt-3 text-sm leading-6 text-stone-600">
                        La persona indicada presiona el boton, la ruleta elige una actividad y el temporizador queda listo. El detalle se muestra solo cuando decidan abrirlo.
                    </p>
                </div>
                <button class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-level-six-reset>
                    <i data-lucide="rotate-cw" class="h-4 w-4"></i>
                    Reiniciar
                </button>
            </div>

            <script type="application/json" data-level-six-options>
                @json($levelSixRouletteOptions)
            </script>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-md border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Presiona</p>
                    <p class="mt-2 text-3xl font-semibold text-stone-950" data-level-six-player>Camilo</p>
                </div>
                <div class="rounded-md border border-stone-200 bg-stone-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Jugadas</p>
                    <p class="mt-2 text-3xl font-semibold text-stone-950" data-level-six-count>0</p>
                </div>
            </div>

            <div class="mt-5 grid gap-3 lg:grid-cols-[1fr_0.7fr]">
                <button class="inline-flex items-center justify-center gap-2 rounded-md bg-stone-950 px-5 py-4 text-base font-bold text-white hover:bg-stone-800" type="button" data-level-six-spin>
                    <i data-lucide="dices" class="h-5 w-5"></i>
                    Sacar actividad
                </button>
                <div class="rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-semibold text-stone-700" data-level-six-next>
                    Camilo presiona ahora.
                </div>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_0.75fr]">
                <article class="rounded-lg border border-stone-300 bg-stone-950 p-6 text-white min-h-[22rem]">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full border border-white/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-300" data-level-six-target>Sin destino</span>
                        <span class="rounded-full border border-white/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-300" data-level-six-type>Sin tipo</span>
                        <span class="rounded-full border border-white/20 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-300" data-level-six-duration>Sin tiempo detectado</span>
                    </div>
                    <h4 class="mt-5 text-3xl font-semibold leading-tight" data-level-six-title>Actividad pendiente</h4>
                    <p class="mt-4 text-sm leading-6 text-stone-300">La actividad saldra resumida aqui. Abran el detalle solo cuando ambos esten listos.</p>
                    <div class="mt-5 hidden rounded-md border border-white/10 bg-white/10 p-4" data-level-six-detail-wrap>
                        <p class="whitespace-pre-line text-sm leading-6 text-stone-200" data-level-six-description></p>
                    </div>
                    <button class="mt-5 hidden rounded-md bg-white px-4 py-3 text-sm font-bold text-stone-950 hover:bg-stone-100" type="button" data-level-six-toggle>
                        Mostrar actividad
                    </button>
                </article>

                <aside class="rounded-lg border border-stone-300 bg-stone-50 p-5">
                    <h4 class="text-lg font-semibold text-stone-950">Temporizador</h4>
                    <div class="mt-4 rounded-md bg-white p-4 text-center">
                        <div class="font-mono text-5xl font-semibold tracking-normal text-stone-950" data-level-six-timer-display>00:00</div>
                        <p class="mt-2 text-sm font-semibold text-stone-500" data-level-six-timer-state>Listo</p>
                    </div>
                    <label class="mt-4 block text-sm font-semibold text-stone-800">Segundos
                        <input class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950" type="number" min="5" step="5" value="60" data-level-six-timer-input>
                    </label>
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <button class="rounded-md bg-stone-950 px-3 py-2 text-sm font-bold text-white hover:bg-stone-800" type="button" data-level-six-timer-start>Iniciar</button>
                        <button class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-level-six-timer-pause>Pausar</button>
                        <button class="rounded-md border border-stone-300 bg-white px-3 py-2 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-level-six-timer-reset>Reiniciar</button>
                    </div>
                    <div class="mt-6 rounded-md border border-stone-200 bg-white p-4">
                        <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Siguiente</p>
                        <p class="mt-2 text-sm font-semibold leading-6 text-stone-800" data-level-six-next-summary>Camilo presiona para iniciar.</p>
                    </div>
                </aside>
            </div>
        </section>

        <section class="mt-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Cartas privadas</p>
                    <h3 class="mt-2 text-3xl font-semibold text-stone-950">Cartas de juego Nivel 6</h3>
                </div>
                <a class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" href="{{ route('couple-experience.timers') }}">
                    <i data-lucide="timer" class="h-4 w-4"></i>
                    Abrir cronometros
                </a>
            </div>

            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                @foreach ($levelSixRituals as $ritual)
                    @php($ritualProgress = $progress->get($ritual['key']))
                    <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-stone-950 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white">Nivel {{ $ritual['level'] }}</span>
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">{{ $ritual['privacy'] }}</span>
                            <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">{{ $ritual['type'] }}</span>
                            @if ($ritualProgress)
                                <span class="rounded-full border border-stone-950 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-950">{{ $ritualProgress->status === 'completed' ? 'Completada' : 'Pasada' }}</span>
                            @endif
                        </div>

                        <h4 class="mt-4 text-xl font-semibold text-stone-950">{{ $ritual['title'] }}</h4>
                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $ritual['prompt'] }}</p>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-widest text-stone-500">{{ $ritual['inspiration'] }}</p>

                        <div class="mt-5 grid gap-2 sm:grid-cols-2">
                            <form method="POST" action="{{ route('couple-experience.progress') }}">
                                @csrf
                                <input type="hidden" name="activity_key" value="{{ $ritual['key'] }}">
                                <input type="hidden" name="status" value="completed">
                                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-3 text-sm font-bold text-white hover:bg-stone-800" type="submit">
                                    <i data-lucide="check" class="h-4 w-4"></i>
                                    Completar
                                </button>
                            </form>
                            <form method="POST" action="{{ route('couple-experience.progress') }}">
                                @csrf
                                <input type="hidden" name="activity_key" value="{{ $ritual['key'] }}">
                                <input type="hidden" name="status" value="skipped">
                                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="submit">
                                    <i data-lucide="circle-pause" class="h-4 w-4"></i>
                                    Pasar
                                </button>
                            </form>
                        </div>

                        <form class="mt-4" method="POST" action="{{ route('couple-experience.answers') }}">
                            @csrf
                            <input type="hidden" name="activity_key" value="{{ $ritual['key'] }}">
                            <textarea class="min-h-24 w-full rounded-md border border-stone-300 bg-stone-50 px-3 py-3 text-sm" name="answer" placeholder="Guarden acuerdos, limites, palabra de pausa o como quieren cerrar..." required></textarea>
                            <button class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 hover:bg-stone-50" type="submit">
                                <i data-lucide="send" class="h-4 w-4"></i>
                                Guardar acuerdo
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
