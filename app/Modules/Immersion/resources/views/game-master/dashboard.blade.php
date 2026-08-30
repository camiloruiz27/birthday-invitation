@extends('immersion::layout')

@section('header-actions')
    <form method="POST" action="{{ route('immersion.gm.logout') }}">
        @csrf
        <button type="submit" class="border-2 border-[#e9e2d0] px-3 py-1 text-xs uppercase tracking-wide hover:bg-[#e9e2d0] hover:text-[#241f14]">
            Salir
        </button>
    </form>
@endsection

@section('content')
    <section class="mb-10">
        <h2 class="immersion-stamp text-sm uppercase tracking-[0.2em] text-[#5c5236]">Partidas</h2>

        @if ($games->isEmpty())
            <p class="mt-3 text-sm text-[#5c5236]">Todavia no hay ninguna partida creada.</p>
        @else
            <div class="mt-3 space-y-3">
                @foreach ($games as $game)
                    <a href="{{ route('immersion.gm.game.show', $game) }}"
                       class="block border-2 border-[#241f14] bg-[#f5efe0] px-4 py-3 hover:bg-[#efe6ce]">
                        <div class="flex items-center justify-between">
                            <span class="font-bold">{{ $game->name }}</span>
                            <span class="text-xs uppercase tracking-wide">{{ $game->status }}</span>
                        </div>
                        @if ($game->started_at)
                            <p class="mt-1 text-xs text-[#5c5236]">Iniciada: {{ $game->started_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="border-2 border-[#241f14] bg-[#f5efe0] p-6">
        <h2 class="immersion-stamp text-sm uppercase tracking-[0.2em] text-[#5c5236]">Nueva partida</h2>

        <form method="POST" action="{{ route('immersion.gm.games.store') }}" class="mt-4 space-y-6" id="new-game-form">
            @csrf

            <div>
                <label for="name" class="block text-sm font-bold">Nombre de la partida</label>
                <input type="text" name="name" id="name" required placeholder="Ej. Mesa 1 - sabado noche"
                    class="mt-1 w-full border-2 border-[#241f14] bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <p class="text-sm font-bold">Jugadores</p>
                <p class="text-xs text-[#5c5236]">Borra filas con la "x" si vas a probar con menos de 6 personas.</p>
                <div id="player-rows" class="mt-2 space-y-2">
                    @for ($i = 0; $i < 6; $i++)
                        <div class="grid grid-cols-[1fr_1fr_auto] items-center gap-2">
                            <input type="text" name="players[{{ $i }}][name]" placeholder="Nombre" required
                                class="border-2 border-[#241f14] bg-white px-3 py-2 text-sm">
                            <input type="email" name="players[{{ $i }}][email]" placeholder="Correo" required
                                class="border-2 border-[#241f14] bg-white px-3 py-2 text-sm">
                            <button type="button" class="remove-player-row border-2 border-[#241f14] px-2 py-2 text-xs font-bold" title="Quitar">&times;</button>
                        </div>
                    @endfor
                </div>
                <button type="button" id="add-player-row" class="mt-3 text-xs font-bold uppercase tracking-wide underline">
                    + Agregar jugador
                </button>
            </div>

            <button type="submit" class="w-full border-2 border-[#241f14] bg-[#241f14] px-4 py-2 text-sm font-bold text-[#e9e2d0] hover:bg-[#3a3220]">
                Crear partida
            </button>
        </form>
    </section>

    <script>
        (function () {
            var rows = document.getElementById('player-rows');
            var addButton = document.getElementById('add-player-row');
            var index = 6;

            function bindRemove(row) {
                var removeButton = row.querySelector('.remove-player-row');
                removeButton.addEventListener('click', function () {
                    if (rows.children.length > 1) {
                        row.remove();
                    }
                });
            }

            Array.prototype.forEach.call(rows.children, bindRemove);

            addButton.addEventListener('click', function () {
                var row = document.createElement('div');
                row.className = 'grid grid-cols-[1fr_1fr_auto] items-center gap-2';
                row.innerHTML =
                    '<input type="text" name="players[' + index + '][name]" placeholder="Nombre" required class="border-2 border-[#241f14] bg-white px-3 py-2 text-sm">' +
                    '<input type="email" name="players[' + index + '][email]" placeholder="Correo" required class="border-2 border-[#241f14] bg-white px-3 py-2 text-sm">' +
                    '<button type="button" class="remove-player-row border-2 border-[#241f14] px-2 py-2 text-xs font-bold" title="Quitar">&times;</button>';
                rows.appendChild(row);
                bindRemove(row);
                index++;
            });
        })();
    </script>
@endsection
