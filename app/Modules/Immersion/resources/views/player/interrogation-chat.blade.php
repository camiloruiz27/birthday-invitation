@extends('immersion::layout')

@section('title', 'Interrogatorio — '.$suspect['name'])

@section('header-actions')
    <a href="{{ route('immersion.player.interrogation.index', $player->access_token) }}" class="border-2 border-[#e9e2d0] px-3 py-1 text-xs uppercase tracking-wide hover:bg-[#e9e2d0] hover:text-[#241f14]">
        &larr; Personas
    </a>
@endsection

@section('content')
    <div class="mb-4 flex items-center justify-between border-2 border-[#241f14] bg-[#f5efe0] px-4 py-3">
        <div class="flex items-center gap-3">
            <img src="{{ asset('immersion/photos/' . $suspect['photo']) }}" alt="{{ $suspect['name'] }}"
                 class="h-14 w-14 rounded object-cover border border-[#8a7b57]">
            <div>
                <p class="immersion-stamp text-xs uppercase tracking-[0.2em] text-[#5c5236]">{{ $suspect['role'] }}</p>
                <h2 class="text-lg font-bold">{{ $suspect['name'] }}</h2>
            </div>
        </div>
        <span class="text-sm font-bold">Preguntas: {{ $session->questions_used }}/5</span>
    </div>

    <div class="mb-4 space-y-3">
        @forelse ($session->messages as $message)
            <div class="border-2 border-[#241f14] px-4 py-2 text-sm {{ $message->role === 'player' ? 'bg-white' : 'bg-[#f5efe0]' }}">
                <p class="text-xs font-bold uppercase tracking-wide text-[#5c5236]">
                    {{ $message->role === 'player' ? 'Tu' : $suspect['name'] }}
                </p>
                <p class="mt-1">{{ $message->content }}</p>
            </div>
        @empty
            <p class="text-sm text-[#5c5236]">Todavia no le has hecho ninguna pregunta.</p>
        @endforelse
    </div>

    @if (! $session->isClosed())
        <form method="POST" action="{{ route('immersion.player.interrogation.store', [$player->access_token, $slug]) }}" class="space-y-3">
            @csrf
            <textarea name="question" rows="2" required maxlength="600" placeholder="Escribe tu pregunta..."
                class="w-full border-2 border-[#241f14] bg-white px-3 py-2 text-sm"></textarea>
            <button type="submit" class="w-full border-2 border-[#241f14] bg-[#241f14] px-4 py-2 text-sm font-bold text-[#e9e2d0] hover:bg-[#3a3220]">
                Preguntar ({{ $session->questionsRemaining() }} restantes)
            </button>
        </form>
    @else
        <div class="border-2 border-dashed border-[#8a7b57] p-4 text-center text-sm text-[#5c5236]">
            Ya usaste tus 5 preguntas con {{ $suspect['name'] }}. Abajo tienes su declaracion oficial completa.
        </div>

        <div class="mt-4 border-2 border-[#241f14] bg-[#f5efe0] p-4">
            <p class="immersion-stamp mb-2 text-xs uppercase tracking-[0.2em] text-[#5c5236]">Declaracion oficial</p>
            <div class="prose prose-sm max-w-none">
                {!! $originalTestimonyHtml !!}
            </div>
        </div>
    @endif
@endsection
