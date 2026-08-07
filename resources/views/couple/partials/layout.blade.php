<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Experiencia privada de aniversario en Cartagena.">
    <title>{{ $title ?? 'Aniversario en Cartagena' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f3f0ea] text-stone-950 antialiased">
<div class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-950/80 px-4 backdrop-blur-sm" data-loading-overlay>
    <div class="w-full max-w-sm rounded-lg border border-white/10 bg-white p-6 text-center shadow-xl">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full border border-stone-200 bg-stone-50">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-stone-300 border-t-stone-950"></span>
        </div>
        <h2 class="mt-5 text-xl font-semibold text-stone-950" data-loading-title>Preparando experiencia</h2>
        <p class="mt-2 text-sm leading-6 text-stone-600" data-loading-message>Estamos cargando la pagina.</p>
    </div>
</div>

@if (! $user)
    @include('couple.partials.login')
@else
    <main class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        @include('couple.partials.header')

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

        @yield('content')
    </main>
@endif

@include('couple.partials.scripts')
</body>
</html>
