@extends('immersion::layout')

@section('title', 'Interrogatorio')

@section('header-actions')
    <a href="{{ route('immersion.player.inbox', $player->access_token) }}" class="border-2 border-[#e9e2d0] px-3 py-1 text-xs uppercase tracking-wide hover:bg-[#e9e2d0] hover:text-[#241f14]">
        &larr; Bandeja
    </a>
@endsection

@section('content')
    <p class="mb-4 text-sm text-[#5c5236]">Elige a quien interrogar. Tienes un maximo de 5 preguntas por persona.</p>

    <div class="mb-6 flex items-center gap-4 border-2 border-[#241f14] bg-[#241f14] p-4 text-[#e9e2d0]">
        <img src="{{ asset('immersion/photos/' . $victim['photo']) }}" alt="{{ $victim['name'] }}"
             class="h-16 w-16 shrink-0 rounded object-cover border-2 border-[#e9e2d0]">
        <div>
            <p class="immersion-stamp text-[10px] uppercase tracking-[0.2em] text-[#c9b98a]">Víctima</p>
            <p class="font-bold">{{ $victim['name'] }}</p>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach ($suspects as $slug => $suspect)
            @php($session = $sessions->get($slug))
            <a href="{{ route('immersion.player.interrogation.show', [$player->access_token, $slug]) }}"
               class="flex items-start gap-3 border-2 border-[#241f14] bg-[#f5efe0] p-3 hover:bg-[#efe6ce]">
                <img src="{{ asset('immersion/photos/' . $suspect['photo']) }}" alt="{{ $suspect['name'] }}"
                     class="h-16 w-16 shrink-0 rounded object-cover border border-[#8a7b57]">
                <div class="min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold">{{ $suspect['name'] }}</span>
                    </div>
                    <span class="text-xs uppercase tracking-wide text-[#5c5236]">{{ $suspect['role'] }}</span>
                    @if ($suspect['connection'])
                        <p class="mt-1 text-xs">{{ $suspect['connection'] }}</p>
                    @endif
                    <p class="mt-1 text-xs font-bold">
                        @if ($session)
                            Preguntas: {{ $session->questions_used }}/{{ $maxQuestions }}
                            @if ($session->isClosed())
                                &mdash; cerrado
                            @endif
                        @else
                            Sin iniciar
                        @endif
                    </p>
                </div>
            </a>
        @endforeach
    </div>
@endsection
