@php
    /** @var \App\Models\ContratistaExterno|\App\Models\ContratistaInterno $contratista */
    /** @var string $tipo  'externo' | 'interno' */
    $tipoLabel = $tipo === 'interno' ? 'Interno' : 'Externo';
@endphp

<div class="item-grupo" data-item-grupo="{{ $tipo }}-{{ $contratista->id }}">
    <button type="button" class="item-toggle flex w-full items-center gap-2 px-4 py-2.5 text-left hover:bg-zinc-50" data-item-toggle="{{ $tipo }}-{{ $contratista->id }}" aria-expanded="false">
        <svg class="item-chevron size-4 shrink-0 text-zinc-500 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium text-zinc-900">{{ $contratista->nombres_apellidos }}</span>
        <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-zinc-600">{{ $tipoLabel }}</span>
        <span class="text-xs text-zinc-500">{{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</span>
        @if ($contratista->estado === 'VIGENTE')
            <span class="ml-auto rounded px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700">Vigente</span>
        @elseif ($contratista->estado === 'VENCIDA')
            <span class="ml-auto rounded px-2 py-0.5 text-[10px] font-bold uppercase text-red-700">Vencida</span>
        @else
            <span class="ml-auto rounded px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-400">Sin I/R</span>
        @endif
    </button>
    <div class="item-detalle hidden border-t border-zinc-100 bg-zinc-50/50 px-4 py-3" data-item-panel="{{ $tipo }}-{{ $contratista->id }}" hidden>
        <dl class="mb-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nombres y apellidos</dt><dd class="mt-0.5 font-medium text-zinc-900">{{ $contratista->nombres_apellidos }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tipo</dt><dd class="mt-0.5 text-zinc-900">{{ $tipoLabel }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->tipo_documento }} {{ $contratista->numero_documento }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Empresa</dt><dd class="mt-0.5 text-zinc-900">{{ $empresa->nombre }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">ARL</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->arl ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fecha última I/R</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->fecha_ultima_ir?->format('d/m/Y') ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vencimiento</dt><dd class="mt-0.5 text-zinc-900">{{ $contratista->fecha_vencimiento?->format('d/m/Y') ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Días faltantes</dt><dd class="mt-0.5 font-bold tabular-nums text-zinc-900">{{ $contratista->dias_faltantes ?? '—' }}</dd></div>
            <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Estado</dt><dd class="mt-0.5"><span class="font-bold {{ $contratista->estado === 'VIGENTE' ? 'text-emerald-700' : ($contratista->estado === 'VENCIDA' ? 'text-red-700' : 'text-zinc-400') }}">{{ $contratista->estado }}</span></dd></div>
        </dl>
        @include('contratistas._detalle_campos_adicionales', ['contratista' => $contratista])
        <p class="mb-2 mt-3 text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Control mensual {{ $anioActual }}</p>
        <div class="flex flex-wrap gap-1">
            @foreach (\App\Models\ContratistaInterno::MESES as $mes => $abrev)
                @php $estadoMesEmp = $contratista->estadoMes($anioActual, $mes); @endphp
                <span class="inline-flex h-7 min-w-7 items-center justify-center rounded text-[10px] font-bold {{ $estadoMesEmp === 'ok' ? 'bg-emerald-100 text-emerald-800' : ($estadoMesEmp === 'rechazado' ? 'bg-red-100 text-red-700' : 'bg-zinc-100 text-zinc-400') }}" title="{{ $abrev }}">
                    {{ $estadoMesEmp === 'ok' ? 'OK' : $abrev }}
                </span>
            @endforeach
        </div>
    </div>
</div>
