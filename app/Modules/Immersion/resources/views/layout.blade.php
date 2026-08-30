<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Caso SF 554301')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    <style>
        .immersion-typewriter { font-family: 'Courier Prime', ui-monospace, monospace; }
        .immersion-stamp { font-family: 'Special Elite', cursive; }
    </style>
</head>
<body class="immersion-typewriter min-h-screen bg-[#e9e2d0] text-[#241f14] antialiased">
    <header class="border-b-4 border-double border-[#241f14] bg-[#241f14] text-[#e9e2d0] px-4 py-4 sm:px-8">
        <div class="mx-auto flex max-w-5xl items-center justify-between">
            <div>
                <p class="immersion-stamp text-xs uppercase tracking-[0.3em] text-[#c9b98a]">Caso SF 554301 &middot; Confidencial</p>
                <h1 class="mt-1 text-xl font-bold sm:text-2xl">{{ $heading ?? '¿Que le sucedio a Steve Jacobs?' }}</h1>
            </div>
            @yield('header-actions')
        </div>
    </header>

    <main class="mx-auto w-full max-w-5xl px-4 py-8 sm:px-8">
        @if (session('status'))
            <div class="mb-6 border-2 border-[#241f14] bg-[#f5efe0] px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 border-2 border-red-800 bg-red-50 px-4 py-3 text-sm text-red-900">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
