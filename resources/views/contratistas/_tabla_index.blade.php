@php
    /** @var string $tipo  'externo' | 'interno' */
    $rutaBase = 'contratistas-'.$tipo.'s';
    $puedeEditar = auth()->user()?->puedeEditar();
    $totalColumnas = 9 + count(\App\Models\ContratistaInterno::MESES) + 1 + ($puedeEditar ? 1 : 0);
@endphp

<div class="rounded-lg border border-zinc-200">
    <table class="w-full table-auto text-left text-sm">
        <thead>
            <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                <th class="min-w-[10rem] px-3 py-3">Nombres y apellidos</th>
                <th class="px-3 py-3">Tipo doc.</th>
                <th class="px-3 py-3">Documento</th>
                <th class="min-w-[8rem] px-3 py-3">Empresa</th>
                <th class="px-3 py-3">ARL</th>
                <th class="px-3 py-3">Última I/R</th>
                <th class="px-3 py-3">Días falt.</th>
                <th class="px-3 py-3">Vencimiento</th>
                <th class="px-3 py-3">Estado I/R</th>
                @foreach (\App\Models\ContratistaInterno::MESES as $mes => $abrev)
                    <th class="w-9 px-0.5 py-3 text-center text-[10px]">{{ $abrev }}</th>
                @endforeach
                <th class="px-3 py-3">Registro</th>
                @if ($puedeEditar)
                <th class="w-24 px-2 py-3 text-center">Acciones</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200">
            @forelse ($contratistas as $c)
                @php
                    $estadoFiltro = match ($c->estado) {
                        'VIGENTE', 'VENCIDA' => $c->estado,
                        default => 'SIN_REGISTRO',
                    };
                @endphp
                <tr
                    class="contratista-fila cursor-pointer bg-white hover:bg-zinc-50/80 {{ ! $c->activo ? 'opacity-60' : '' }}"
                    data-contratista-toggle="{{ $tipo }}-{{ $c->id }}"
                    aria-expanded="false"
                    @if ($habilitarFiltrosCliente ?? false)
                        data-filtro-modulo="{{ $tipo }}s"
                        data-filtro-nombre="{{ mb_strtolower($c->nombres_apellidos, 'UTF-8') }}"
                        data-filtro-tipo-documento="{{ $c->tipo_documento }}"
                        data-filtro-documento="{{ mb_strtolower(preg_replace('/\s+/', '', $c->numero_documento), 'UTF-8') }}"
                        data-filtro-arl="{{ mb_strtolower($c->arl ?? '', 'UTF-8') }}"
                        data-filtro-estado="{{ $estadoFiltro }}"
                    @endif
                >
                    <td class="px-3 py-2 font-medium text-zinc-900">
                        <span class="inline-flex items-center gap-2">
                            <svg class="contratista-chevron size-4 shrink-0 text-emerald-700 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                            </svg>
                            {{ $c->nombres_apellidos }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-zinc-800">{{ $c->tipo_documento }}</td>
                    <td class="px-3 py-2 text-zinc-800">{{ $c->numero_documento }}</td>
                    <td class="px-3 py-2 break-words text-zinc-800">{{ $c->empresa?->nombre ?? '—' }}</td>
                    <td class="px-3 py-2 text-zinc-800">{{ $c->arl ?? '—' }}</td>
                    <td class="px-3 py-2 text-zinc-800">{{ $c->fecha_ultima_ir?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-3 py-2 font-semibold tabular-nums text-zinc-900">{{ $c->dias_faltantes ?? '—' }}</td>
                    <td class="px-3 py-2 text-zinc-800">{{ $c->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</td>
                    <td class="px-3 py-2">
                        @if ($c->estado === 'VIGENTE')
                            <span class="font-bold text-emerald-700">VIGENTE</span>
                        @elseif ($c->estado === 'VENCIDA')
                            <span class="font-bold text-red-700">VENCIDA</span>
                        @else
                            <span class="font-semibold text-zinc-400">—</span>
                        @endif
                    </td>
                    @foreach (\App\Models\ContratistaInterno::MESES as $mes => $abrev)
                        @php $estadoMes = $c->estadoMes($anio, $mes); @endphp
                        <td class="px-0.5 py-2 text-center">
                            @if ($puedeEditar)
                            <form action="{{ route($rutaBase.'.toggle-mes', $c) }}" method="post" class="inline" onclick="event.stopPropagation()">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="anio" value="{{ $anio }}">
                                <input type="hidden" name="mes" value="{{ $mes }}">
                                <button
                                    type="submit"
                                    title="{{ $abrev }} — clic para cambiar estado (vacío → OK → no vigente)"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded text-[10px] font-bold transition {{ $estadoMes === 'ok' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : ($estadoMes === 'rechazado' ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'text-zinc-300 hover:bg-zinc-100 hover:text-zinc-500') }}"
                                >
                                    {{ $estadoMes === 'ok' ? 'OK' : ($estadoMes === 'rechazado' ? '✕' : '·') }}
                                </button>
                            </form>
                            @else
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded text-[10px] font-bold {{ $estadoMes === 'ok' ? 'bg-emerald-100 text-emerald-800' : ($estadoMes === 'rechazado' ? 'bg-red-100 text-red-700' : 'text-zinc-300') }}">
                                {{ $estadoMes === 'ok' ? 'OK' : ($estadoMes === 'rechazado' ? '✕' : '·') }}
                            </span>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-3 py-2">
                        @if ($c->activo)
                            <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-800">Activo</span>
                        @else
                            <span class="rounded bg-zinc-200 px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-700">Inactivo</span>
                        @endif
                    </td>
                    @if ($puedeEditar)
                    <td class="px-3 py-2">
                        @include('contratistas._acciones_contratista', [
                            'contratista' => $c,
                            'editRoute' => route($rutaBase.'.edit', $c),
                            'toggleActivoRoute' => route($rutaBase.'.toggle-activo', $c),
                            'anio' => $anio,
                        ])
                    </td>
                    @endif
                </tr>
                <tr class="hidden bg-zinc-50/50" data-contratista-panel="{{ $tipo }}-{{ $c->id }}" hidden>
                    <td colspan="{{ $totalColumnas }}" class="border-t border-zinc-100 px-4 py-4">
                        <dl class="mb-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nombres y apellidos</dt><dd class="mt-0.5 font-medium text-zinc-900">{{ $c->nombres_apellidos }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento</dt><dd class="mt-0.5 text-zinc-900">{{ $c->tipo_documento }} {{ $c->numero_documento }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Empresa</dt><dd class="mt-0.5 text-zinc-900">{{ $c->empresa?->nombre ?? '—' }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">ARL</dt><dd class="mt-0.5 text-zinc-900">{{ $c->arl ?? '—' }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fecha última I/R</dt><dd class="mt-0.5 text-zinc-900">{{ $c->fecha_ultima_ir?->format('d/m/Y') ?? '—' }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vencimiento</dt><dd class="mt-0.5 text-zinc-900">{{ $c->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Días faltantes</dt><dd class="mt-0.5 font-bold tabular-nums text-zinc-900">{{ $c->dias_faltantes ?? '—' }}</dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Estado I/R</dt><dd class="mt-0.5"><span class="font-bold {{ $c->estado === 'VIGENTE' ? 'text-emerald-700' : ($c->estado === 'VENCIDA' ? 'text-red-700' : 'text-zinc-400') }}">{{ $c->estado }}</span></dd></div>
                            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Registro</dt><dd class="mt-0.5 text-zinc-900">{{ $c->activo ? 'Activo' : 'Inactivo' }}</dd></div>
                        </dl>
                        @include('contratistas._detalle_campos_adicionales', ['contratista' => $c])
                        <p class="mb-2 mt-3 text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Control mensual {{ $anio }}</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach (\App\Models\ContratistaInterno::MESES as $mes => $abrev)
                                @php $estadoMesDet = $c->estadoMes($anio, $mes); @endphp
                                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded text-[10px] font-bold {{ $estadoMesDet === 'ok' ? 'bg-emerald-100 text-emerald-800' : ($estadoMesDet === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-zinc-100 text-zinc-400') }}" title="{{ $abrev }}">
                                    {{ $estadoMesDet === 'ok' ? 'OK' : $abrev }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $totalColumnas }}" class="px-3 py-8 text-center text-zinc-500">
                        No hay contratistas {{ $tipo }}s registrados.
                        @if ($puedeEditar)
                        <a href="{{ route($rutaBase.'.create') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Registrar el primero</a>
                        @endif
                    </td>
                </tr>
            @endforelse
            @if (($habilitarFiltrosCliente ?? false) && $contratistas->isNotEmpty())
                <tr id="filtro-{{ $tipo }}s-sin-resultados" class="hidden">
                    <td colspan="{{ $totalColumnas }}" class="px-3 py-8 text-center text-zinc-500">
                        No hay contratistas {{ $tipo }}s que coincidan con los filtros.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
