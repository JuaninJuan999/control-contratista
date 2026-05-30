@extends('layouts.app')

@section('title', 'Vehículos — '.config('app.name'))

@section('content')
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Vehículos</h1>
            @if (auth()->user()?->puedeEditar())
            <a
                href="{{ route('vehiculos.create') }}"
                class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800"
            >
                Nuevo vehículo
            </a>
            @endif
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        <div id="filtros-vehiculos" class="mb-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4">
            <p class="mb-3 text-xs text-zinc-600">Filtre la tabla sin recargar la página. Pulse <strong>Filtrar</strong> o <strong>Enter</strong>.</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <div class="flex min-w-0 flex-col">
                    <label for="filtro-vehiculo-placa" class="mb-1 block min-h-10 text-xs font-semibold uppercase leading-tight tracking-wide text-zinc-600">Placa</label>
                    <input
                        type="text"
                        id="filtro-vehiculo-placa"
                        placeholder="Ej. ABC123"
                        autocomplete="off"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                </div>
                <div class="flex min-w-0 flex-col">
                    <label for="filtro-vehiculo-empresa" class="mb-1 block min-h-10 text-xs font-semibold uppercase leading-tight tracking-wide text-zinc-600">Empresa</label>
                    <select
                        id="filtro-vehiculo-empresa"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                        <option value="">Todas</option>
                        @foreach ($empresas as $empresa)
                            <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex min-w-0 flex-col">
                    <label for="filtro-vehiculo-soat" class="mb-1 block min-h-10 text-xs font-semibold uppercase leading-tight tracking-wide text-zinc-600">Estado SOAT</label>
                    <select
                        id="filtro-vehiculo-soat"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                        <option value="">Todos</option>
                        <option value="VIGENTE">Vigente</option>
                        <option value="VENCIDA">Vencida</option>
                    </select>
                </div>
                <div class="flex min-w-0 flex-col">
                    <label for="filtro-vehiculo-tecnomecanica" class="mb-1 block min-h-10 text-xs font-semibold uppercase leading-tight tracking-wide text-zinc-600">Estado tecnomecánica</label>
                    <select
                        id="filtro-vehiculo-tecnomecanica"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                        <option value="">Todos</option>
                        <option value="VIGENTE">Vigente</option>
                        <option value="VENCIDA">Vencida</option>
                    </select>
                </div>
                <div class="flex min-w-0 flex-col sm:col-span-2 lg:col-span-1">
                    <label for="filtro-vehiculo-inspeccion" class="mb-1 block min-h-10 text-xs font-semibold uppercase leading-tight tracking-wide text-zinc-600">Insp. sanitaria</label>
                    <select
                        id="filtro-vehiculo-inspeccion"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                        <option value="">Todos</option>
                        <option value="VIGENTE">Vigente</option>
                        <option value="VENCIDA">Vencida</option>
                        <option value="NO_APLICA">No aplica</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button type="button" id="btn-filtrar-vehiculos" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                    Filtrar
                </button>
                <button type="button" id="btn-limpiar-vehiculos" class="hidden rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50">
                    Limpiar
                </button>
            </div>
            <p id="filtro-vehiculos-resumen" class="mt-3 hidden text-xs font-medium text-emerald-800"></p>
        </div>

        <div class="overflow-x-auto rounded-lg border border-zinc-200">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                        <th class="px-3 py-3">Placa</th>
                        <th class="px-3 py-3">Empresa</th>
                        <th class="px-3 py-3">SOAT (fin)</th>
                        <th class="px-3 py-3">Estado SOAT</th>
                        <th class="px-3 py-3">Tecnomecánica (fin)</th>
                        <th class="px-3 py-3">Estado tecnomecánica</th>
                        <th class="px-3 py-3">Documentos</th>
                        @if (auth()->user()?->puedeEditar())
                        <th class="px-3 py-3">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($vehiculos as $v)
                        @php
                            $estadoInspeccion = $v->inspeccion_sanitaria ? $v->inspeccion_sanitaria_estado : 'NO_APLICA';
                        @endphp
                        <tr
                            data-vehiculo-fila="vehiculo-{{ $v->id }}"
                            class="vehiculo-fila bg-white hover:bg-zinc-50/80"
                            data-filtro-placa="{{ mb_strtolower(preg_replace('/\s+/', '', $v->placa), 'UTF-8') }}"
                            data-filtro-empresa="{{ $v->empresa_id ?? '' }}"
                            data-filtro-soat="{{ $v->soat_estado }}"
                            data-filtro-tecnomecanica="{{ $v->tecnomecanica_estado }}"
                            data-filtro-inspeccion="{{ $estadoInspeccion }}"
                        >
                            <td class="px-3 py-2 font-medium text-zinc-900">{{ $v->placa }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $v->empresa?->nombre ?? '—' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-zinc-800">{{ $v->soat_fin->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">
                                @if ($v->soat_estado === 'VIGENTE')
                                    <span class="font-bold text-emerald-700">VIGENTE</span>
                                @else
                                    <span class="font-bold text-red-700">VENCIDA</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-zinc-800">{{ $v->tecnomecanica_fin->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">
                                @if ($v->tecnomecanica_estado === 'VIGENTE')
                                    <span class="font-bold text-emerald-700">VIGENTE</span>
                                @else
                                    <span class="font-bold text-red-700">VENCIDA</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                @php $tieneDocs = false; @endphp
                                <div class="flex flex-col gap-0.5">
                                    @foreach (\App\Models\Vehiculo::DOCUMENTOS as $campoDoc => $etiquetaDoc)
                                        @if ($v->{$campoDoc})
                                            @php $tieneDocs = true; @endphp
                                            <a href="{{ \App\Services\VehiculoDocumentoStorage::urlPublica($v->{$campoDoc}) }}" target="_blank" rel="noopener noreferrer" class="text-xs font-medium text-emerald-700 underline hover:text-emerald-800">{{ $etiquetaDoc }}</a>
                                        @endif
                                    @endforeach
                                    @unless ($tieneDocs)
                                        <span class="text-zinc-400">—</span>
                                    @endunless
                                    @if ($v->inspeccion_sanitaria && $v->inspeccion_sanitaria_fin)
                                        <span class="text-[11px] text-zinc-500">
                                            Insp. sanitaria: {{ $v->inspeccion_sanitaria_fin->format('d/m/Y') }} —
                                            <span class="font-bold {{ $v->inspeccion_sanitaria_estado === 'VIGENTE' ? 'text-emerald-700' : 'text-red-700' }}">{{ $v->inspeccion_sanitaria_estado }}</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            @if (auth()->user()?->puedeEditar())
                            <td class="px-3 py-2">
                                <a
                                    href="{{ route('vehiculos.edit', $v) }}"
                                    class="rounded-md border border-emerald-700 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-900 hover:bg-emerald-100"
                                >
                                    Editar
                                </a>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->puedeEditar() ? 8 : 7 }}" class="px-3 py-8 text-center text-zinc-500">
                                No hay vehículos registrados.
                                @if (auth()->user()?->puedeEditar())
                                <a href="{{ route('vehiculos.create') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Registrar el primero</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                    @if ($vehiculos->isNotEmpty())
                        <tr id="filtro-vehiculos-sin-resultados" class="hidden">
                            <td colspan="{{ auth()->user()?->puedeEditar() ? 8 : 7 }}" class="px-3 py-8 text-center text-zinc-500">
                                No hay vehículos que coincidan con los filtros.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    @include('vehiculos._filtros_script')

    <script>
        (function () {
            var params = new URLSearchParams(window.location.search);
            var abrir = params.get('abrir');

            if (!abrir || !abrir.startsWith('vehiculo-')) {
                return;
            }

            setTimeout(function () {
                var fila = document.querySelector('[data-vehiculo-fila="' + abrir + '"]');

                if (!fila) {
                    return;
                }

                if (window.resaltarFilaBusqueda) {
                    window.resaltarFilaBusqueda(fila);
                }
            }, 120);
        })();
    </script>
@endsection
