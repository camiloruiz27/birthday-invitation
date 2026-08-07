@extends('couple.partials.layout')

@section('content')
    @if (! $consentAccepted)
        @include('couple.partials.consent')
    @else
        <section class="mt-6 rounded-lg border border-stone-300 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-stone-500">Actividades por contexto</p>
                    <h2 class="mt-2 text-3xl font-semibold text-stone-950">Elijan segun dia, lugar e intensidad</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-600">Cada actividad permite completar, pasar, guardar una respuesta o usarla como contexto para el asistente.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-3 lg:w-[34rem]">
                    <select class="rounded-md border border-stone-300 bg-white px-3 py-3 text-sm" data-context-day>
                        @foreach ($days as $day)
                            <option value="{{ $day['day'] }}">Dia {{ $day['day'] }}</option>
                        @endforeach
                    </select>
                    <select class="rounded-md border border-stone-300 bg-white px-3 py-3 text-sm" data-context-level>
                        @foreach ([2, 3, 4, 5] as $level)
                            <option value="{{ $level }}">Nivel {{ $level }}</option>
                        @endforeach
                    </select>
                    <select class="rounded-md border border-stone-300 bg-white px-3 py-3 text-sm" data-context-location>
                        @foreach ($locations as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-5 flex gap-2 overflow-x-auto pb-1">
                @foreach ($activities as $day)
                    <button class="rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-bold text-stone-900 data-[active=true]:border-stone-950 data-[active=true]:bg-stone-950 data-[active=true]:text-white" type="button" data-day-tab="{{ $day['day'] }}">
                        Dia {{ $day['day'] }}
                    </button>
                @endforeach
            </div>
        </section>

        <div class="mt-6 space-y-8">
            @foreach ($activities as $day)
                <section data-day-panel="{{ $day['day'] }}" class="hidden">
                    <div class="mb-4">
                        <h3 class="text-2xl font-semibold text-stone-950">Dia {{ $day['day'] }}: {{ $day['title'] }}</h3>
                        <p class="mt-1 text-sm leading-6 text-stone-600">{{ $day['setting'] }}</p>
                    </div>
                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($day['activities'] as $activity)
                            @php($activityProgress = $progress->get($activity['key']))
                            <article class="rounded-lg border border-stone-300 bg-white p-5 shadow-sm" data-activity-card data-key="{{ $activity['key'] }}" data-day="{{ $activity['day'] }}" data-level="{{ $activity['level'] }}" data-location="{{ $activity['location'] }}" data-privacy="{{ $activity['privacy'] }}">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">Nivel {{ $activity['level'] }}</span>
                                    <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">{{ $locations[$activity['location']] ?? $activity['location'] }}</span>
                                    <span class="rounded-full border border-stone-300 px-3 py-1 text-xs font-bold uppercase tracking-wider text-stone-600">{{ $activity['privacy'] }}</span>
                                    @if ($activityProgress)
                                        <span class="rounded-full bg-stone-950 px-3 py-1 text-xs font-bold uppercase tracking-wider text-white">{{ $activityProgress->status === 'completed' ? 'Completada' : 'Pasada' }}</span>
                                    @endif
                                </div>

                                <h4 class="mt-4 text-xl font-semibold text-stone-950">{{ $activity['title'] }}</h4>
                                <p class="mt-2 text-sm leading-6 text-stone-600">{{ $activity['prompt'] }}</p>
                                <p class="mt-3 text-xs font-semibold uppercase tracking-widest text-stone-500">{{ $activity['type'] }} · {{ $activity['inspiration'] }}</p>

                                <div class="mt-5 grid gap-2 sm:grid-cols-3">
                                    <form method="POST" action="{{ route('couple-experience.progress') }}">
                                        @csrf
                                        <input type="hidden" name="activity_key" value="{{ $activity['key'] }}">
                                        <input type="hidden" name="status" value="completed">
                                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-stone-950 px-3 py-2 text-sm font-bold text-white hover:bg-stone-800" type="submit">
                                            <i data-lucide="check" class="h-4 w-4"></i>
                                            Completar
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('couple-experience.progress') }}">
                                        @csrf
                                        <input type="hidden" name="activity_key" value="{{ $activity['key'] }}">
                                        <input type="hidden" name="status" value="skipped">
                                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-3 py-2 text-sm font-bold text-stone-900 hover:bg-stone-50" type="submit">
                                            <i data-lucide="circle-pause" class="h-4 w-4"></i>
                                            Pasar
                                        </button>
                                    </form>
                                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-3 py-2 text-sm font-bold text-stone-900 hover:bg-stone-50" type="button" data-use-for-ai>
                                        <i data-lucide="wand-2" class="h-4 w-4"></i>
                                        Usar IA
                                    </button>
                                </div>

                                <form class="mt-4" method="POST" action="{{ route('couple-experience.answers') }}">
                                    @csrf
                                    <input type="hidden" name="activity_key" value="{{ $activity['key'] }}">
                                    <textarea class="min-h-24 w-full rounded-md border border-stone-300 bg-stone-50 px-3 py-3 text-sm" name="answer" placeholder="Escriban lo que respondieron, sintieron o acordaron..." required></textarea>
                                    <div class="mt-3 flex justify-between gap-3">
                                        <button class="text-sm font-bold text-stone-600 hover:text-stone-950" type="button" data-next-card>Siguiente</button>
                                        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-stone-300 bg-white px-4 py-2 text-sm font-bold text-stone-900 hover:bg-stone-50" type="submit">
                                            <i data-lucide="send" class="h-4 w-4"></i>
                                            Guardar respuesta
                                        </button>
                                    </div>
                                </form>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <input type="hidden" data-context-activity>
        <div class="mt-6 hidden rounded-md border border-stone-300 bg-stone-50 p-4 text-sm leading-6 text-stone-700" data-game-output></div>
    @endif
@endsection
