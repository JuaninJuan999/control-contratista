@php
    /** @var \App\Models\Empresa $empresa */
    $resumen = $empresa->resumenVigenciaSsContratistas();
@endphp

@if ($resumen['total'] > 0)
    <div class="border-b border-zinc-100 bg-zinc-50 px-4 py-3">
        <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-zinc-600">Vigencia SS por contratista</p>
        <div class="flex flex-wrap gap-2 text-xs">
            @if ($resumen['vigentes'] > 0)
                <span class="rounded-full bg-emerald-100 px-2.5 py-1 font-semibold text-emerald-900">{{ $resumen['vigentes'] }} vigente{{ $resumen['vigentes'] === 1 ? '' : 's' }}</span>
            @endif
            @if ($resumen['proximas'] > 0)
                <span class="rounded-full bg-amber-100 px-2.5 py-1 font-semibold text-amber-900">{{ $resumen['proximas'] }} por vencer</span>
            @endif
            @if ($resumen['vencidas'] > 0)
                <span class="rounded-full bg-red-100 px-2.5 py-1 font-semibold text-red-900">{{ $resumen['vencidas'] }} vencida{{ $resumen['vencidas'] === 1 ? '' : 's' }}</span>
            @endif
            @if ($resumen['sin_fecha'] > 0)
                <span class="rounded-full bg-zinc-200 px-2.5 py-1 font-semibold text-zinc-800">{{ $resumen['sin_fecha'] }} sin fecha</span>
            @endif
        </div>
        @if ($empresa->esPlanillaIndependiente() && count($resumen['items']) > 0)
            <div class="mt-3 overflow-x-auto rounded-lg border border-zinc-200 bg-white">
                <table class="w-full min-w-[32rem] text-left text-xs">
                    <thead class="bg-zinc-100 text-[10px] font-bold uppercase tracking-wide text-zinc-600">
                        <tr>
                            <th class="px-3 py-2">Contratista</th>
                            <th class="px-3 py-2">Tipo planilla</th>
                            <th class="px-3 py-2">Fecha límite SS</th>
                            <th class="px-3 py-2">Estado SS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($resumen['items'] as $item)
                            @if ($item['tipo'] !== 'interno')
                                @continue
                            @endif
                            <tr>
                                <td class="px-3 py-2 font-medium text-zinc-900">{{ $item['nombre'] }}</td>
                                <td class="px-3 py-2 text-zinc-700">{{ $item['tipo_planilla'] ?? '—' }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-zinc-800">{{ $item['limite']?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-3 py-2">@include('empresas._badge_estado_ss', ['estado' => $item['estado'], 'dias' => $item['dias']])</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif
