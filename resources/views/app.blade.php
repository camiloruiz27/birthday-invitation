<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="@if (config('platform.indexable'))index, follow@else noindex, nofollow @endif">

    {{-- Overridden per page by Inertia's <Head title>; kept case-neutral so
         the shell does not name one particular mystery. --}}
    <title inertia>Central de investigación</title>

    {{-- Paints the browser chrome to match the console surface instead of
         leaving a white bar above a dark page on mobile. --}}
    <meta name="theme-color" content="#14171d">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Inter carries the platform UI; Special Elite and Courier Prime are
         used only by the case (fiction) surface. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Special+Elite&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">

    {{-- Both emit an inline <script>, so both carry the CSP nonce that
         SecurityHeaders puts on the response. Ziggy takes it as its second
         argument; Vite reads it from Vite::useCspNonce(). --}}
    @routes(null, $cspNonce ?? null)
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
