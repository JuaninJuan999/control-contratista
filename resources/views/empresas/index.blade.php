@extends('layouts.app')

@section('title', 'Empresas — '.config('app.name'))

@section('content')
    @php
        $anioActual = now()->year;
    @endphp

    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Empresas</h1>
            @if (auth()->user()?->puedeEditar())
            <a href="{{ route('empresas.create') }}" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                Nueva empresa
            </a>
            @endif
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

        <p class="mb-4 text-xs text-zinc-600 md:text-sm">Haz clic en una empresa para ver <strong>Contratistas</strong> y <strong>Vehículos</strong>. Luego expande cada sección y el registro que quieras consultar.</p>

        <div class="overflow-x-auto rounded-lg border border-zinc-200">
        <table class="min-w-full text-left text-sm">
            <thead>
                <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                    <th class="w-8 px-2 py-3"></th>
                    <th class="px-3 py-3">Nombre</th>
                    <th class="px-3 py-3">NIT</th>
                    <th class="px-3 py-3">Teléfono</th>
                    <th class="px-3 py-3">Correos</th>
                    <th class="px-3 py-3">Límite</th>
                    <th class="px-3 py-3">Planilla</th>
                    @if (auth()->user()?->puedeEditar())
                    <th class="px-3 py-3 w-44 text-end">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200">
                @forelse ($empresas as $empresa)
                    <tr
                        class="empresa-fila cursor-pointer bg-white hover:bg-emerald-50/60"
                        data-empresa-toggle="{{ $empresa->id }}"
                        aria-expanded="false"
                    >
                        <td class="px-2 py-2 text-zinc-500">
                            <svg class="empresa-chevron size-4 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                            </svg>
                        </td>
                        <td class="px-3 py-2 font-medium text-zinc-900">
                            {{ $empresa->nombre }}
                            <span class="ml-1.5 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-900">
                                {{ $empresa->contratistas_externos_count + $empresa->contratistas_internos_count }} contratista{{ ($empresa->contratistas_externos_count + $empresa->contratistas_internos_count) === 1 ? '' : 's' }}
                            </span>
                            @if ($empresa->vehiculos_count > 0)
                                <span class="ml-1 rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-zinc-700">
                                    {{ $empresa->vehiculos_count }} vehículo{{ $empresa->vehiculos_count === 1 ? '' : 's' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-zinc-800">{{ $empresa->nit ?? '—' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-zinc-800">{{ $empresa->telefono ?? '—' }}</td>
                        <td class="px-3 py-2 text-zinc-800">
                            @if (is_array($empresa->correos) && count($empresa->correos) > 0)
                                <div class="flex max-w-[14rem] flex-col gap-0.5">
                                    @foreach ($empresa->correos as $correo)
                                        <span class="truncate text-xs md:text-sm" title="{{ $correo }}">{{ $correo }}</span>
                                    @endforeach
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-zinc-800">{{ $empresa->limite?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-3 py-2 text-zinc-800">{{ $empresa->planilla ?? '—' }}</td>
                        @if (auth()->user()?->puedeEditar())
                        <td class="px-3 py-2 text-end" data-acciones>
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('empresas.edit', $empresa) }}" class="text-sm font-medium text-emerald-800 underline hover:text-emerald-950" data-acciones>
                                    Editar
                                </a>
                                <form action="{{ route('empresas.destroy', $empresa) }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar esta empresa? Esta acción no se puede deshacer.');" data-acciones>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-700 underline hover:text-red-900">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    <tr class="empresa-detalle hidden bg-zinc-50/80" data-empresa-panel="{{ $empresa->id }}" hidden>
                        <td colspan="{{ auth()->user()?->puedeEditar() ? 8 : 7 }}" class="px-4 py-3">
                            @php
                                $totalContratistasEmpresa = $empresa->contratistasExternos->count() + $empresa->contratistasInternos->count();
                                $categoriaContratistasId = 'empresa-'.$empresa->id.'-contratistas';
                                $categoriaVehiculosId = 'empresa-'.$empresa->id.'-vehiculos';
                            @endphp
                            @if ($totalContratistasEmpresa === 0 && $empresa->vehiculos->isEmpty())
                                <p class="text-sm text-zinc-500">Esta empresa no tiene contratistas ni vehículos vinculados.</p>
                            @else
                                <div class="divide-y divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 bg-white">
                                    {{-- Categoría Contratistas --}}
                                    <div class="categoria-grupo" data-categoria-grupo="{{ $categoriaContratistasId }}">
                                        <button
                                            type="button"
                                            class="categoria-toggle flex w-full items-center gap-2 px-4 py-3 text-left hover:bg-zinc-50"
                                            data-categoria-toggle="{{ $categoriaContratistasId }}"
                                            aria-expanded="false"
                                        >
                                            <svg class="categoria-chevron size-4 shrink-0 text-emerald-700 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-sm font-bold uppercase tracking-wide text-emerald-800">Contratistas</span>
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-900">{{ $totalContratistasEmpresa }}</span>
                                        </button>
                                        <div class="categoria-panel hidden border-t border-zinc-100 bg-zinc-50/40" data-categoria-panel="{{ $categoriaContratistasId }}" hidden>
                                            @if ($totalContratistasEmpresa === 0)
                                                <p class="px-4 py-3 text-sm text-zinc-500">No hay contratistas vinculados a esta empresa.</p>
                                            @else
                                                <div class="divide-y divide-zinc-200 border-t border-zinc-100 bg-white">
                                                    @foreach ($empresa->contratistasExternos as $contratista)
                                                        <div class="item-grupo" data-item-grupo="externo-{{ $contratista->id }}">
                                                            <button type="button" class="item-toggle flex w-full items-center gap-2 px-4 py-2.5 text-left hover:bg-zinc-50" data-item-toggle="externo-{{ $contratista->id }}" aria-expanded="false">
                                                                <svg class="item-chevron size-4 shrink-0 text-zinc-500 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                                                                </svg>
                                                                <span class="font-medium text-zinc-900">{{ $contratista->nombres_apellidos }}</span>
                                                                <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-zinc-600">Externo</span>
                                                                <span class="text-xs text-zinc-500">{{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</span>
                                                                @if ($contratista->estado === 'VIGENTE')
                                                                    <span class="ml-auto rounded px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700">Vigente</span>
                                                                @else
                                                                    <span class="ml-auto rounded px-2 py-0.5 text-[10px] font-bold uppercase text-red-700">Vencida</span>
                                                                @endif
                                                            </button>
                                                            <div class="item-detalle hidden border-t border-zinc-100 bg-zinc-50/50 px-4 py-3" data-item-panel="externo-{{ $contratista->id }}" hidden>
                                                                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nombres y apellidos</dt><dd class="mt-0.5 font-medium text-zinc-900">{{ $contratista->nombres_apellidos }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tipo</dt><dd class="mt-0.5 text-zinc-900">Externo</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Empresa</dt><dd class="mt-0.5 text-zinc-900">{{ $empresa->nombre }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fecha última I/R</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->fecha_ultima_ir->format('d/m/Y') }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vigencia</dt><dd class="mt-0.5 font-semibold text-zinc-900">{{ $contratista->vigencia_dias }} días</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vencimiento</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->fecha_vencimiento->format('d/m/Y') }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Días faltantes</dt><dd class="mt-0.5 font-bold tabular-nums text-zinc-900">{{ $contratista->dias_faltantes }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Estado</dt><dd class="mt-0.5"><span class="font-bold {{ $contratista->estado === 'VIGENTE' ? 'text-emerald-700' : 'text-red-700' }}">{{ $contratista->estado }}</span></dd></div>
                                                                </dl>
                                                                @include('contratistas._detalle_campos_adicionales', ['contratista' => $contratista])
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    @foreach ($empresa->contratistasInternos as $contratista)
                                                        <div class="item-grupo" data-item-grupo="interno-{{ $contratista->id }}">
                                                            <button type="button" class="item-toggle flex w-full items-center gap-2 px-4 py-2.5 text-left hover:bg-zinc-50" data-item-toggle="interno-{{ $contratista->id }}" aria-expanded="false">
                                                                <svg class="item-chevron size-4 shrink-0 text-zinc-500 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                                                                </svg>
                                                                <span class="font-medium text-zinc-900">{{ $contratista->nombres_apellidos }}</span>
                                                                <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-zinc-600">Interno</span>
                                                                <span class="text-xs text-zinc-500">{{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</span>
                                                                <span class="ml-auto text-xs font-medium text-zinc-600">ARL: {{ $contratista->arl }}</span>
                                                            </button>
                                                            <div class="item-detalle hidden border-t border-zinc-100 bg-zinc-50/50 px-4 py-3" data-item-panel="interno-{{ $contratista->id }}" hidden>
                                                                <dl class="mb-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nombres y apellidos</dt><dd class="mt-0.5 font-medium text-zinc-900">{{ $contratista->nombres_apellidos }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tipo</dt><dd class="mt-0.5 text-zinc-900">Interno</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Empresa</dt><dd class="mt-0.5 text-zinc-900">{{ $empresa->nombre }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">ARL</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->arl }}</dd></div>
                                                                </dl>
                                                                @include('contratistas._detalle_campos_adicionales', ['contratista' => $contratista])
                                                                <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Control mensual {{ $anioActual }}</p>
                                                                <div class="flex flex-wrap gap-1">
                                                                    @foreach (\App\Models\ContratistaInterno::MESES as $mes => $abrev)
                                                                        <span class="inline-flex h-7 min-w-7 items-center justify-center rounded text-[10px] font-bold {{ $contratista->mesRegistrado($anioActual, $mes) ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-400' }}" title="{{ $abrev }}">
                                                                            {{ $contratista->mesRegistrado($anioActual, $mes) ? 'OK' : $abrev }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Categoría Vehículos --}}
                                    <div class="categoria-grupo" data-categoria-grupo="{{ $categoriaVehiculosId }}">
                                        <button
                                            type="button"
                                            class="categoria-toggle flex w-full items-center gap-2 px-4 py-3 text-left hover:bg-zinc-50"
                                            data-categoria-toggle="{{ $categoriaVehiculosId }}"
                                            aria-expanded="false"
                                        >
                                            <svg class="categoria-chevron size-4 shrink-0 text-emerald-700 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-sm font-bold uppercase tracking-wide text-emerald-800">Vehículos</span>
                                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-700">{{ $empresa->vehiculos->count() }}</span>
                                        </button>
                                        <div class="categoria-panel hidden border-t border-zinc-100 bg-zinc-50/40" data-categoria-panel="{{ $categoriaVehiculosId }}" hidden>
                                            @if ($empresa->vehiculos->isEmpty())
                                                <p class="px-4 py-3 text-sm text-zinc-500">No hay vehículos vinculados a esta empresa.</p>
                                            @else
                                                <div class="divide-y divide-zinc-200 border-t border-zinc-100 bg-white">
                                                    @foreach ($empresa->vehiculos as $vehiculo)
                                                        <div class="item-grupo" data-item-grupo="vehiculo-{{ $vehiculo->id }}">
                                                            <button type="button" class="item-toggle flex w-full items-center gap-2 px-4 py-2.5 text-left hover:bg-zinc-50" data-item-toggle="vehiculo-{{ $vehiculo->id }}" aria-expanded="false">
                                                                <svg class="item-chevron size-4 shrink-0 text-zinc-500 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                                                                </svg>
                                                                <span class="font-medium text-zinc-900">{{ $vehiculo->placa }}</span>
                                                                <span class="ml-auto text-xs text-zinc-500">SOAT {{ $vehiculo->soat_fin->format('d/m/Y') }}</span>
                                                            </button>
                                                            <div class="item-detalle hidden border-t border-zinc-100 bg-zinc-50/50 px-4 py-3" data-item-panel="vehiculo-{{ $vehiculo->id }}" hidden>
                                                                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Placa</dt><dd class="mt-0.5 font-medium text-zinc-900">{{ $vehiculo->placa }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Empresa</dt><dd class="mt-0.5 text-zinc-900">{{ $empresa->nombre }}</dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">SOAT (fin)</dt><dd class="mt-0.5 text-zinc-900">{{ $vehiculo->soat_fin->format('d/m/Y') }} — <span class="font-bold {{ $vehiculo->soat_estado === 'VIGENTE' ? 'text-emerald-700' : 'text-red-700' }}">{{ $vehiculo->soat_estado }}</span></dd></div>
                                                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tecnomecánica (fin)</dt><dd class="mt-0.5 text-zinc-900">{{ $vehiculo->tecnomecanica_fin->format('d/m/Y') }} — <span class="font-bold {{ $vehiculo->tecnomecanica_estado === 'VIGENTE' ? 'text-emerald-700' : 'text-red-700' }}">{{ $vehiculo->tecnomecanica_estado }}</span></dd></div>
                                                                </dl>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()?->puedeEditar() ? 8 : 7 }}" class="px-3 py-8 text-center text-zinc-500">
                            No hay empresas registradas.
                            @if (auth()->user()?->puedeEditar())
                            <a href="{{ route('empresas.create') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Crear una</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($empresas->hasPages())
        <div class="mt-4 text-sm">
            {{ $empresas->links() }}
        </div>
    @endif
    </div>

    <script>
        (function () {
            function togglePanel(panel, trigger, chevron, expanded) {
                if (!panel || !trigger) return;
                panel.hidden = !expanded;
                panel.classList.toggle('hidden', !expanded);
                trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                if (chevron) {
                    chevron.classList.toggle('rotate-90', expanded);
                }
            }

            document.querySelectorAll('[data-empresa-toggle]').forEach(function (fila) {
                fila.addEventListener('click', function (event) {
                    if (event.target.closest('[data-acciones]')) return;

                    var id = fila.getAttribute('data-empresa-toggle');
                    var panel = document.querySelector('[data-empresa-panel="' + id + '"]');
                    var chevron = fila.querySelector('.empresa-chevron');
                    var abierto = fila.getAttribute('aria-expanded') === 'true';

                    document.querySelectorAll('[data-empresa-toggle]').forEach(function (otra) {
                        if (otra === fila) return;
                        var otroId = otra.getAttribute('data-empresa-toggle');
                        togglePanel(
                            document.querySelector('[data-empresa-panel="' + otroId + '"]'),
                            otra,
                            otra.querySelector('.empresa-chevron'),
                            false
                        );
                        otra.classList.remove('bg-emerald-50');
                    });

                    togglePanel(panel, fila, chevron, !abierto);
                    fila.classList.toggle('bg-emerald-50', !abierto);
                });
            });

            document.querySelectorAll('[data-categoria-toggle]').forEach(function (boton) {
                boton.addEventListener('click', function (event) {
                    event.stopPropagation();
                    var id = boton.getAttribute('data-categoria-toggle');
                    var panel = document.querySelector('[data-categoria-panel="' + id + '"]');
                    var chevron = boton.querySelector('.categoria-chevron');
                    var abierto = boton.getAttribute('aria-expanded') === 'true';
                    var contenedor = boton.closest('[data-empresa-panel]');

                    if (contenedor) {
                        contenedor.querySelectorAll('[data-categoria-toggle]').forEach(function (otro) {
                            if (otro === boton) return;
                            var otroId = otro.getAttribute('data-categoria-toggle');
                            togglePanel(
                                document.querySelector('[data-categoria-panel="' + otroId + '"]'),
                                otro,
                                otro.querySelector('.categoria-chevron'),
                                false
                            );
                        });
                    }

                    togglePanel(panel, boton, chevron, !abierto);
                });
            });

            document.querySelectorAll('[data-item-toggle]').forEach(function (boton) {
                boton.addEventListener('click', function (event) {
                    event.stopPropagation();
                    var id = boton.getAttribute('data-item-toggle');
                    var panel = document.querySelector('[data-item-panel="' + id + '"]');
                    var chevron = boton.querySelector('.item-chevron');
                    var abierto = boton.getAttribute('aria-expanded') === 'true';
                    var grupo = boton.closest('[data-item-grupo]');

                    if (grupo && grupo.parentElement) {
                        grupo.parentElement.querySelectorAll('[data-item-grupo]').forEach(function (otro) {
                            if (otro === grupo) return;
                            var otroBoton = otro.querySelector('[data-item-toggle]');
                            var otroId = otroBoton ? otroBoton.getAttribute('data-item-toggle') : null;
                            togglePanel(
                                otroId ? document.querySelector('[data-item-panel="' + otroId + '"]') : null,
                                otroBoton,
                                otro.querySelector('.item-chevron'),
                                false
                            );
                        });
                    }

                    togglePanel(panel, boton, chevron, !abierto);
                });
            });
        })();
    </script>
@endsection
