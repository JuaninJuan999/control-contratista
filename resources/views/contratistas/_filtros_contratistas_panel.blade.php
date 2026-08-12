@php
    /** @var string $filtrosTipo  'externo' | 'interno' */
    $filtrosModulo = $filtrosTipo.'s';
@endphp

<div id="filtros-{{ $filtrosModulo }}" class="mb-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4">
    <p class="mb-3 text-xs text-zinc-600">Filtre la tabla sin recargar la página. Pulse <strong>Filtrar</strong> o <strong>Enter</strong>.</p>
    <div class="@if ($filtrosTipo === 'interno') flex min-w-0 flex-nowrap items-end gap-2 overflow-x-auto pb-0.5 @else grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 @endif">
        <div class="@if ($filtrosTipo === 'interno') min-w-[11rem] flex-[1.4] @else sm:col-span-2 lg:col-span-1 xl:col-span-2 @endif">
            <label for="filtro-{{ $filtrosTipo }}-nombre" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Nombres y apellidos</label>
            <input
                type="text"
                id="filtro-{{ $filtrosTipo }}-nombre"
                placeholder="Buscar por nombre…"
                autocomplete="off"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
        </div>
        <div class="@if ($filtrosTipo === 'interno') min-w-[7.5rem] w-[7.5rem] shrink-0 @endif">
            <label for="filtro-{{ $filtrosTipo }}-tipo-documento" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Tipo de documento</label>
            <select
                id="filtro-{{ $filtrosTipo }}-tipo-documento"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
                <option value="">Todos</option>
                @foreach (\App\Support\TiposDocumento::OPCIONES as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="@if ($filtrosTipo === 'interno') min-w-[7rem] w-[7rem] shrink-0 @endif">
            <label for="filtro-{{ $filtrosTipo }}-documento" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Documento</label>
            <input
                type="text"
                id="filtro-{{ $filtrosTipo }}-documento"
                placeholder="Número…"
                autocomplete="off"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
        </div>
        @if ($filtrosTipo === 'interno')
        <div class="min-w-[10rem] flex-1">
            <label for="filtro-{{ $filtrosTipo }}-empresa" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Empresa</label>
            <select
                id="filtro-{{ $filtrosTipo }}-empresa"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
                <option value="">Todas</option>
                @foreach ($empresasFiltro ?? [] as $empresa)
                    <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="@if ($filtrosTipo === 'interno') min-w-[6.5rem] w-[6.5rem] shrink-0 @endif">
            <label for="filtro-{{ $filtrosTipo }}-arl" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">ARL</label>
            <input
                type="text"
                id="filtro-{{ $filtrosTipo }}-arl"
                placeholder="Ej. SURA"
                autocomplete="off"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
        </div>
        @if ($filtrosTipo === 'externo')
        <div>
            <label for="filtro-{{ $filtrosTipo }}-estado" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Estado I/R</label>
            <select
                id="filtro-{{ $filtrosTipo }}-estado"
                class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
            >
                <option value="">Todos</option>
                <option value="VIGENTE">Vigente</option>
                <option value="VENCIDA">Vencida</option>
                <option value="SIN_REGISTRO">Sin registro</option>
            </select>
        </div>
        @endif
        <div class="@if ($filtrosTipo === 'interno') shrink-0 @else flex items-end gap-2 sm:col-span-2 lg:col-span-2 xl:col-span-1 @endif flex items-end gap-2">
            <button type="button" id="btn-filtrar-{{ $filtrosModulo }}" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                Filtrar
            </button>
            <button type="button" id="btn-limpiar-{{ $filtrosModulo }}" class="hidden rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50">
                Limpiar
            </button>
        </div>
    </div>
    <p id="filtro-{{ $filtrosModulo }}-resumen" class="mt-3 hidden text-xs font-medium text-emerald-800"></p>
    <p class="mt-2 text-xs text-zinc-600 md:text-sm">Clic en el nombre para ver el detalle.@if ($filtrosTipo === 'interno' && auth()->user()?->puedeEditar()) Clic en un mes para marcar <strong>OK</strong>.@endif</p>
</div>
