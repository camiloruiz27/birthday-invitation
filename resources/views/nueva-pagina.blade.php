<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Experiencia privada de aniversario en Cartagena.">
    <title>Aniversario en Cartagena</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f3f0ea] text-stone-950 antialiased">
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-950/80 px-4 backdrop-blur-sm" data-loading-overlay>
    <div class="w-full max-w-sm rounded-lg border border-white/10 bg-white p-6 text-center shadow-xl">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-stone-200 bg-stone-50">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-stone-300 border-t-stone-950"></span>
        </div>
        <h2 class="mt-5 text-xl font-semibold text-stone-950" data-loading-title>Preparando experiencia</h2>
        <p class="mt-2 text-sm leading-6 text-stone-600" data-loading-message>
            Estamos cargando la pagina.
        </p>
    </div>
</div>

@if (! $user)
    <main class="grid min-h-screen place-items-center px-4 py-10">
        <section class="grid w-full max-w-5xl overflow-hidden rounded-lg border border-stone-300 bg-white shadow-sm lg:grid-cols-[1.05fr_0.95fr]">
            <div class="bg-stone-950 p-8 text-white sm:p-10 lg:p-12">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1 text-xs font-bold uppercase tracking-widest text-stone-300">
                    <i data-lucide="sparkles" class="h-4 w-4"></i>
                    Cartagena privada
                </div>
                <h1 class="mt-8 max-w-xl text-5xl font-semibold leading-none sm:text-6xl">
                    4 dias para volver a elegirse
                </h1>
                <p class="mt-6 max-w-lg text-base leading-7 text-stone-300">
                    Una experiencia cerrada para conversar, jugar, conectar y subir intensidad con consentimiento claro.
                </p>
                <div class="mt-10 grid gap-3 text-sm text-stone-300 sm:grid-cols-3">
                    <div class="rounded-md border border-white/15 p-4">
                        <i data-lucide="calendar-days" class="h-5 w-5"></i>
                        <strong class="mt-3 block text-white">4 dias</strong>
                    </div>
                    <div class="rounded-md border border-white/15 p-4">
                        <i data-lucide="heart-handshake" class="h-5 w-5"></i>
                        <strong class="mt-3 block text-white">2 personas</strong>
                    </div>
                    <div class="rounded-md border border-white/15 p-4">
                        <i data-lucide="shield-check" class="h-5 w-5"></i>
                        <strong class="mt-3 block text-white">Privado</strong>
                    </div>
                </div>
            </div>

            <div class="p-7 sm:p-10 lg:p-12">
                <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Acceso cerrado</p>
                <h2 class="mt-3 text-3xl font-semibold text-stone-950">Entrar a la experiencia</h2>
                <p class="mt-4 text-sm leading-6 text-stone-600">
                    Usa una de las cuentas configuradas en la base de datos. No hay registro publico.
                </p>

                @if ($errors->any())
                    <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form class="mt-7 space-y-5" method="POST" action="{{ route('couple-experience.login') }}">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-stone-800" for="email">Correo</label>
                        <input class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950 outline-none transition focus:border-stone-950 focus:ring-2 focus:ring-stone-200" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-stone-800" for="password">Clave</label>
                        <input class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950 outline-none transition focus:border-stone-950 focus:ring-2 focus:ring-stone-200" id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-800" type="submit">
                        <i data-lucide="play" class="h-4 w-4"></i>
                        Entrar
                    </button>
                </form>
            </div>
        </section>
    </main>
@else
    @php
        $totalActivities = collect($activities)->sum(fn ($day) => count($day['activities']));
        $completedCount = $progress->filter(fn ($item) => $item->status === 'completed')->count();
        $progressPercent = $totalActivities > 0 ? (int) round(($completedCount / $totalActivities) * 100) : 0;
    @endphp

    <main class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <header class="sticky top-0 z-30 -mx-4 border-b border-stone-300 bg-[#f3f0ea]/95 px-4 py-4 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Aniversario en Cartagena</p>
                    <h1 class="mt-1 text-2xl font-semibold leading-tight text-stone-950 sm:text-3xl">
                        Hola, {{ $user->name }}
                    </h1>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <a class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-bold text-stone-900 transition hover:bg-stone-50" href="#assistant-panel">
                        <i data-lucide="wand-2" class="h-4 w-4"></i>
                        Asistente
                    </a>
                    <a class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-bold text-stone-900 transition hover:bg-stone-50" href="#activity-board">
                        <i data-lucide="dices" class="h-4 w-4"></i>
                        Juegos
                    </a>
                    <form method="POST" action="{{ route('couple-experience.logout') }}">
                        @csrf
                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-2 text-sm font-bold text-white transition hover:bg-stone-800 sm:w-auto" type="submit">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            Salir
                        </button>
                    </form>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="mt-6 grid gap-4 lg:grid-cols-[1.45fr_0.55fr]">
            <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm sm:p-8 lg:p-10">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-2 rounded-full border border-stone-300 bg-stone-50 px-3 py-1 text-xs font-bold uppercase tracking-widest text-stone-600">
                        <i data-lucide="heart" class="h-4 w-4"></i>
                        Modo viaje de pareja
                    </span>
                    <span class="inline-flex rounded-full bg-stone-950 px-3 py-1 text-xs font-bold uppercase tracking-widest text-white">
                        Niveles 2-5
                    </span>
                </div>
                <h2 class="mt-6 max-w-4xl text-5xl font-semibold leading-none text-stone-950 sm:text-7xl">
                    Conexion, deseo y juego sin prisa
                </h2>
                <p class="mt-6 max-w-2xl text-base leading-7 text-stone-600">
                    Un tablero para decidir que hacer, que decir y como avanzar segun el lugar, la energia del momento y los limites de ambos.
                </p>
            </div>

            <aside class="grid gap-3">
                <div class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Progreso total</p>
                    <strong class="mt-2 block text-4xl font-semibold">{{ $progressPercent }}%</strong>
                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-stone-200">
                        <span class="block h-full bg-stone-950" style="width: {{ $progressPercent }}%"></span>
                    </div>
                    <p class="mt-3 text-sm text-stone-600">{{ $completedCount }} de {{ $totalActivities }} actividades completadas.</p>
                </div>
                <div class="grid grid-cols-3 gap-3 lg:grid-cols-1">
                    <div class="rounded-lg border border-stone-300 bg-white p-4 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Dias</p>
                        <strong class="mt-2 block text-2xl">4</strong>
                    </div>
                    <div class="rounded-lg border border-stone-300 bg-white p-4 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Guias IA</p>
                        <strong class="mt-2 block text-2xl">{{ $aiSuggestions->count() }}</strong>
                    </div>
                    <div class="rounded-lg border border-stone-300 bg-white p-4 shadow-sm">
                        <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Respuestas</p>
                        <strong class="mt-2 block text-2xl">{{ $answers->count() }}</strong>
                    </div>
                </div>
            </aside>
        </section>

        @if (! $consentAccepted)
            <section class="mt-6 rounded-lg border border-stone-300 bg-white p-6 shadow-sm lg:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-stone-950 text-white">
                        <i data-lucide="shield-check" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-semibold text-stone-950">Antes de empezar</h2>
                        <p class="mt-4 max-w-3xl text-base leading-7 text-stone-600">
                            Esta experiencia incluye contenido emocional, sensual y sexual para adultos. Nada es obligatorio. Pasar, ajustar o detener un reto siempre es una respuesta valida.
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-md border border-stone-200 bg-stone-50 p-5">
                        <strong class="block text-stone-950">Si claro</strong>
                        <p class="mt-2 text-sm leading-6 text-stone-600">Lo que ambos quieran explorar con entusiasmo.</p>
                    </div>
                    <div class="rounded-md border border-stone-200 bg-stone-50 p-5">
                        <strong class="block text-stone-950">Tal vez</strong>
                        <p class="mt-2 text-sm leading-6 text-stone-600">Lo que necesita conversacion, ajuste o mas calma.</p>
                    </div>
                    <div class="rounded-md border border-stone-200 bg-stone-50 p-5">
                        <strong class="block text-stone-950">No</strong>
                        <p class="mt-2 text-sm leading-6 text-stone-600">Lo que se respeta de inmediato y no se insiste.</p>
                    </div>
                </div>

                <form class="mt-6" method="POST" action="{{ route('couple-experience.consent') }}">
                    @csrf
                    <label class="flex items-start gap-3 text-sm leading-6 text-stone-700">
                        <input class="mt-1 h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-400" type="checkbox" name="accept_rules" value="1" required>
                        <span>Confirmo que somos adultos, que esta experiencia es consensuada y que cualquiera puede pausar o pasar un reto.</span>
                    </label>
                    <button class="mt-5 inline-flex items-center gap-2 rounded-md bg-stone-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-800" type="submit">
                        <i data-lucide="check" class="h-4 w-4"></i>
                        Aceptar e iniciar
                    </button>
                </form>
            </section>
        @else
            <section id="assistant-panel" class="mt-6 grid gap-4 lg:grid-cols-[0.82fr_1.18fr]">
                <div class="rounded-lg border border-stone-300 bg-stone-950 p-6 text-white shadow-sm">
                    <div class="inline-flex h-11 w-11 items-center justify-center rounded-md bg-white text-stone-950">
                        <i data-lucide="brain" class="h-5 w-5"></i>
                    </div>
                    <p class="mt-6 text-xs font-bold uppercase tracking-widest text-stone-400">Asistente de momento</p>
                    <h2 class="mt-3 text-4xl font-semibold leading-none">Donde estamos ahora?</h2>
                    <p class="mt-5 text-sm leading-6 text-stone-300">
                        Elige contexto, energia e intencion. La IA o el fallback local te devuelve una guia lista para hacer en ese momento.
                    </p>
                    <div class="mt-6 rounded-md border border-white/15 p-4 text-sm leading-6 text-stone-300">
                        Niveles 4 y 5 se tratan como privados, graduales y con opcion explicita de pausar.
                    </div>
                </div>

                <form class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm sm:p-6" method="POST" action="{{ route('couple-experience.ai.suggest') }}">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-semibold text-stone-800">
                            Dia
                            <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950" name="day" data-context-day>
                                @foreach ($activities as $day)
                                    <option value="{{ $day['day'] }}">Dia {{ $day['day'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm font-semibold text-stone-800">
                            Nivel
                            <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950" name="level" data-context-level>
                                <option value="2">Nivel 2</option>
                                <option value="3">Nivel 3</option>
                                <option value="4">Nivel 4</option>
                                <option value="5">Nivel 5</option>
                            </select>
                        </label>
                        <label class="block text-sm font-semibold text-stone-800">
                            Lugar
                            <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950" name="location" data-context-location>
                                @foreach ($locations as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm font-semibold text-stone-800">
                            Estado
                            <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950" name="mood" data-context-mood>
                                @foreach ($moods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block text-sm font-semibold text-stone-800 sm:col-span-2">
                            Intencion
                            <select class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950" name="intention" data-context-intention>
                                @foreach ($intentions as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <label class="mt-4 block text-sm font-semibold text-stone-800">
                        Limites o notas del momento
                        <textarea class="mt-2 min-h-20 w-full resize-y rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950" name="limits" placeholder="Ej: estamos en publico, queremos algo suave, hoy preferimos no subir de nivel..."></textarea>
                    </label>

                    <input type="hidden" name="activity_key" data-context-activity>
                    <div class="mt-5 grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                        <button class="inline-flex items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-3 text-sm font-bold text-white transition hover:bg-stone-800" type="submit">
                            <i data-lucide="wand-2" class="h-4 w-4"></i>
                            Sugerir con IA
                        </button>
                        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 transition hover:bg-stone-50" type="button" data-roulette>
                            <i data-lucide="rotate-cw" class="h-4 w-4"></i>
                            Ruleta
                        </button>
                        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 transition hover:bg-stone-50" type="button" data-dice>
                            <i data-lucide="dices" class="h-4 w-4"></i>
                            Dados
                        </button>
                        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 transition hover:bg-stone-50 disabled:opacity-60" type="button" data-timer-start>
                            <i data-lucide="timer" class="h-4 w-4"></i>
                            Timer 3 min
                        </button>
                    </div>
                    <p class="mt-4 hidden rounded-md border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700" data-game-output></p>
                </form>
            </section>

            <section class="mt-4 grid gap-3 md:grid-cols-5">
                @foreach ($gameModes as $game)
                    <article class="rounded-lg border border-stone-300 bg-white p-4 shadow-sm">
                        <h3 class="flex items-center gap-2 text-sm font-bold text-stone-950">
                            <i data-lucide="sparkles" class="h-4 w-4 text-stone-500"></i>
                            {{ $game['title'] }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-stone-600">{{ $game['description'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="mt-6 grid gap-4 lg:grid-cols-2">
                <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-stone-950 text-white">
                            <i data-lucide="timer" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Cronometro clasico</p>
                            <h2 class="text-2xl font-semibold text-stone-950">Tiempo libre</h2>
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-stone-600">
                        Para conversaciones, miradas, masajes, pausas o cualquier reto que quieran medir sin estructura.
                    </p>

                    <div class="mt-6 rounded-lg border border-stone-200 bg-stone-50 p-5 text-center">
                        <div class="text-6xl font-semibold tabular-nums text-stone-950" data-classic-display>00:00</div>
                        <p class="mt-2 text-sm font-semibold text-stone-500" data-classic-state>Listo para iniciar</p>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <button class="rounded-md bg-stone-950 px-4 py-3 text-sm font-bold text-white transition hover:bg-stone-800" type="button" data-classic-start>Iniciar</button>
                        <button class="rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 transition hover:bg-stone-50" type="button" data-classic-pause>Pausar</button>
                        <button class="rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 transition hover:bg-stone-50" type="button" data-classic-reset>Reiniciar</button>
                    </div>
                    <button class="mt-3 w-full rounded-md border border-stone-300 bg-white px-4 py-3 text-sm font-bold text-stone-900 transition hover:bg-stone-50" type="button" data-sound-toggle>
                        Sonido activado
                    </button>
                </div>

                <div class="rounded-lg border border-stone-300 bg-stone-950 p-6 text-white shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-white text-stone-950">
                            <i data-lucide="rotate-cw" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-stone-400">Cronometro express</p>
                            <h2 class="text-2xl font-semibold">45 + 10</h2>
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-stone-300">
                        Cada turno dura 45 segundos. Luego hay 10 segundos para cambiar, respirar o decidir si siguen.
                    </p>

                    <div class="mt-6 rounded-lg border border-white/15 p-5 text-center">
                        <div class="text-sm font-bold uppercase tracking-widest text-stone-400" data-express-phase>Turno</div>
                        <div class="mt-2 text-6xl font-semibold tabular-nums" data-express-display>45</div>
                        <p class="mt-2 text-sm font-semibold text-stone-300" data-express-round>Ronda 1</p>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <button class="rounded-md bg-white px-4 py-3 text-sm font-bold text-stone-950 transition hover:bg-stone-100" type="button" data-express-start>Iniciar</button>
                        <button class="rounded-md border border-white/20 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10" type="button" data-express-pause>Pausar</button>
                        <button class="rounded-md border border-white/20 px-4 py-3 text-sm font-bold text-white transition hover:bg-white/10" type="button" data-express-reset>Reiniciar</button>
                    </div>
                    <p class="mt-3 text-center text-xs font-semibold uppercase tracking-widest text-stone-400">
                        Usa el mismo sonido del cronometro clasico
                    </p>
                </div>
            </section>

            <section class="mt-6 grid gap-4 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-stone-950 text-white">
                            <i data-lucide="message-circle-heart" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Biblioteca intima</p>
                            <h2 class="text-2xl font-semibold text-stone-950">Conceptos para conectar</h2>
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-stone-600">
                        Inspirado en ideas publicas de conexion, presencia y ritual del Kamasutra, sin copiar textos. Usenlo como guia, no como obligacion.
                    </p>

                    <div class="mt-5 grid gap-3">
                        @foreach ($intimacyConcepts as $concept)
                            <article class="rounded-md border border-stone-200 bg-stone-50 p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-stone-950">{{ $concept['title'] }}</h3>
                                    <span class="rounded-full bg-white px-2 py-1 text-xs font-bold uppercase tracking-wide text-stone-500">{{ $concept['level'] }}</span>
                                    <span class="rounded-full bg-white px-2 py-1 text-xs font-bold uppercase tracking-wide text-stone-500">{{ $concept['context'] }}</span>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-stone-600">{{ $concept['description'] }}</p>
                                <p class="mt-3 rounded-md bg-white px-3 py-2 text-sm leading-6 text-stone-700"><strong>Practica:</strong> {{ $concept['practice'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-stone-950 text-white">
                            <i data-lucide="heart-handshake" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Guia corporal</p>
                            <h2 class="text-2xl font-semibold text-stone-950">Sugerencias de posiciones</h2>
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-stone-600">
                        Ideas sobrias para orientar cercania, ritmo y comunicacion. Los niveles 4 y 5 son solo para espacios privados y con acuerdo claro.
                    </p>

                    <div class="mt-5 grid gap-3">
                        @foreach ($positionSuggestions as $position)
                            <article class="rounded-md border border-stone-200 bg-stone-50 p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-stone-950">{{ $position['title'] }}</h3>
                                    <span class="rounded-full bg-white px-2 py-1 text-xs font-bold uppercase tracking-wide text-stone-500">Nivel {{ $position['level'] }}</span>
                                    <span class="rounded-full bg-white px-2 py-1 text-xs font-bold uppercase tracking-wide text-stone-500">{{ $position['privacy'] }}</span>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-stone-600"><strong>Enfoque:</strong> {{ $position['focus'] }}</p>
                                <p class="mt-2 text-sm leading-6 text-stone-600">{{ $position['guide'] }}</p>
                                <p class="mt-3 rounded-md bg-white px-3 py-2 text-sm leading-6 text-stone-700"><strong>Chequeo:</strong> {{ $position['consent_cue'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <nav id="activity-board" class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4" aria-label="Dias del viaje">
                @foreach ($activities as $day)
                    <button class="rounded-lg border border-stone-300 bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:bg-stone-50 data-[active=true]:border-stone-950 data-[active=true]:bg-stone-950 data-[active=true]:text-white" type="button" data-day-tab="{{ $day['day'] }}">
                        <strong class="block text-sm">Dia {{ $day['day'] }}</strong>
                        <span class="mt-1 block text-xs opacity-75">{{ $day['title'] }}</span>
                    </button>
                @endforeach
            </nav>

            @foreach ($activities as $day)
                @php
                    $completedInDay = collect($day['activities'])
                        ->filter(fn ($activity) => optional($progress->get($activity['key']))->status === 'completed')
                        ->count();
                    $percentage = (int) round(($completedInDay / count($day['activities'])) * 100);
                @endphp

                <section class="hidden" data-day-panel="{{ $day['day'] }}">
                    <div class="mt-6 rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Dia {{ $day['day'] }}</p>
                                <h2 class="mt-2 text-3xl font-semibold text-stone-950">{{ $day['title'] }}</h2>
                                <p class="mt-3 max-w-3xl text-base leading-7 text-stone-600">{{ $day['setting'] }}</p>
                            </div>
                            <div class="shrink-0 rounded-md border border-stone-200 bg-stone-50 px-4 py-3 text-sm font-bold text-stone-700">
                                {{ $completedInDay }} / {{ count($day['activities']) }} completadas
                            </div>
                        </div>
                        <div class="mt-5 h-2 w-full overflow-hidden rounded-full bg-stone-200" aria-label="Progreso del dia">
                            <span class="block h-full bg-stone-950" style="width: {{ $percentage }}%"></span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 lg:grid-cols-2">
                        @foreach ($day['activities'] as $activity)
                            @php
                                $activityProgress = $progress->get($activity['key']);
                                $status = optional($activityProgress)->status;
                            @endphp

                            <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md data-[status=completed]:border-emerald-300 data-[status=completed]:bg-emerald-50 data-[status=skipped]:opacity-70" data-activity-card data-status="{{ $status ?: 'pending' }}" data-day="{{ $activity['day'] }}" data-level="{{ $activity['level'] }}" data-location="{{ $activity['location'] }}" data-privacy="{{ $activity['privacy'] }}" data-key="{{ $activity['key'] }}">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <span class="inline-flex rounded-full border border-stone-300 bg-stone-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-stone-700">
                                            Nivel {{ $activity['level'] }}
                                        </span>
                                        <h3 class="mt-3 text-xl font-semibold text-stone-950">{{ $activity['title'] }}</h3>
                                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wide text-stone-500">
                                            <span class="inline-flex items-center gap-1"><i data-lucide="map-pin" class="h-3.5 w-3.5"></i>{{ $locations[$activity['location']] ?? $activity['location'] }}</span>
                                            <span>{{ $activity['type'] }}</span>
                                            <span>{{ $activity['privacy'] }}</span>
                                        </div>
                                    </div>
                                    @if ($status)
                                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold uppercase tracking-wide text-stone-700">
                                            {{ $status === 'completed' ? 'Completado' : 'Pasado' }}
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-4 text-base leading-7 text-stone-600">{{ $activity['prompt'] }}</p>
                                <p class="mt-3 rounded-md bg-stone-50 px-3 py-2 text-sm leading-6 text-stone-500">Inspiracion: {{ $activity['inspiration'] }}</p>

                                <div class="mt-5 flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('couple-experience.progress') }}">
                                        @csrf
                                        <input type="hidden" name="activity_key" value="{{ $activity['key'] }}">
                                        <input type="hidden" name="status" value="completed">
                                        <button class="inline-flex items-center gap-2 rounded-md bg-stone-950 px-4 py-2 text-sm font-bold text-white transition hover:bg-stone-800" type="submit"><i data-lucide="check" class="h-4 w-4"></i>Completar</button>
                                    </form>

                                    <form method="POST" action="{{ route('couple-experience.progress') }}">
                                        @csrf
                                        <input type="hidden" name="activity_key" value="{{ $activity['key'] }}">
                                        <input type="hidden" name="status" value="skipped">
                                        <button class="inline-flex items-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-bold text-stone-900 transition hover:bg-stone-50" type="submit"><i data-lucide="circle-pause" class="h-4 w-4"></i>Pasar</button>
                                    </form>

                                    <button class="inline-flex items-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-bold text-stone-900 transition hover:bg-stone-50" type="button" data-next-card><i data-lucide="rotate-cw" class="h-4 w-4"></i>Siguiente</button>
                                    <button class="inline-flex items-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-bold text-stone-900 transition hover:bg-stone-50" type="button" data-use-for-ai><i data-lucide="wand-2" class="h-4 w-4"></i>Usar IA</button>
                                </div>

                                <form class="mt-5 border-t border-stone-200 pt-5" method="POST" action="{{ route('couple-experience.answers') }}">
                                    @csrf
                                    <input type="hidden" name="activity_key" value="{{ $activity['key'] }}">
                                    <label class="block text-sm font-semibold text-stone-800" for="answer-{{ $activity['key'] }}">Respuesta compartida</label>
                                    <textarea class="mt-2 min-h-28 w-full resize-y rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950 outline-none transition focus:border-stone-950 focus:ring-2 focus:ring-stone-200" id="answer-{{ $activity['key'] }}" name="answer" placeholder="Escriban aqui si quieren guardar una respuesta para verla juntos..." required></textarea>
                                    <button class="mt-3 inline-flex items-center gap-2 rounded-md bg-stone-800 px-4 py-2 text-sm font-bold text-white transition hover:bg-stone-700" type="submit">
                                        <i data-lucide="send" class="h-4 w-4"></i>
                                        Guardar respuesta
                                    </button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <section class="mt-6 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Guias generadas</p>
                    <h2 class="mt-2 text-3xl font-semibold text-stone-950">Sugerencias IA guardadas</h2>
                    <div class="mt-5 grid gap-4">
                        @forelse ($aiSuggestions as $suggestion)
                            @php($guide = $suggestion->suggestion)
                            <article class="rounded-md border border-stone-200 bg-stone-50 p-4">
                                <div class="flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wide text-stone-500">
                                    <span>{{ $suggestion->user->name ?? 'Usuario' }}</span>
                                    <span>Dia {{ $suggestion->day }}</span>
                                    <span>Nivel {{ $suggestion->level }}</span>
                                    <span>{{ $locations[$suggestion->location] ?? $suggestion->location }}</span>
                                    <span>{{ $suggestion->status }}</span>
                                </div>
                                <dl class="mt-4 grid gap-3 text-sm leading-6 text-stone-700">
                                    <div><dt class="font-bold text-stone-950">Frase inicial</dt><dd>{{ $guide['opening_phrase'] ?? '' }}</dd></div>
                                    <div><dt class="font-bold text-stone-950">Pregunta</dt><dd>{{ $guide['connection_question'] ?? '' }}</dd></div>
                                    <div><dt class="font-bold text-stone-950">Juego corto</dt><dd>{{ $guide['short_game'] ?? '' }}</dd></div>
                                    <div><dt class="font-bold text-stone-950">Actividad</dt><dd>{{ $guide['main_activity'] ?? '' }}</dd></div>
                                    <div><dt class="font-bold text-stone-950">Cierre o pausa</dt><dd>{{ $guide['closing_or_pause'] ?? '' }}</dd></div>
                                </dl>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('couple-experience.ai.status', $suggestion->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="completed">
                                        <button class="inline-flex items-center gap-2 rounded-md bg-stone-950 px-4 py-2 text-sm font-bold text-white" type="submit"><i data-lucide="check" class="h-4 w-4"></i>Completar</button>
                                    </form>
                                    <form method="POST" action="{{ route('couple-experience.ai.status', $suggestion->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="dismissed">
                                        <button class="inline-flex items-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-bold text-stone-900" type="submit"><i data-lucide="x" class="h-4 w-4"></i>Descartar</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <article class="rounded-md border border-stone-200 bg-stone-50 p-4">
                                <p class="text-stone-600">Todavia no hay sugerencias IA guardadas.</p>
                            </article>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-stone-300 bg-white p-6 shadow-sm">
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Historial compartido</p>
                    <h2 class="mt-2 text-3xl font-semibold text-stone-950">Respuestas</h2>
                    <p class="mt-3 text-sm leading-6 text-stone-600">
                        Estas respuestas quedan guardadas en texto normal y pueden verlas ambos usuarios.
                    </p>

                    <div class="mt-5 grid gap-3">
                        @forelse ($answers as $answer)
                            <article class="rounded-md border border-stone-200 bg-stone-50 p-4">
                                <div class="flex flex-wrap gap-2 text-xs font-bold uppercase tracking-wide text-stone-500">
                                    <span>{{ $answer->user->name ?? 'Usuario' }}</span>
                                    <span>Dia {{ $answer->day }}</span>
                                    <span>Nivel {{ $answer->level }}</span>
                                </div>
                                <p class="mt-3 text-sm leading-6 text-stone-700"><strong>{{ $answer->prompt }}</strong></p>
                                <p class="mt-2 text-base leading-7 text-stone-600">{{ $answer->answer }}</p>
                            </article>
                        @empty
                            <article class="rounded-md border border-stone-200 bg-stone-50 p-4">
                                <p class="text-stone-600">Todavia no hay respuestas guardadas.</p>
                            </article>
                        @endforelse
                    </div>
                </div>
            </section>
        @endif
    </main>
@endif

<script>
    const loadingOverlay = document.querySelector('[data-loading-overlay]');
    const loadingTitle = document.querySelector('[data-loading-title]');
    const loadingMessage = document.querySelector('[data-loading-message]');
    const tabs = Array.from(document.querySelectorAll('[data-day-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-day-panel]'));
    const activityCards = Array.from(document.querySelectorAll('[data-activity-card]'));
    const contextDay = document.querySelector('[data-context-day]');
    const contextLevel = document.querySelector('[data-context-level]');
    const contextLocation = document.querySelector('[data-context-location]');
    const contextActivity = document.querySelector('[data-context-activity]');
    const gameOutput = document.querySelector('[data-game-output]');
    const savedDay = window.localStorage.getItem('coupleExperienceDay') || '1';
    const classicDisplay = document.querySelector('[data-classic-display]');
    const classicState = document.querySelector('[data-classic-state]');
    const classicStart = document.querySelector('[data-classic-start]');
    const classicPause = document.querySelector('[data-classic-pause]');
    const classicReset = document.querySelector('[data-classic-reset]');
    const expressPhase = document.querySelector('[data-express-phase]');
    const expressDisplay = document.querySelector('[data-express-display]');
    const expressRound = document.querySelector('[data-express-round]');
    const expressStart = document.querySelector('[data-express-start]');
    const expressPause = document.querySelector('[data-express-pause]');
    const expressReset = document.querySelector('[data-express-reset]');
    const soundToggle = document.querySelector('[data-sound-toggle]');
    let classicInterval = null;
    let classicSeconds = 0;
    let expressInterval = null;
    let expressSeconds = 45;
    let expressIsChange = false;
    let expressRoundCount = 1;
    let soundEnabled = window.localStorage.getItem('coupleTimerSound') !== 'off';
    let audioContext = null;

    function showLoading(title = 'Procesando', message = 'Un momento, estamos guardando los cambios.') {
        if (!loadingOverlay) return;
        if (loadingTitle) loadingTitle.textContent = title;
        if (loadingMessage) loadingMessage.textContent = message;
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');
    }

    function hideLoading() {
        if (!loadingOverlay) return;
        loadingOverlay.classList.add('hidden');
        loadingOverlay.classList.remove('flex');
    }

    showLoading('Cargando experiencia', 'Estamos preparando el tablero privado.');
    window.addEventListener('load', () => {
        setTimeout(hideLoading, 250);
    });
    window.addEventListener('pageshow', hideLoading);

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            const action = form.getAttribute('action') || '';
            if (action.includes('/login')) {
                showLoading('Validando acceso', 'Estamos verificando tus datos.');
                return;
            }
            if (action.includes('/logout')) {
                showLoading('Cerrando sesion', 'Estamos saliendo de forma segura.');
                return;
            }
            if (action.includes('/consent')) {
                showLoading('Guardando consentimiento', 'Estamos habilitando la experiencia.');
                return;
            }
            if (action.includes('/ai/suggest')) {
                showLoading('Generando guia', 'La IA esta preparando una sugerencia para este momento.');
                return;
            }
            if (action.includes('/answers')) {
                showLoading('Guardando respuesta', 'Estamos guardando esto para verlo juntos.');
                return;
            }
            if (action.includes('/progress')) {
                showLoading('Actualizando progreso', 'Estamos marcando el reto.');
                return;
            }
            showLoading();
        });
    });

    function activateDay(day) {
        tabs.forEach(tab => {
            const active = tab.dataset.dayTab === day;
            tab.dataset.active = active ? 'true' : 'false';
        });

        panels.forEach(panel => {
            panel.classList.toggle('hidden', panel.dataset.dayPanel !== day);
        });

        if (contextDay) contextDay.value = day;
        window.localStorage.setItem('coupleExperienceDay', day);
        filterActivities();
    }

    function filterActivities() {
        if (!contextDay || !contextLevel || !contextLocation) return;

        const day = contextDay.value;
        const level = contextLevel.value;
        const location = contextLocation.value;
        let visibleCount = 0;

        activityCards.forEach(card => {
            const matchesDay = card.dataset.day === day;
            const matchesLevel = card.dataset.level === level;
            const matchesLocation = card.dataset.location === location || card.dataset.privacy === 'privado';
            const visible = matchesDay && matchesLevel && matchesLocation;
            card.classList.toggle('hidden', !visible);
            if (visible) visibleCount++;
        });

        if (visibleCount === 0) {
            activityCards.forEach(card => {
                const visible = card.dataset.day === day && card.dataset.level === level;
                card.classList.toggle('hidden', !visible);
            });
        }
    }

    function showGameOutput(message) {
        if (!gameOutput) return;
        gameOutput.textContent = message;
        gameOutput.classList.remove('hidden');
    }

    function pickRandom(values) {
        return values[Math.floor(Math.random() * values.length)];
    }

    function formatMinutes(totalSeconds) {
        const minutes = Math.floor(totalSeconds / 60);
        const seconds = totalSeconds % 60;
        return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    function updateClassic() {
        if (classicDisplay) classicDisplay.textContent = formatMinutes(classicSeconds);
    }

    function updateExpress() {
        if (expressDisplay) expressDisplay.textContent = String(expressSeconds).padStart(2, '0');
        if (expressPhase) expressPhase.textContent = expressIsChange ? 'Cambio' : 'Turno';
        if (expressRound) expressRound.textContent = `Ronda ${expressRoundCount}`;
    }

    function updateSoundToggle() {
        if (!soundToggle) return;
        soundToggle.textContent = soundEnabled ? 'Sonido activado' : 'Sonido desactivado';
        soundToggle.classList.toggle('bg-stone-950', soundEnabled);
        soundToggle.classList.toggle('text-white', soundEnabled);
        soundToggle.classList.toggle('bg-white', !soundEnabled);
        soundToggle.classList.toggle('text-stone-900', !soundEnabled);
    }

    function getAudioContext() {
        if (!audioContext) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return null;
            audioContext = new AudioContextClass();
        }
        if (audioContext.state === 'suspended') {
            audioContext.resume();
        }
        return audioContext;
    }

    function playTone(frequency = 660, duration = 0.12, volume = 0.08) {
        if (!soundEnabled) return;
        const context = getAudioContext();
        if (!context) return;

        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const now = context.currentTime;

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(frequency, now);
        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(volume, now + 0.015);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + duration);

        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.start(now);
        oscillator.stop(now + duration + 0.02);
    }

    function playStartSound() {
        playTone(620, 0.1, 0.07);
    }

    function playPauseSound() {
        playTone(420, 0.12, 0.06);
    }

    function playResetSound() {
        playTone(300, 0.12, 0.06);
    }

    function playPhaseSound() {
        playTone(760, 0.12, 0.08);
        setTimeout(() => playTone(980, 0.16, 0.08), 140);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            showLoading('Cambiando de dia', 'Estamos organizando las actividades.');
            activateDay(tab.dataset.dayTab);
            setTimeout(hideLoading, 180);
        });
    });

    if (tabs.length) {
        activateDay(tabs.some(tab => tab.dataset.dayTab === savedDay) ? savedDay : '1');
    }

    [contextDay, contextLevel, contextLocation].forEach(control => {
        if (control) control.addEventListener('change', filterActivities);
    });

    document.querySelectorAll('[data-next-card]').forEach(button => {
        button.addEventListener('click', () => {
            const current = button.closest('[data-activity-card]');
            const cards = activityCards.filter(card => !card.classList.contains('hidden'));
            const next = cards[cards.indexOf(current) + 1] || cards[0];
            showLoading('Buscando reto', 'Estamos moviendonos al siguiente juego.');
            next.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(hideLoading, 350);
        });
    });

    document.querySelectorAll('[data-use-for-ai]').forEach(button => {
        button.addEventListener('click', () => {
            const card = button.closest('[data-activity-card]');
            if (!card || !contextActivity) return;
            contextActivity.value = card.dataset.key;
            if (contextDay) contextDay.value = card.dataset.day;
            if (contextLevel) contextLevel.value = card.dataset.level;
            if (contextLocation) contextLocation.value = card.dataset.location;
            showGameOutput('Actividad seleccionada como contexto para IA. Ahora puedes pedir una guia personalizada.');
            showLoading('Preparando contexto', 'Estamos conectando esta actividad con el asistente.');
            window.scrollTo({ top: 0, behavior: 'smooth' });
            filterActivities();
            setTimeout(hideLoading, 350);
        });
    });

    const roulette = document.querySelector('[data-roulette]');
    if (roulette && contextDay && contextLevel && contextLocation) {
        roulette.addEventListener('click', () => {
            contextDay.value = pickRandom(['1', '2', '3', '4']);
            contextLevel.value = pickRandom(['2', '3', '4', '5']);
            contextLocation.value = pickRandom(['hotel', 'playa', 'restaurante', 'caminata', 'transporte', 'noche', 'descanso']);
            showLoading('Girando ruleta', 'Estamos eligiendo una combinacion para ustedes.');
            activateDay(contextDay.value);
            showGameOutput(`Ruleta: Dia ${contextDay.value}, nivel ${contextLevel.value}, lugar ${contextLocation.options[contextLocation.selectedIndex].text}.`);
            setTimeout(hideLoading, 350);
        });
    }

    const dice = document.querySelector('[data-dice]');
    if (dice) {
        dice.addEventListener('click', () => {
            const intensities = ['suave', 'jugueton', 'coqueto', 'intenso privado', 'pausa consciente', 'cierre con cuidado'];
            const actions = ['hablar', 'mirar', 'tocar con permiso', 'guiar', 'preguntar limites', 'cerrar'];
            showLoading('Lanzando dados', 'Estamos combinando intensidad y accion.');
            showGameOutput(`Dados: intensidad ${pickRandom(intensities)} + accion ${pickRandom(actions)}.`);
            setTimeout(hideLoading, 350);
        });
    }

    const timerStart = document.querySelector('[data-timer-start]');
    if (timerStart) {
        timerStart.addEventListener('click', () => {
            let seconds = 180;
            timerStart.disabled = true;
            const interval = setInterval(() => {
                const minutes = Math.floor(seconds / 60);
                const rest = String(seconds % 60).padStart(2, '0');
                showGameOutput(`Timer de conexion: ${minutes}:${rest}. Mantengan el foco en una sola actividad.`);
                seconds--;
                if (seconds < 0) {
                    clearInterval(interval);
                    timerStart.disabled = false;
                    showGameOutput('Timer terminado. Cierren diciendo: esto me acerco a ti porque...');
                }
            }, 1000);
        });
    }

    if (classicStart) {
        classicStart.addEventListener('click', () => {
            if (classicInterval) return;
            playStartSound();
            if (classicState) classicState.textContent = 'Cronometro activo';
            classicInterval = setInterval(() => {
                classicSeconds++;
                updateClassic();
            }, 1000);
        });
    }

    if (classicPause) {
        classicPause.addEventListener('click', () => {
            if (classicInterval) {
                playPauseSound();
                clearInterval(classicInterval);
                classicInterval = null;
                if (classicState) classicState.textContent = 'Pausado';
            }
        });
    }

    if (classicReset) {
        classicReset.addEventListener('click', () => {
            playResetSound();
            if (classicInterval) clearInterval(classicInterval);
            classicInterval = null;
            classicSeconds = 0;
            if (classicState) classicState.textContent = 'Listo para iniciar';
            updateClassic();
        });
    }

    if (expressStart) {
        expressStart.addEventListener('click', () => {
            if (expressInterval) return;
            playStartSound();
            expressInterval = setInterval(() => {
                expressSeconds--;
                if (expressSeconds <= 0) {
                    playPhaseSound();
                    if (expressIsChange) {
                        expressIsChange = false;
                        expressRoundCount++;
                        expressSeconds = 45;
                    } else {
                        expressIsChange = true;
                        expressSeconds = 10;
                    }
                }
                updateExpress();
            }, 1000);
        });
    }

    if (expressPause) {
        expressPause.addEventListener('click', () => {
            if (expressInterval) {
                playPauseSound();
                clearInterval(expressInterval);
                expressInterval = null;
            }
        });
    }

    if (expressReset) {
        expressReset.addEventListener('click', () => {
            playResetSound();
            if (expressInterval) clearInterval(expressInterval);
            expressInterval = null;
            expressSeconds = 45;
            expressIsChange = false;
            expressRoundCount = 1;
            updateExpress();
        });
    }

    if (soundToggle) {
        soundToggle.addEventListener('click', () => {
            soundEnabled = !soundEnabled;
            window.localStorage.setItem('coupleTimerSound', soundEnabled ? 'on' : 'off');
            updateSoundToggle();
            if (soundEnabled) playStartSound();
        });
    }

    updateClassic();
    updateExpress();
    updateSoundToggle();
</script>
</body>
</html>
