<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Una invitación de cumpleaños creada especialmente para ti."
    >

    <meta name="theme-color" content="#fff8fa">

    <title>Una invitación especial para ti</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <main>
        <section
            class="relative flex min-h-screen items-center justify-center overflow-hidden px-6"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-romantic-100 via-white to-romantic-200"
                aria-hidden="true"
            ></div>

            <div
                class="absolute left-1/4 top-1/4 size-72 rounded-full bg-romantic-300/30 blur-3xl"
                aria-hidden="true"
            ></div>

            <div
                class="absolute bottom-1/4 right-1/4 size-96 rounded-full bg-romantic-400/20 blur-3xl"
                aria-hidden="true"
            ></div>

            <div
                data-hero-content
                class="relative z-10 mx-auto max-w-3xl text-center"
            >
                <span
                    class="mb-6 inline-flex items-center gap-2 rounded-full border border-romantic-300 bg-white/60 px-5 py-2 text-sm font-medium tracking-[0.2em] text-romantic-700 uppercase backdrop-blur-md"
                >
                    <i data-lucide="heart" class="size-4"></i>
                    Para alguien especial
                </span>

                <h1
                    class="font-romantic text-6xl leading-none font-semibold text-romantic-900 md:text-8xl"
                >
                    Tengo una sorpresa para ti
                </h1>

                <p
                    class="mx-auto mt-8 max-w-xl text-base leading-8 text-romantic-800/80 md:text-lg"
                >
                    Esta página será el inicio de una experiencia creada
                    especialmente para celebrar tu cumpleaños.
                </p>

                <button
                    type="button"
                    class="mt-10 inline-flex items-center gap-3 rounded-full bg-romantic-600 px-8 py-4 font-medium text-white shadow-xl shadow-romantic-500/30 transition duration-300 hover:-translate-y-1 hover:bg-romantic-700"
                    onclick="window.confetti({
                        particleCount: 150,
                        spread: 90,
                        origin: { y: 0.7 }
                    })"
                >
                    Abrir invitación

                    <i data-lucide="heart" class="size-5"></i>
                </button>
            </div>
        </section>
    </main>
</body>
</html>