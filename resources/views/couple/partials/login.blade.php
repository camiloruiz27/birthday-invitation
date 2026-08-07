<main class="grid min-h-screen place-items-center px-4 py-10">
    <section class="grid w-full max-w-5xl overflow-hidden rounded-lg border border-stone-300 bg-white shadow-sm lg:grid-cols-[1.05fr_0.95fr]">
        <div class="bg-stone-950 p-8 text-white sm:p-10 lg:p-12">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1 text-xs font-bold uppercase tracking-widest text-stone-300">
                <i data-lucide="sparkles" class="h-4 w-4"></i>
                Cartagena privada
            </div>
            <h1 class="mt-8 max-w-xl text-5xl font-semibold leading-none sm:text-6xl">4 dias para volver a elegirse</h1>
            <p class="mt-6 max-w-lg text-base leading-7 text-stone-300">Una experiencia cerrada para conversar, jugar, conectar y subir intensidad con consentimiento claro.</p>
        </div>

        <div class="p-7 sm:p-10 lg:p-12">
            <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Acceso cerrado</p>
            <h2 class="mt-3 text-3xl font-semibold text-stone-950">Entrar a la experiencia</h2>
            <p class="mt-4 text-sm leading-6 text-stone-600">Usa una de las cuentas configuradas en la base de datos. No hay registro publico.</p>

            <form class="mt-7 space-y-5" method="POST" action="{{ route('couple-experience.login') }}">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-stone-800" for="email">Correo</label>
                    <input class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950 outline-none transition focus:border-stone-950 focus:ring-2 focus:ring-stone-200" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-800" for="password">Clave</label>
                    <input class="mt-2 w-full rounded-md border border-stone-300 bg-white px-3 py-3 text-stone-950 outline-none transition focus:border-stone-950 focus:ring-2 focus:ring-stone-200" id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-800" type="submit">
                    <i data-lucide="play" class="h-4 w-4"></i>
                    Entrar
                </button>
            </form>
        </div>
    </section>
</main>
