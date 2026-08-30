@extends('immersion::layout')

@section('title', 'Acusacion final')

@section('header-actions')
    <a href="{{ route('immersion.player.inbox', $player->access_token) }}" class="border-2 border-[#e9e2d0] px-3 py-1 text-xs uppercase tracking-wide hover:bg-[#e9e2d0] hover:text-[#241f14]">
        &larr; Bandeja
    </a>
@endsection

@section('content')
    @unless ($unlocked)
        <div class="border-2 border-dashed border-[#8a7b57] p-6 text-center text-sm text-[#5c5236]">
            El formulario de acusacion todavia no esta disponible. El Game Master lo habilitara cuando corresponda.
        </div>
    @else
        <div class="border-2 border-[#241f14] bg-[#f5efe0] p-6">
            <h2 class="immersion-stamp text-sm uppercase tracking-[0.2em] text-[#5c5236]">Tu acusacion, {{ $player->name }}</h2>
            <p class="mt-2 text-sm text-[#5c5236]">Puedes enviarla y actualizarla las veces que quieras mientras el Game Master no revele la solucion.</p>

            <form method="POST" action="{{ route('immersion.player.accusation.store', $player->access_token) }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label for="suspect_name" class="block text-sm font-bold">Sospechoso</label>
                    <input type="text" name="suspect_name" id="suspect_name" required
                        value="{{ old('suspect_name', $player->accusation->suspect_name ?? '') }}"
                        class="mt-1 w-full border-2 border-[#241f14] bg-white px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="weapon" class="block text-sm font-bold">Arma o metodo</label>
                    <input type="text" name="weapon" id="weapon" required
                        value="{{ old('weapon', $player->accusation->weapon ?? '') }}"
                        class="mt-1 w-full border-2 border-[#241f14] bg-white px-3 py-2 text-sm">
                </div>

                <div>
                    <label for="motive" class="block text-sm font-bold">Motivo</label>
                    <textarea name="motive" id="motive" rows="4" required
                        class="mt-1 w-full border-2 border-[#241f14] bg-white px-3 py-2 text-sm">{{ old('motive', $player->accusation->motive ?? '') }}</textarea>
                </div>

                <button type="submit" class="w-full border-2 border-[#241f14] bg-[#241f14] px-4 py-2 text-sm font-bold text-[#e9e2d0] hover:bg-[#3a3220]">
                    Enviar acusacion
                </button>
            </form>
        </div>
    @endunless
@endsection
