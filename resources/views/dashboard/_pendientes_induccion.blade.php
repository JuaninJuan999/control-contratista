@if ($pendientesInduccion->isNotEmpty())
    <section class="rounded-xl border border-sky-200 bg-sky-50/80 p-4 shadow-lg md:p-5">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-semibold text-sky-950 md:text-xl">
                    <span aria-hidden="true">📋</span> Pendientes de fecha de inducción
                </h2>
                <p class="mt-0.5 text-sm text-sky-800/80">
                    Contratistas activos sin fecha de última I/R registrada. Complete el dato en editar contratista.
                </p>
            </div>
            <span class="rounded-full bg-sky-600 px-2.5 py-0.5 text-xs font-bold text-white shadow-sm">
                {{ $pendientesInduccion->count() }}
            </span>
        </div>

        <ul class="max-h-64 divide-y divide-sky-100 overflow-y-auto rounded-lg border border-sky-200 bg-white">
            @foreach ($pendientesInduccion as $item)
                <li class="flex flex-wrap items-center justify-between gap-2 px-3 py-2.5 text-sm">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-zinc-900">{{ $item['nombre'] }}</p>
                        <p class="text-xs text-zinc-600">
                            {{ $item['documento'] }}
                            · {{ $item['tipo_label'] }}
                            @if (! empty($item['empresa']))
                                · {{ $item['empresa'] }}
                            @endif
                        </p>
                    </div>
                    <a
                        href="{{ $item['editar_url'] }}"
                        class="shrink-0 rounded-md bg-sky-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-sky-800"
                    >
                        Registrar I/R
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
