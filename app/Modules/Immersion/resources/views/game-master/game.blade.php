@extends('immersion::layout')

@section('header-actions')
    <a href="{{ route('immersion.gm.dashboard') }}" class="border-2 border-[#e9e2d0] px-3 py-1 text-xs uppercase tracking-wide hover:bg-[#e9e2d0] hover:text-[#241f14]">
        &larr; Partidas
    </a>
@endsection

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-2 border-[#241f14] bg-[#f5efe0] p-4">
        <div>
            <p class="immersion-stamp text-xs uppercase tracking-[0.2em] text-[#5c5236]">{{ $game->status }}</p>
            <h2 class="text-lg font-bold">{{ $game->name }}</h2>
            @if ($game->started_at)
                <p class="mt-1 text-sm">Reloj de la partida: <span id="elapsed-clock" data-base-seconds="{{ $game->elapsedMinutes() * 60 }}" data-running="{{ $game->isRunning() ? '1' : '0' }}">{{ $game->elapsedMinutes() }} min</span></p>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            @if ($game->status === 'draft')
                <form method="POST" action="{{ route('immersion.gm.game.start', $game) }}">
                    @csrf
                    <button class="border-2 border-[#241f14] bg-[#241f14] px-3 py-2 text-xs font-bold uppercase text-[#e9e2d0]">Iniciar caso</button>
                </form>
            @endif

            @if ($game->isRunning())
                <form method="POST" action="{{ route('immersion.gm.game.pause', $game) }}">
                    @csrf
                    <button class="border-2 border-[#241f14] px-3 py-2 text-xs font-bold uppercase">Pausar</button>
                </form>
            @endif

            @if ($game->isPaused())
                <form method="POST" action="{{ route('immersion.gm.game.resume', $game) }}">
                    @csrf
                    <button class="border-2 border-[#241f14] px-3 py-2 text-xs font-bold uppercase">Reanudar</button>
                </form>
            @endif

            @if (in_array($game->status, ['running', 'paused']))
                <form method="POST" action="{{ route('immersion.gm.game.force-next', $game) }}">
                    @csrf
                    <button class="border-2 border-[#241f14] px-3 py-2 text-xs font-bold uppercase">Forzar siguiente evento</button>
                </form>
            @endif

            <a href="{{ route('immersion.gm.game.results', $game) }}" class="border-2 border-[#241f14] px-3 py-2 text-xs font-bold uppercase">
                Ver acusaciones
            </a>

            <form method="POST" action="{{ route('immersion.gm.game.toggle-interrogation', $game) }}">
                @csrf
                <button class="border-2 border-[#241f14] px-3 py-2 text-xs font-bold uppercase">
                    {{ $game->interrogation_enabled ? 'Deshabilitar' : 'Habilitar' }} interrogatorio (Mec. 7)
                </button>
            </form>

            <a href="{{ route('immersion.gm.game.interrogations', $game) }}" class="border-2 border-[#241f14] px-3 py-2 text-xs font-bold uppercase">
                Ver interrogatorios
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.3fr_0.7fr]">
        <section class="border-2 border-[#241f14] bg-[#f5efe0] p-4">
            <h3 class="immersion-stamp text-xs uppercase tracking-[0.2em] text-[#5c5236]">Linea de tiempo</h3>

            @if ($game->timelineEvents->isEmpty())
                <div class="mt-3 border-2 border-dashed border-red-800 bg-red-50 p-4 text-sm text-red-900">
                    <p class="font-bold">Esta partida no tiene linea de tiempo cargada.</p>
                    <p class="mt-1">Esto pasa con partidas creadas antes de este arreglo. "Forzar siguiente evento" no hara nada hasta que cargues la linea de tiempo.</p>
                    <form method="POST" action="{{ route('immersion.gm.game.load-default-timeline', $game) }}" class="mt-3">
                        @csrf
                        <button class="border-2 border-red-900 bg-red-900 px-3 py-2 text-xs font-bold uppercase text-white">
                            Cargar linea de tiempo por defecto
                        </button>
                    </form>
                </div>
            @endif

            <ul class="mt-3 space-y-2">
                @foreach ($game->timelineEvents as $event)
                    <li class="flex items-start justify-between gap-3 border-b border-dashed border-[#8a7b57] py-2 text-sm">
                        <div>
                            <span class="font-bold">Min {{ $event->trigger_offset_minutes }}</span>
                            &mdash; {{ $event->title }}
                            <span class="ml-2 text-xs uppercase text-[#5c5236]">({{ $event->type }} / {{ $event->delivery_mode }})</span>
                            @if ($event->delivery_mode === 'random_player' && $event->deliveredToPlayer)
                                <span class="ml-2 text-xs italic">&rarr; {{ $event->deliveredToPlayer->name }}</span>
                            @endif
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if ($event->isAudio() && $event->isSent() && ! $event->audio_path)
                                <form method="POST" action="{{ route('immersion.gm.game.event.retry-audio', [$game, $event]) }}">
                                    @csrf
                                    <button class="border-2 border-[#241f14] px-2 py-1 text-xs font-bold uppercase" title="El correo salio sin el audio adjunto; genera el audio y reenvia el correo">
                                        Reintentar audio
                                    </button>
                                </form>
                            @endif
                            <span class="text-xs font-bold uppercase">
                                {{ $event->isSent() ? 'Enviado' : 'Pendiente' }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="border-2 border-[#241f14] bg-[#f5efe0] p-4">
            <h3 class="immersion-stamp text-xs uppercase tracking-[0.2em] text-[#5c5236]">Jugadores</h3>
            <ul class="mt-3 space-y-3 text-sm">
                @foreach ($game->players as $player)
                    <li class="border-b border-dashed border-[#8a7b57] pb-2">
                        <p class="font-bold">{{ $player->name }}</p>
                        <p class="text-xs text-[#5c5236]">{{ $player->email }}</p>
                        <p class="mt-1 break-all text-xs">
                            {{ route('immersion.player.inbox', $player->access_token) }}
                        </p>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <script>
        (function () {
            var el = document.getElementById('elapsed-clock');
            if (!el || el.dataset.running !== '1') {
                return;
            }
            var seconds = parseInt(el.dataset.baseSeconds, 10) || 0;
            setInterval(function () {
                seconds += 1;
                var minutes = Math.floor(seconds / 60);
                el.textContent = minutes + ' min';
            }, 1000);
        })();
    </script>
@endsection
