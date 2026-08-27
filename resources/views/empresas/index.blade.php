@extends('layouts.app')

@section('title', 'Empresas — '.config('app.name'))

@section('containerClass', 'max-w-none w-full')

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

        <form method="get" action="{{ route('empresas.index') }}" id="filtros-empresas" class="mb-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4">
            <p class="mb-3 text-xs text-zinc-600">Busque en <strong>todas las empresas</strong> del sistema, aunque estén en otra página. Pulse <strong>Filtrar</strong> o <strong>Enter</strong>.</p>
            <p class="mb-3 text-xs text-zinc-600">El campo <strong>Buscar</strong> también encuentra la empresa por el nombre o documento de un contratista, o por la placa de un vehículo.</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                <div class="sm:col-span-2 lg:col-span-1 xl:col-span-2">
                    <label for="filtro-buscar" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Buscar</label>
                    <input
                        type="text"
                        name="buscar"
                        id="filtro-buscar"
                        value="{{ $buscar }}"
                        placeholder="Empresa, contratista, documento o placa"
                        autocomplete="off"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                </div>
                <div>
                    <label for="filtro-nit" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">NIT</label>
                    <input
                        type="text"
                        name="nit"
                        id="filtro-nit"
                        value="{{ $nit }}"
                        placeholder="NIT…"
                        autocomplete="off"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                </div>
                <div>
                    <label for="filtro-estado" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Estado SS</label>
                    <select
                        name="estado_ss"
                        id="filtro-estado"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                        <option value="">Todos</option>
                        <option value="VIGENTE" @selected($estadoSs === 'VIGENTE')>Vigente</option>
                        <option value="PRÓXIMA A VENCER" @selected($estadoSs === 'PRÓXIMA A VENCER')>Próxima a vencer</option>
                        <option value="VENCIDA" @selected($estadoSs === 'VENCIDA')>Vencida</option>
                        <option value="SIN FECHA" @selected($estadoSs === 'SIN FECHA')>Sin fecha límite</option>
                    </select>
                </div>
                <div>
                    <label for="filtro-tipo-empresa" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Clasificación</label>
                    <select
                        name="tipo_empresa"
                        id="filtro-tipo-empresa"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                        <option value="">Todas</option>
                        <option value="INTERNA" @selected($tipoEmpresa === 'INTERNA')>Interna</option>
                        <option value="EXTERNA" @selected($tipoEmpresa === 'EXTERNA')>Externa</option>
                        <option value="SIN_CLASIFICAR" @selected($tipoEmpresa === 'SIN_CLASIFICAR')>Sin clasificar</option>
                    </select>
                </div>
                <div>
                    <label for="filtro-planilla" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Tipo planilla</label>
                    <select
                        name="planilla"
                        id="filtro-planilla"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                        <option value="">Todas</option>
                        @foreach ($planillas as $opcionPlanilla)
                            <option value="{{ $opcionPlanilla }}" @selected($planilla === $opcionPlanilla)>{{ $opcionPlanilla }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
                    <button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                        Filtrar
                    </button>
                    @if ($hayFiltros)
                        <a href="{{ route('empresas.index') }}" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50">
                            Limpiar
                        </a>
                    @endif
                </div>
            </div>
            @if ($hayFiltros)
                <p class="mt-3 text-xs font-medium text-emerald-800">
                    {{ $empresas->total() }} empresa{{ $empresas->total() === 1 ? '' : 's' }} encontrada{{ $empresas->total() === 1 ? '' : 's' }} en total.
                    @if ($empresas->hasPages())
                        Página {{ $empresas->currentPage() }} de {{ $empresas->lastPage() }}.
                    @endif
                </p>
            @endif
        </form>

        <p class="mb-4 text-xs text-zinc-600 md:text-sm">Haz clic en una empresa para ver <strong>Contratistas</strong> y <strong>Vehículos</strong>. Las empresas <strong>internas dependientes</strong> también muestran planilla SS de la empresa; las <strong>internas independientes</strong> la llevan cada empleado en Contratistas.</p>

        <div class="rounded-lg border border-zinc-200">
        <table class="w-full table-auto text-left text-sm">
            <thead>
                <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                    <th class="w-8 px-2 py-3"></th>
                    <th class="px-3 py-3">Nombre</th>
                    <th class="px-3 py-3">NIT</th>
                    <th class="px-3 py-3">Teléfono</th>
                    <th class="px-3 py-3">Correos</th>
                    <th class="px-3 py-3">Límite SS</th>
                    <th class="px-3 py-3">Estado SS</th>
                    <th class="px-3 py-3">Clasificación</th>
                    <th class="px-3 py-3">Tipo planilla</th>
                    @if (auth()->user()?->puedeEditar())
                    <th class="w-20 px-2 py-3 text-center">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200">
                @forelse ($empresas as $empresa)
                    @php
                        $esInterna = $empresa->esInterna();
                        $esIndependiente = $empresa->planillaSsPorEmpleado();
                        $llevaPlanillaEmpresa = $empresa->llevaPlanillaSs();
                        $planillaVigenteAdjunta = $llevaPlanillaEmpresa ? $empresa->planillaVigenteAdjunta() : false;
                        $limiteVencido = $llevaPlanillaEmpresa && $empresa->estado_limite === 'VENCIDA';
                        $resumenSs = $esIndependiente ? $empresa->resumenVigenciaSsContratistas() : null;
                        $estadoListado = $empresa->estadoLimiteParaListado();
                        $muestraSsEnListado = $esInterna;
                    @endphp
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
                            <div>{{ $empresa->nombre }}</div>
                            <div class="mt-1 flex flex-wrap gap-1">
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-900">
                                    {{ $empresa->contratistas_externos_count + $empresa->contratistas_internos_count }} contratista{{ ($empresa->contratistas_externos_count + $empresa->contratistas_internos_count) === 1 ? '' : 's' }}
                                </span>
                                @if ($empresa->vehiculos_count > 0)
                                    <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-zinc-700">
                                        {{ $empresa->vehiculos_count }} vehículo{{ $empresa->vehiculos_count === 1 ? '' : 's' }}
                                    </span>
                                @endif
                                @if ($llevaPlanillaEmpresa)
                                    @if ($planillaVigenteAdjunta)
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-800" title="Planilla SS adjunta para la vigencia actual">Planilla SS</span>
                                    @elseif ($limiteVencido)
                                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-800" title="Fecha límite vencida; renueve y adjunte nueva planilla">SS vencida</span>
                                    @elseif ($empresa->limite !== null)
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-900" title="Planilla SS pendiente para la vigencia actual">SS pendiente</span>
                                    @endif
                                @elseif ($esIndependiente && $resumenSs)
                                    @if ($resumenSs['vencidas'] > 0)
                                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-800">{{ $resumenSs['vencidas'] }} SS vencida{{ $resumenSs['vencidas'] === 1 ? '' : 's' }}</span>
                                    @endif
                                    @if ($resumenSs['proximas'] > 0)
                                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-900">{{ $resumenSs['proximas'] }} por vencer</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-2 text-zinc-800">{{ $empresa->nit ?? '—' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-zinc-800">{{ $empresa->telefono ?? '—' }}</td>
                        <td class="px-3 py-2 text-zinc-800">
                            @if (is_array($empresa->correos) && count($empresa->correos) > 0)
                                <div class="flex flex-col gap-0.5 break-all">
                                    @foreach ($empresa->correos as $correo)
                                        <span class="text-xs md:text-sm" title="{{ $correo }}">{{ $correo }}</span>
                                    @endforeach
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-3 py-2 text-zinc-800">
                            @if (! $muestraSsEnListado)
                                <span class="text-zinc-400">—</span>
                            @elseif ($esIndependiente && $resumenSs && $resumenSs['total'] > 0)
                                <div class="flex max-w-xs flex-col gap-1 text-xs">
                                    @foreach ($resumenSs['items'] as $item)
                                        @if ($item['tipo'] !== 'interno')
                                            @continue
                                        @endif
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="truncate font-medium text-zinc-900" title="{{ $item['nombre'] }}">{{ Str::limit($item['nombre'], 22) }}</span>
                                            <span class="tabular-nums text-zinc-600">{{ $item['limite']?->format('d/m/Y') ?? '—' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="whitespace-nowrap">{{ $empresa->limite?->format('d/m/Y') ?? '—' }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if (! $muestraSsEnListado)
                                <span class="text-zinc-400">—</span>
                            @else
                            @php $estadoLimite = $estadoListado; @endphp
                            @if ($estadoLimite === null)
                                <span class="text-zinc-400">—</span>
                            @elseif ($estadoLimite === 'VIGENTE')
                                <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-800">Vigente</span>
                            @elseif ($estadoLimite === 'PRÓXIMA A VENCER')
                                <span class="rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800">Próxima a vencer</span>
                            @elseif ($estadoLimite === 'VENCIDA')
                                @php
                                    $diasVencida = $esIndependiente && $resumenSs
                                        ? null
                                        : abs($empresa->dias_para_limite ?? 0);
                                    $sufijoVencida = match (true) {
                                        $diasVencida !== null => ' ('.$diasVencida.' día'.($diasVencida === 1 ? '' : 's').')',
                                        $esIndependiente && $resumenSs => ' ('.$resumenSs['vencidas'].' pers.)',
                                        default => '',
                                    };
                                @endphp
                                <span class="rounded bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-red-800">Vencida{{ $sufijoVencida }}</span>
                            @endif
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if ($empresa->tipo_empresa === \App\Support\EmpresaTipo::INTERNA)
                                <span class="rounded bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-violet-800">Interna</span>
                            @elseif ($empresa->tipo_empresa === \App\Support\EmpresaTipo::EXTERNA)
                                <span class="rounded bg-sky-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-sky-800">Externa</span>
                            @else
                                <span class="rounded bg-zinc-200 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-zinc-700">Sin clasificar</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-zinc-800">
                            @if ($esInterna)
                                {{ $empresa->planilla ?? '—' }}
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                        @if (auth()->user()?->puedeEditar())
                        <td class="px-2 py-2 text-center" data-acciones>
                            <div class="inline-flex items-center justify-center gap-1" data-acciones onclick="event.stopPropagation()">
                                <a
                                    href="{{ route('empresas.edit', $empresa) }}"
                                    title="Editar"
                                    aria-label="Editar"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-base transition hover:bg-emerald-50"
                                    data-acciones
                                >✏️</a>
                                <form action="{{ route('empresas.destroy', $empresa) }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar esta empresa? Esta acción no se puede deshacer.');" data-acciones>
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        title="Eliminar"
                                        aria-label="Eliminar"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-base transition hover:bg-red-50"
                                    >🗑️</button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    <tr class="empresa-detalle hidden bg-zinc-50/80" data-empresa-panel="{{ $empresa->id }}" hidden>
                        <td colspan="{{ auth()->user()?->puedeEditar() ? 10 : 9 }}" class="px-4 py-3">
                            @php
                                $totalContratistasEmpresa = $empresa->contratistasExternos->count() + $empresa->contratistasInternos->count();
                                $categoriaContratistasId = 'empresa-'.$empresa->id.'-contratistas';
                                $categoriaVehiculosId = 'empresa-'.$empresa->id.'-vehiculos';
                                $categoriaPlanillaId = 'empresa-'.$empresa->id.'-planilla';
                            @endphp
                            <div class="divide-y divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 bg-white">
                                @if ($llevaPlanillaEmpresa)
                                {{-- Categoría Planilla (solo internas dependientes) --}}
                                <div class="categoria-grupo" data-categoria-grupo="{{ $categoriaPlanillaId }}">
                                    <button
                                        type="button"
                                        class="categoria-toggle flex w-full items-center gap-2 px-4 py-3 text-left hover:bg-zinc-50"
                                        data-categoria-toggle="{{ $categoriaPlanillaId }}"
                                        aria-expanded="false"
                                    >
                                        <svg class="categoria-chevron size-4 shrink-0 text-emerald-700 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-bold uppercase tracking-wide text-emerald-800">Planilla</span>
                                        @if ($planillaVigenteAdjunta)
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-900">Adjuntada</span>
                                        @elseif ($limiteVencido)
                                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-800">Límite vencido</span>
                                        @elseif ($empresa->limite !== null)
                                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900">Pendiente</span>
                                        @else
                                            <span class="rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold text-zinc-700">Sin vigencia</span>
                                        @endif
                                    </button>
                                    <div class="categoria-panel hidden border-t border-zinc-100 bg-zinc-50/40" data-categoria-panel="{{ $categoriaPlanillaId }}" hidden>
                                        @include('empresas._planilla_section', ['empresa' => $empresa])
                                    </div>
                                </div>
                                @endif

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
                                            @if ($esIndependiente && $resumenSs)
                                                @if ($resumenSs['vigentes'] > 0)
                                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800">{{ $resumenSs['vigentes'] }} vig.</span>
                                                @endif
                                                @if ($resumenSs['proximas'] > 0)
                                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900">{{ $resumenSs['proximas'] }} por vencer</span>
                                                @endif
                                                @if ($resumenSs['vencidas'] > 0)
                                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold text-red-800">{{ $resumenSs['vencidas'] }} venc.</span>
                                                @endif
                                            @endif
                                        </button>
                                        <div class="categoria-panel hidden border-t border-zinc-100 bg-zinc-50/40" data-categoria-panel="{{ $categoriaContratistasId }}" hidden>
                                            @if ($totalContratistasEmpresa === 0)
                                                <p class="px-4 py-3 text-sm text-zinc-500">No hay contratistas vinculados a esta empresa.</p>
                                            @else
                                                @if ($esIndependiente)
                                                    @include('empresas._contratistas_resumen_ss', ['empresa' => $empresa])
                                                @endif
                                                <div class="divide-y divide-zinc-200 border-t border-zinc-100 bg-white">
                                                    @foreach ($empresa->contratistasExternos as $contratista)
                                                        @include('empresas._contratista_item', ['contratista' => $contratista, 'tipo' => 'externo', 'empresa' => $empresa, 'anioActual' => $anioActual])
                                                    @endforeach
                                                    @php
                                                        $internosOrdenados = $empresa->contratistasInternos->sort(function ($a, $b) use ($empresa) {
                                                            $a->setRelation('empresa', $empresa);
                                                            $b->setRelation('empresa', $empresa);
                                                            $orden = ['VENCIDA' => 0, 'PRÓXIMA A VENCER' => 1, 'VIGENTE' => 2, null => 3];
                                                            $oa = $orden[$a->estadoLimiteSs()] ?? 4;
                                                            $ob = $orden[$b->estadoLimiteSs()] ?? 4;
                                                            if ($oa !== $ob) {
                                                                return $oa <=> $ob;
                                                            }

                                                            return strcasecmp($a->nombres_apellidos, $b->nombres_apellidos);
                                                        })->values();
                                                    @endphp
                                                    @foreach ($internosOrdenados as $contratista)
                                                        @include('empresas._contratista_item', ['contratista' => $contratista, 'tipo' => 'interno', 'empresa' => $empresa, 'anioActual' => $anioActual])
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
                                                                    @foreach (\App\Models\Vehiculo::DOCUMENTOS as $campoDoc => $etiquetaDoc)
                                                                        <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">{{ $etiquetaDoc }}</dt><dd class="mt-0.5 text-zinc-900">@if ($vehiculo->{$campoDoc})<a href="{{ \App\Services\VehiculoDocumentoStorage::urlPublica($vehiculo->{$campoDoc}) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 underline hover:text-emerald-800">Ver</a>@else—@endif</dd></div>
                                                                    @endforeach
                                                                    <div>
                                                                        <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Inspección sanitaria</dt>
                                                                        <dd class="mt-0.5 text-zinc-900">
                                                                            @if ($vehiculo->inspeccion_sanitaria)
                                                                                @if ($vehiculo->inspeccion_sanitaria_fin)
                                                                                    {{ $vehiculo->inspeccion_sanitaria_fin->format('d/m/Y') }} —
                                                                                    <span class="font-bold {{ $vehiculo->inspeccion_sanitaria_estado === 'VIGENTE' ? 'text-emerald-700' : 'text-red-700' }}">{{ $vehiculo->inspeccion_sanitaria_estado }}</span>
                                                                                @else
                                                                                    Sí
                                                                                @endif
                                                                            @else
                                                                                No
                                                                            @endif
                                                                        </dd>
                                                                    </div>
                                                                </dl>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="tabla-empresas-vacia">
                        <td colspan="{{ auth()->user()?->puedeEditar() ? 10 : 9 }}" class="px-3 py-8 text-center text-zinc-500">
                            @if ($hayFiltros)
                                No hay empresas que coincidan con los filtros.
                                <a href="{{ route('empresas.index') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Limpiar filtros</a>
                            @else
                                No hay empresas registradas.
                                @if (auth()->user()?->puedeEditar())
                                <a href="{{ route('empresas.create') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Crear una</a>
                                @endif
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
            var formFiltros = document.getElementById('filtros-empresas');

            ['filtro-estado', 'filtro-tipo-empresa', 'filtro-planilla'].forEach(function (id) {
                var campo = document.getElementById(id);
                if (!campo || !formFiltros) return;
                campo.addEventListener('change', function () {
                    formFiltros.submit();
                });
            });

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

            (function abrirDesdeUrl() {
                var params = new URLSearchParams(window.location.search);
                var abrir = params.get('abrir');
                if (!abrir || !abrir.startsWith('empresa-')) return;

                var empId = abrir.replace('empresa-', '');
                var categoria = params.get('categoria');
                var item = params.get('item');

                function expandEmpresa() {
                    var fila = document.querySelector('[data-empresa-toggle="' + empId + '"]');
                    if (fila && fila.getAttribute('aria-expanded') !== 'true') fila.click();
                    return fila;
                }

                function expandCategoria(catId) {
                    var btn = document.querySelector('[data-categoria-toggle="' + catId + '"]');
                    if (btn && btn.getAttribute('aria-expanded') !== 'true') btn.click();
                }

                function expandItem(itemId) {
                    var btn = document.querySelector('[data-item-toggle="' + itemId + '"]');
                    if (btn && btn.getAttribute('aria-expanded') !== 'true') btn.click();
                    return btn;
                }

                function resaltarDestino(fila, itemId) {
                    var destino = fila;

                    if (itemId) {
                        var grupo = document.querySelector('[data-item-grupo="' + itemId + '"]');
                        if (grupo) {
                            destino = grupo;
                        }
                    }

                    if (window.resaltarFilaBusqueda) {
                        window.resaltarFilaBusqueda(destino);
                    } else if (destino) {
                        destino.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                setTimeout(function () {
                    var fila = expandEmpresa();
                    if (categoria === 'contratistas' && item) {
                        setTimeout(function () {
                            expandCategoria('empresa-' + empId + '-contratistas');
                            setTimeout(function () {
                                expandItem(item);
                                resaltarDestino(fila, item);
                            }, 80);
                        }, 80);
                    } else if (categoria === 'vehiculos' && item) {
                        setTimeout(function () {
                            expandCategoria('empresa-' + empId + '-vehiculos');
                            setTimeout(function () {
                                expandItem(item);
                                resaltarDestino(fila, item);
                            }, 80);
                        }, 80);
                    } else {
                        resaltarDestino(fila);
                    }
                }, 150);
            })();
        })();
    </script>
@endsection
