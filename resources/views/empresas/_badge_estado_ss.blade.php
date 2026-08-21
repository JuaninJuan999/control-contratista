@php
    /** @var string|null $estado */
    /** @var int|null $dias */
    $estado = $estado ?? null;
    $dias = $dias ?? null;
    $sufijoDias = $dias !== null ? ' ('.abs($dias).' d.)' : '';
@endphp

@if ($estado === 'VIGENTE')
    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-emerald-100 text-emerald-800">SS vigente{{ $sufijoDias }}</span>
@elseif ($estado === 'PRÓXIMA A VENCER')
    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-900">SS por vencer{{ $sufijoDias }}</span>
@elseif ($estado === 'VENCIDA')
    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-red-100 text-red-800">SS vencida{{ $sufijoDias }}</span>
@else
    <span class="rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide bg-zinc-200 text-zinc-700">SS sin fecha</span>
@endif
