@extends('layouts.app')

@section('title', 'Planillas — '.config('app.name'))

@section('containerClass', 'max-w-none w-full')

@section('content')
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-6">
        <div class="mb-4">
            <h1 class="login-text-glow font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Planillas</h1>
            <p class="login-text-glow mt-1 text-sm font-medium text-zinc-900">
                Adjunte la seguridad social de cada empresa por ciclo de vigencia. Cada archivo queda vinculado a la <strong>fecha límite</strong> de la empresa (<strong>vigencia hasta</strong>). Al vencer esa fecha o renovarla, debe adjuntar la nueva planilla.
            </p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                {{ session('error') }}
            </div>
        @endif

        <form method="get" action="{{ route('planillas.index') }}" class="mb-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                <div class="sm:col-span-2">
                    <label for="q" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Empresa</label>
                    <input type="text" name="q" id="q" value="{{ $busqueda }}" placeholder="Buscar por nombre o NIT…" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label for="anio" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Año de registros</label>
                    <select name="anio" id="anio" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                        @for ($y = now()->year + 1; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" @selected($anioFiltro === $y)>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="tipo_empresa" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Clasificación</label>
                    <select name="tipo_empresa" id="tipo_empresa" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                        <option value="">Todas</option>
                        <option value="INTERNA" @selected($tipoFiltro === 'INTERNA')>Interna</option>
                        <option value="EXTERNA" @selected($tipoFiltro === 'EXTERNA')>Externa</option>
                        <option value="SIN_CLASIFICAR" @selected($tipoFiltro === 'SIN_CLASIFICAR')>Sin clasificar</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800">Filtrar</button>
                    <a href="{{ route('planillas.index') }}" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">Limpiar</a>
                </div>
            </div>
        </form>

        <p class="mb-3 text-xs text-zinc-600">Clic en el nombre de la empresa para ver el <strong>historial mensual</strong> y adjuntar la planilla del periodo vigente.</p>

        <div class="overflow-x-auto rounded-lg border border-zinc-200">
            <table class="w-full min-w-[52rem] text-left text-sm">
                <thead>
                    <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                        <th class="min-w-[12rem] px-3 py-3">Empresa</th>
                        <th class="px-3 py-3">Clasificación</th>
                        <th class="px-3 py-3">Fecha límite</th>
                        <th class="px-3 py-3">Periodo vigente</th>
                        <th class="px-3 py-3">Registros {{ $anioFiltro }}</th>
                        <th class="px-3 py-3">Estado vigente</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($empresas as $empresa)
                        @php
                            $periodoVigente = $empresa->periodoVigenciaActual();
                            $archivoVigente = $empresa->archivoPlanillaVigenteActual();
                            $planillaAdjunta = $empresa->planillaVigenteAdjunta();
                            $archivosAnio = $empresa->planillaArchivos->where('periodo_anio', $anioFiltro);
                        @endphp
                        <tr
                            class="planilla-fila cursor-pointer bg-white hover:bg-zinc-50/80"
                            data-planilla-toggle="{{ $empresa->id }}"
                            aria-expanded="false"
                        >
                            <td class="px-3 py-3 font-medium text-zinc-900">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="planilla-chevron size-4 shrink-0 text-emerald-700 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $empresa->nombre }}
                                </span>
                            </td>
                            <td class="px-3 py-3" onclick="event.stopPropagation()">
                                @include('planillas._tipo_empresa_select', ['empresa' => $empresa, 'tipoFiltro' => $tipoFiltro, 'busqueda' => $busqueda, 'anioFiltro' => $anioFiltro])
                            </td>
                            <td class="px-3 py-3 text-zinc-800">{{ $empresa->limite?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-3 text-zinc-800">{{ $empresa->periodoVigenciaEtiqueta() }}</td>
                            <td class="px-3 py-3 tabular-nums text-zinc-800">
                                {{ $archivosAnio->count() }}
                                <span class="text-xs text-zinc-500">/ {{ $empresa->planilla_archivos_count }} total</span>
                            </td>
                            <td class="px-3 py-3">
                                @if ($periodoVigente === null)
                                    <span class="rounded bg-zinc-200 px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-700">Sin límite</span>
                                @elseif ($planillaAdjunta)
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-800">Adjuntada</span>
                                @elseif ($empresa->estado_limite === 'VENCIDA')
                                    <span class="rounded bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase text-red-800">Límite vencido</span>
                                @else
                                    <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-900">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="hidden bg-zinc-50/70" data-planilla-panel="{{ $empresa->id }}" hidden>
                            <td colspan="6" class="border-t border-zinc-100 px-4 py-4">
                                @include('planillas._historial', [
                                    'empresa' => $empresa,
                                    'anioFiltro' => $anioFiltro,
                                    'tipoFiltro' => $tipoFiltro,
                                    'busqueda' => $busqueda,
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-zinc-500">No hay empresas que coincidan con los filtros.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-xs text-zinc-500">
            Formatos: PDF o Excel (.xlsx, .xls), máx. 10 MB. Un registro por fecha límite (vigencia hasta); al renovar la vigencia debe adjuntar la nueva planilla.
        </p>
    </div>

    @include('planillas._expandible_script')
@endsection
