@extends('immersion::layout')

@section('header-actions')
    <a href="{{ route('immersion.gm.game.show', $game) }}" class="border-2 border-[#e9e2d0] px-3 py-1 text-xs uppercase tracking-wide hover:bg-[#e9e2d0] hover:text-[#241f14]">
        &larr; {{ $game->name }}
    </a>
@endsection

@section('content')
    <h2 class="immersion-stamp text-sm uppercase tracking-[0.2em] text-[#5c5236]">Interrogatorios (Mecanica 7)</h2>

    @if ($sessions->isEmpty())
        <p class="mt-3 text-sm text-[#5c5236]">Todavia no hay ningun interrogatorio iniciado.</p>
    @endif

    <div class="mt-4 space-y-4">
        @foreach ($sessions as $session)
            <details class="border-2 border-[#241f14] bg-[#f5efe0]">
                <summary class="cursor-pointer px-4 py-3 text-sm font-bold">
                    {{ $session->player->name }} &rarr; {{ $session->suspect_slug }}
                    <span class="ml-2 text-xs font-normal uppercase text-[#5c5236]">
                        {{ $session->questions_used }}/5 {{ $session->isClosed() ? '— cerrado' : '' }}
                    </span>
                </summary>
                <div class="space-y-2 border-t border-dashed border-[#8a7b57] px-4 py-3 text-sm">
                    @foreach ($session->messages as $message)
                        <p><strong>{{ $message->role === 'player' ? 'Jugador' : 'Sospechoso' }}:</strong> {{ $message->content }}</p>
                    @endforeach
                </div>
            </details>
        @endforeach
    </div>
@endsection
