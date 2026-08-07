<section class="mt-6 rounded-lg border border-stone-300 bg-white p-5 shadow-sm sm:p-6">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-3xl">
            <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Antes de empezar</p>
            <h2 class="mt-2 text-2xl font-semibold text-stone-950">Consentimiento y ritmo compartido</h2>
            <p class="mt-3 text-sm leading-6 text-stone-600">
                Esta experiencia funciona solo si ambos pueden decir si, no, pausa o mas suave en cualquier momento.
                Los niveles 4 y 5 son privados, graduales y se bajan de intensidad si alguno lo pide.
            </p>
        </div>
        <form method="POST" action="{{ route('couple-experience.consent') }}" class="w-full shrink-0 rounded-md border border-stone-200 bg-stone-50 p-4 lg:w-96">
            @csrf
            <label class="flex gap-3 text-sm leading-6 text-stone-700">
                <input class="mt-1 h-4 w-4 rounded border-stone-300 text-stone-950 focus:ring-stone-950" type="checkbox" name="accept_rules" value="1" required>
                Confirmo que todo sera consensuado, privado cuando corresponda y con opcion real de pausar.
            </label>
            <button class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-3 text-sm font-bold text-white transition hover:bg-stone-800" type="submit">
                <i data-lucide="shield-check" class="h-4 w-4"></i>
                Aceptar y desbloquear
            </button>
        </form>
    </div>
</section>
