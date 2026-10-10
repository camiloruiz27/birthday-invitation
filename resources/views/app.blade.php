<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    {{-- viewport-fit=cover is what makes env(safe-area-inset-*) return a real
         value; without it the player's fixed bottom navigation sits under the
         iPhone home indicator, which eats the first tap. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    {{-- A player page is never indexable, whatever the site-wide switch says:
         its URL is the player's credential (/jugador/{token}). --}}
    @php($isPlayerPage = request()->is('jugador/*'))
    {{-- Computed in PHP on purpose. This used to be an inline
         `index, follow@else noindex...@endif`, and Blade does not read an
         @else glued to a word: it printed an EMPTY content attribute, which
         means "no directive" — so every page was indexable whatever the switch
         said. --}}
    {{-- $seo is optional per-page head data (see AdLandingController): a
         page can state its own robots, canonical, title, description and
         share image. It has to come from the server rather than a React
         <Head>, because the crawlers that build link previews (TikTok, Meta,
         WhatsApp) read this HTML and never run the app. --}}
    @php($seo = $seo ?? [])
    <meta name="robots" content="{{ $seo['robots'] ?? (config('platform.indexable') && ! $isPlayerPage ? 'index, follow' : 'noindex, nofollow') }}">

    {{-- Without this, misteriocode.com and www.misteriocode.com (or a
         tracking query string tacked onto a shared link) read as separate
         pages to a search engine — only worth stating now that indexing is
         actually on. url()->current() drops the query string on purpose.
         Skipped on player pages so the token is not echoed into the markup. --}}
    @unless ($isPlayerPage)
        <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">
    @endunless

    {{-- The first thing a page paints, fetched before the app has run: an
         <img> React renders later cannot be discovered by the browser until
         the script has downloaded and executed. --}}
    @isset($preloadImage)
        <link rel="preload" as="image" href="{{ $preloadImage }}" fetchpriority="high">
    @endisset

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
    <link rel="icon" type="image/png" href="/brand/isotipo-96.png">
    <link rel="alternate icon" href="/favicon.ico">

    {{-- What a shared link shows on WhatsApp/Twitter/Facebook. One image and
         one description for the whole site — a page with something more
         specific to say (a case's own cover art) can override these with its
         own <Head> tags later; nothing does yet. The image carries no text of
         its own on purpose: the title and description below are real text,
         not pixels, so they never come out garbled the way AI-generated text
         inside an image does. --}}
    {{-- Always 1200x630: a share card is cropped to ~1.91:1, and a dedicated
         JPEG of that size is ~100 KB where the original art is 1.5-2 MB
         (several crawlers give up on files that heavy).

         One-line PHP statements on purpose, never a multi-line PHP block:
         Blade pairs the one-line form with the next block terminator anywhere
         below it, so a single block swallows every one-liner above it (it
         broke $isPlayerPage). Do not write those directive names inside a
         comment either: Blade reads them before it strips comments. --}}
    @php($shareTitle = $seo['title'] ?? 'MisterioCode — Casos de misterio para jugar en equipo')
    @php($shareDescription = $seo['description'] ?? 'El expediente llega en tiempo real. Investigan, interrogan y acusan — la solución la escribió una persona, no una IA.')
    @php($shareImage = url($seo['image'] ?? '/brand/social-network.jpg'))
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="MisterioCode">
    @unless ($isPlayerPage)
        <meta property="og:url" content="{{ url()->current() }}">
    @endunless
    <meta property="og:title" content="{{ $shareTitle }}">
    <meta property="og:description" content="{{ $shareDescription }}">
    <meta property="og:image" content="{{ $shareImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $shareTitle }}">
    <meta name="twitter:description" content="{{ $shareDescription }}">
    <meta name="twitter:image" content="{{ $shareImage }}">

    @if (config('platform.analytics.ga_measurement_id') || config('platform.analytics.clarity_project_id'))
        {{-- Google Analytics 4 and Microsoft Clarity are NOT loaded here. This
             tag only hands the ids to resources/js/lib/consent.js, which
             injects both scripts according to what the visitor allowed (Ley
             1581 de 2012: cookies need prior, express authorization).
             data-basic="1" lets them load cookieless, with storage denied,
             before any answer. Being a <meta> there is no inline script to
             nonce. --}}
        <meta name="mc-analytics"
              data-ga="{{ config('platform.analytics.ga_measurement_id') }}"
              data-clarity="{{ config('platform.analytics.clarity_project_id') }}"
              data-basic="{{ config('platform.analytics.basic_measurement') ? '1' : '0' }}">
    @endif

    @if (config('platform.ads.tiktok.pixel_id') || config('platform.ads.meta.pixel_id'))
        {{-- Same idea as mc-analytics: only the ids. The TikTok and Meta
             pixels are injected by resources/js/lib/consent.js, and only after
             the visitor accepts the marketing category. --}}
        <meta name="mc-ads"
              data-tiktok="{{ config('platform.ads.tiktok.pixel_id') }}"
              data-meta="{{ config('platform.ads.meta.pixel_id') }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Inter carries the platform UI; Outfit is the clean geometric sans
         for system titles (H1/H2); IBM Plex Mono is every code/timestamp/
         status label in both surfaces; Courier Prime is the fiction
         surface's own document body (envelopes, testimony). --}}
    {{-- A page can say it never shows the fiction surface (an ad landing):
         then Courier Prime, which only the case documents use, is not worth a
         round trip to a third party before the first paint. --}}
    @if ($seo['liteFonts'] ?? false)
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500&family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">
    @endif

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
