<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    {{-- viewport-fit=cover is what makes env(safe-area-inset-*) return a real
         value; without it the player's fixed bottom navigation sits under the
         iPhone home indicator, which eats the first tap. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="robots" content="@if (config('platform.indexable'))index, follow@else noindex, nofollow @endif">

    {{-- Overridden per page by Inertia's <Head title>; kept case-neutral so
         the shell does not name one particular mystery. --}}
    <title inertia>MisterioCode</title>

    {{-- Paints the browser chrome to match the console surface instead of
         leaving a white bar above a dark page on mobile. --}}
    {{-- Must match --color-surface, or Android Chrome's URL bar renders a
         visibly different dark than the header right underneath it. --}}
    <meta name="theme-color" content="#080f14">

    {{-- The real isotype now that one exists; the .ico stays as "alternate"
         for the handful of browsers/OS bookmark icons that expect one. --}}
    <link rel="icon" type="image/png" href="/brand/isotipo.png">
    <link rel="alternate icon" href="/favicon.ico">

    {{-- What a shared link shows on WhatsApp/Twitter/Facebook. One image and
         one description for the whole site — a page with something more
         specific to say (a case's own cover art) can override these with its
         own <Head> tags later; nothing does yet. The image carries no text of
         its own on purpose: the title and description below are real text,
         not pixels, so they never come out garbled the way AI-generated text
         inside an image does. --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="MisterioCode">
    <meta property="og:title" content="MisterioCode — Casos de misterio para jugar en equipo">
    <meta property="og:description" content="El expediente llega en tiempo real. Investigan, interrogan y acusan — la solución la escribió una persona, no una IA.">
    <meta property="og:image" content="{{ url('/brand/social-network.png') }}">
    <meta property="og:image:width" content="1731">
    <meta property="og:image:height" content="909">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="MisterioCode — Casos de misterio para jugar en equipo">
    <meta name="twitter:description" content="El expediente llega en tiempo real. Investigan, interrogan y acusan — la solución la escribió una persona, no una IA.">
    <meta name="twitter:image" content="{{ url('/brand/social-network.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Inter carries the platform UI; Outfit is the clean geometric sans
         for system titles (H1/H2); IBM Plex Mono is every code/timestamp/
         status label in both surfaces; Courier Prime is the fiction
         surface's own document body (envelopes, testimony). --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">

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
