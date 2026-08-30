@extends('immersion::layout')

@section('header-actions')
    <a href="{{ route('immersion.gm.game.show', $game) }}" class="border-2 border-[#e9e2d0] px-3 py-1 text-xs uppercase tracking-wide hover:bg-[#e9e2d0] hover:text-[#241f14]">
        &larr; {{ $game->name }}
    </a>
@endsection

@section('content')
    <h2 class="immersion-stamp text-sm uppercase tracking-[0.2em] text-[#5c5236]">Acusaciones registradas</h2>

    <div class="mt-4 overflow-x-auto border-2 border-[#241f14] bg-[#f5efe0]">
        <table class="w-full min-w-[640px] text-left text-sm">
            <thead>
                <tr class="border-b-2 border-[#241f14] uppercase text-xs">
                    <th class="px-3 py-2">Jugador</th>
                    <th class="px-3 py-2">Sospechoso</th>
                    <th class="px-3 py-2">Motivo</th>
                    <th class="px-3 py-2">Arma</th>
                    <th class="px-3 py-2">Enviada</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($game->players as $player)
                    <tr class="border-b border-dashed border-[#8a7b57] align-top">
                        <td class="px-3 py-2 font-bold">{{ $player->name }}</td>
                        @if ($player->accusation)
                            <td class="px-3 py-2">{{ $player->accusation->suspect_name }}</td>
                            <td class="px-3 py-2">{{ $player->accusation->motive }}</td>
                            <td class="px-3 py-2">{{ $player->accusation->weapon }}</td>
                            <td class="px-3 py-2">{{ $player->accusation->submitted_at->format('d/m H:i') }}</td>
                        @else
                            <td class="px-3 py-2 italic text-[#5c5236]" colspan="4">Sin acusacion todavia.</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-2" colspan="5">No hay jugadores en esta partida.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
