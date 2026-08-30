@extends('immersion::layout')

@section('content')
    <div class="mx-auto max-w-sm border-2 border-[#241f14] bg-[#f5efe0] p-6">
        <p class="immersion-stamp text-xs uppercase tracking-[0.2em] text-[#5c5236]">Acceso restringido</p>
        <h2 class="mt-2 text-lg font-bold">Panel del Game Master</h2>

        <form method="POST" action="{{ route('immersion.gm.login.attempt') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="password" class="block text-sm font-bold">Contrasena</label>
                <input type="password" name="password" id="password" required
                    class="mt-1 w-full border-2 border-[#241f14] bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#241f14]">
            </div>
            <button type="submit" class="w-full border-2 border-[#241f14] bg-[#241f14] px-4 py-2 text-sm font-bold text-[#e9e2d0] hover:bg-[#3a3220]">
                Entrar
            </button>
        </form>
    </div>
@endsection
