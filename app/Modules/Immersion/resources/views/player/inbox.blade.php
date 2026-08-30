@extends('immersion::layout')

@section('title', 'Bandeja de '.$player->name)

@section('header-actions')
    @if ($player->game->interrogation_enabled)
        <a href="{{ route('immersion.player.interrogation.index', $player->access_token) }}" class="border-2 border-[#e9e2d0] px-3 py-1 text-xs uppercase tracking-wide hover:bg-[#e9e2d0] hover:text-[#241f14]">
            Interrogatorio
        </a>
    @endif
    <a href="{{ route('immersion.player.accusation', $player->access_token) }}" class="border-2 border-[#e9e2d0] px-3 py-1 text-xs uppercase tracking-wide hover:bg-[#e9e2d0] hover:text-[#241f14]">
        Formulario de acusacion
    </a>
@endsection

@section('content')
    <p class="mb-6 text-sm text-[#5c5236]">Hola {{ $player->name }}. Estos son los mensajes que has recibido sobre el caso SF 554301.</p>

    @if ($items->isEmpty())
        <p class="border-2 border-dashed border-[#8a7b57] p-6 text-center text-sm text-[#5c5236]">
            Todavia no ha llegado ningun correo. El Game Master iniciara el caso pronto.
        </p>
    @endif

    <div class="space-y-6">
        @foreach ($items as $item)
            @php($event = $item['event'])
            <article class="border-2 border-[#241f14] bg-[#f5efe0]">
                <header class="border-b-2 border-[#241f14] bg-[#241f14] px-4 py-2 text-[#e9e2d0]">
                    <p class="immersion-stamp text-[10px] uppercase tracking-[0.2em] text-[#c9b98a]">
                        {{ $event->sent_at?->format('d/m/Y H:i') }}
                    </p>
                    <h2 class="font-bold">{{ $event->title }}</h2>
                </header>
                @if (trim(strip_tags($item['body_html'])) !== '')
                    <div class="prose prose-sm max-w-none px-4 py-4">
                        {!! $item['body_html'] !!}
                    </div>
                @elseif (! empty($item['gallery']))
                    <p class="px-4 py-4 text-sm italic text-[#5c5236]">Todo el contenido de este sobre está en las imágenes de abajo.</p>
                @endif

                @if (! empty($item['gallery']))
                    <div class="grid grid-cols-2 gap-3 border-t border-dashed border-[#8a7b57] px-4 py-4 sm:grid-cols-3">
                        @foreach ($item['gallery'] as $image)
                            <a href="{{ asset('immersion/gallery/' . $image['file']) }}" target="_blank" class="block">
                                <img src="{{ asset('immersion/gallery/' . $image['file']) }}" alt="{{ $image['caption'] }}"
                                     class="w-full rounded border border-[#8a7b57] object-cover">
                                <p class="mt-1 text-xs text-[#5c5236]">{{ $image['caption'] }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($event->isAudio())
                    <div class="border-t border-dashed border-[#8a7b57] px-4 py-3">
                        <audio controls preload="none" class="w-full">
                            <source src="{{ route('immersion.player.audio', [$player->access_token, $event->id]) }}" type="audio/wav">
                        </audio>
                    </div>
                @endif
            </article>
        @endforeach
    </div>
@endsection
