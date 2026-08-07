@php
    $navItems = [
        ['route' => 'couple-experience.index', 'label' => 'Inicio', 'icon' => 'heart'],
        ['route' => 'couple-experience.assistant', 'label' => 'Asistente', 'icon' => 'wand-2'],
        ['route' => 'couple-experience.activities', 'label' => 'Actividades', 'icon' => 'dices'],
        ['route' => 'couple-experience.timers', 'label' => 'Cronometros', 'icon' => 'timer'],
        ['route' => 'couple-experience.library', 'label' => 'Biblioteca', 'icon' => 'message-circle-heart'],
        ['route' => 'couple-experience.level-six', 'label' => 'Nivel 6', 'icon' => 'sparkles'],
        ['route' => 'couple-experience.history', 'label' => 'Historial', 'icon' => 'brain'],
    ];
@endphp

<header class="sticky top-0 z-30 -mx-4 border-b border-stone-300 bg-[#f3f0ea]/95 px-4 py-4 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
    <div class="mx-auto flex max-w-7xl flex-col gap-4">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Aniversario en Cartagena</p>
                <h1 class="mt-1 text-2xl font-semibold leading-tight text-stone-950 sm:text-3xl">Hola, {{ $user->name }}</h1>
            </div>
            <form method="POST" action="{{ route('couple-experience.logout') }}">
                @csrf
                <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-4 py-2 text-sm font-bold text-white transition hover:bg-stone-800 sm:w-auto" type="submit">
                    <i data-lucide="log-out" class="h-4 w-4"></i>
                    Salir
                </button>
            </form>
        </div>
        <nav class="flex gap-2 overflow-x-auto pb-1" aria-label="Navegacion de aniversario">
            @foreach ($navItems as $item)
                @php($active = request()->routeIs($item['route']))
                <a class="inline-flex shrink-0 items-center gap-2 rounded-md border px-3 py-2 text-sm font-bold transition {{ $active ? 'border-stone-950 bg-stone-950 text-white' : 'border-stone-300 bg-white text-stone-900 hover:bg-stone-50' }}" href="{{ route($item['route']) }}">
                    <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4"></i>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
