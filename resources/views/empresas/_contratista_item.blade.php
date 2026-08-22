@php
    /** @var \App\Models\ContratistaExterno|\App\Models\ContratistaInterno $contratista */
    /** @var string $tipo  'externo' | 'interno' */
    $tipoLabel = $tipo === 'interno' ? 'Interno' : 'Externo';
    $mostrarCamposIr = $tipo === 'externo';
    $mostrarPlanillaSs = $empresa->planillaSsPorEmpleado() && $tipo === 'interno';
    $estadoSs = null;
    $diasSs = null;
    $limiteSs = null;
    if ($mostrarPlanillaSs) {
        $contratista->setRelation('empresa', $empresa);
        $estadoSs = $contratista->estadoLimiteSs();
        $diasSs = $contratista->diasParaLimiteSs();
        $limiteSs = $contratista->limiteEfectivo();
    }
@endphp

<div class="item-grupo {{ ! $contratista->activo ? 'bg-zinc-50/80' : '' }}" data-item-grupo="{{ $tipo }}-{{ $contratista->id }}">
    <button type="button" class="item-toggle flex w-full flex-wrap items-center gap-2 px-4 py-2.5 text-left hover:bg-zinc-50 {{ ! $contratista->activo ? 'opacity-80' : '' }}" data-item-toggle="{{ $tipo }}-{{ $contratista->id }}" aria-expanded="false">
        <svg class="item-chevron size-4 shrink-0 text-zinc-500 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium {{ $contratista->activo ? 'text-zinc-900' : 'text-zinc-500 line-through decoration-zinc-400' }}">{{ $contratista->nombres_apellidos }}</span>
        <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-zinc-600">{{ $tipoLabel }}</span>
        @if (! $contratista->activo)
            <span class="rounded bg-zinc-300 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-zinc-800">Inactivo</span>
        @endif
        <span class="text-xs text-zinc-500">{{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</span>
        @if ($mostrarPlanillaSs)
            <span class="text-xs tabular-nums text-zinc-600">Límite SS: {{ $limiteSs?->format('d/m/Y') ?? '—' }}</span>
            <span class="ml-auto">@include('empresas._badge_estado_ss', ['estado' => $estadoSs, 'dias' => $diasSs])</span>
        @elseif ($mostrarCamposIr)
            @if ($contratista->estado === 'VIGENTE')
                <span class="ml-auto rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $contratista->activo ? 'bg-emerald-100 text-emerald-800' : 'bg-emerald-50 text-emerald-600' }}">Vigente</span>
            @elseif ($contratista->estado === 'VENCIDA')
                <span class="ml-auto rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $contratista->activo ? 'bg-red-100 text-red-800' : 'bg-red-50 text-red-600' }}">Vencida</span>
            @else
                <span class="ml-auto rounded px-2 py-0.5 text-[10px] font-bold uppercase {{ $contratista->activo ? 'bg-zinc-100 text-zinc-500' : 'bg-zinc-100 text-zinc-400' }}">Sin I/R</span>
            @endif
        @endif
    </button>
    <div class="item-detalle hidden border-t border-zinc-100 bg-zinc-50/50 px-4 py-3" data-item-panel="{{ $tipo }}-{{ $contratista->id }}" hidden>
        <dl class="mb-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nombres y apellidos</dt><dd class="mt-0.5 font-medium text-zinc-900">{{ $contratista->nombres_apellidos }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tipo</dt><dd class="mt-0.5 text-zinc-900">{{ $tipoLabel }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Empresa</dt><dd class="mt-0.5 text-zinc-900">{{ $empresa->nombre }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">ARL</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->arl ?? '—' }}</dd></div>
            @if ($mostrarPlanillaSs)
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tipo planilla</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->tipo_planilla ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fecha límite SS</dt><dd class="mt-0.5 text-zinc-900">{{ $limiteSs?->format('d/m/Y') ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Días para límite SS</dt><dd class="mt-0.5 font-bold tabular-nums text-zinc-900">{{ $diasSs ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Estado SS</dt><dd class="mt-0.5">@include('empresas._badge_estado_ss', ['estado' => $estadoSs, 'dias' => $diasSs])</dd></div>
            @endif
            @if ($mostrarCamposIr)
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fecha última I/R</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->fecha_ultima_ir?->format('d/m/Y') ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vencimiento</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Días faltantes</dt><dd class="mt-0.5 font-bold tabular-nums text-zinc-900">{{ $contratista->dias_faltantes ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Estado I/R</dt><dd class="mt-0.5"><span class="font-bold {{ $contratista->estado === 'VIGENTE' ? 'text-emerald-700' : ($contratista->estado === 'VENCIDA' ? 'text-red-700' : 'text-zinc-400') }}">{{ $contratista->estado }}</span></dd></div>
            @endif
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Registro</dt><dd class="mt-0.5">
                @if ($contratista->activo)
                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-800">Activo</span>
                @else
                    <span class="rounded bg-zinc-300 px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-800">Inactivo</span>
                @endif
            </dd></div>
        </dl>
        @include('contratistas._detalle_campos_adicionales', ['contratista' => $contratista])
        <p class="mb-2 mt-3 text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Control mensual {{ $anioActual }}</p>
        <div class="flex flex-wrap gap-1">
            @foreach (\App\Models\ContratistaInterno::MESES as $mes => $abrev)
                @if ($mostrarPlanillaSs)
                    @php $uiEmp = $contratista->controlMesSsUi($anioActual, $mes, false); @endphp
                    <span
                        title="{{ $uiEmp['titulo'] }}"
                        class="relative inline-flex h-7 min-w-7 items-center justify-center rounded px-0.5 text-[10px] font-bold {{ $uiEmp['estado'] === 'ok' ? 'bg-emerald-100 text-emerald-800' : ($uiEmp['estado'] === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-zinc-200 text-zinc-600') }}"
                    >
                        {{ $uiEmp['estado'] === 'rechazado' ? '✕' : $uiEmp['abrev'] }}
                        @if ($uiEmp['mostrar_badge'])
                            <span class="absolute -right-1.5 -top-1.5 inline-flex min-h-[14px] min-w-[14px] items-center justify-center rounded-full px-0.5 text-[9px] font-bold leading-none shadow {{ $uiEmp['urgencia'] === 'proxima' ? 'bg-amber-500 text-white' : 'bg-emerald-700 text-white' }}">
                                {{ $uiEmp['dias'] }}
                            </span>
                        @endif
                    </span>
                @else
                @php $estadoMesEmp = $contratista->estadoMes($anioActual, $mes); @endphp
                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded px-0.5 text-[10px] font-bold {{ $estadoMesEmp === 'ok' ? 'bg-emerald-100 text-emerald-800' : ($estadoMesEmp === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-zinc-200 text-zinc-600') }}" title="{{ $abrev }}">
                    {{ $estadoMesEmp === 'rechazado' ? '✕' : $abrev }}
                </span>
                @endif
            @endforeach
        </div>
    </div>
</div>
