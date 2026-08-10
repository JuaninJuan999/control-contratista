@php
    use App\Support\EmpresaTipo;
    use App\Support\PeriodoPlanilla;

    $periodoVigente = $empresa->periodoVigenciaActual();
    $archivoVigente = $empresa->archivoPlanillaVigenteActual();
    $planillaAdjunta = $empresa->planillaVigenteAdjunta();
@endphp

<div class="px-4 py-4">
    <dl class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Clasificación</dt>
            <dd class="mt-0.5">
                @if ($empresa->tipo_empresa === EmpresaTipo::INTERNA)
                    <span class="rounded bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-800">Interna</span>
                @elseif ($empresa->tipo_empresa === EmpresaTipo::EXTERNA)
                    <span class="rounded bg-sky-100 px-2 py-0.5 text-[10px] font-bold uppercase text-sky-800">Externa</span>
                @else
                    <span class="rounded bg-zinc-200 px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-700">Sin clasificar</span>
                @endif
            </dd>
        </div>
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tipo planilla</dt>
            <dd class="mt-0.5 text-zinc-900">{{ $empresa->planilla ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fecha límite (vigencia)</dt>
            <dd class="mt-0.5 text-zinc-900">{{ $empresa->limite?->format('d/m/Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Periodo vigente</dt>
            <dd class="mt-0.5 text-zinc-900">{{ $empresa->periodoVigenciaEtiqueta() }}</dd>
        </div>
    </dl>

    <div class="mb-4 rounded-lg border border-zinc-200 bg-white p-3">
        <p class="mb-2 text-xs font-bold uppercase tracking-wide text-zinc-600">Estado planilla de seguridad social</p>
        @if ($empresa->limite === null)
            <p class="text-sm text-amber-800">Sin fecha límite definida. Configure la vigencia en <a href="{{ route('empresas.edit', $empresa) }}" class="font-semibold underline hover:text-amber-950">editar empresa</a>.</p>
        @elseif ($empresa->estado_limite === 'VENCIDA')
            <p class="text-sm text-red-900">
                <span class="mr-2 rounded bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase text-red-800">Límite vencido</span>
                La vigencia venció el {{ $empresa->limite->format('d/m/Y') }}. Renueve la fecha límite y adjunte la nueva planilla de seguridad social.
            </p>
        @elseif ($planillaAdjunta)
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-800">Adjuntada</span>
                <span class="text-sm text-zinc-700">Vigencia hasta {{ $empresa->limite->format('d/m/Y') }}:</span>
                <a href="{{ route('planillas.archivo.descargar', $archivoVigente) }}" class="text-sm font-medium text-emerald-800 underline hover:text-emerald-950">
                    {{ $archivoVigente->nombre_original ?? 'Descargar archivo' }}
                </a>
                <span class="text-xs text-zinc-500">{{ $archivoVigente->tamanoLegible() }} · {{ $archivoVigente->created_at?->format('d/m/Y H:i') }}</span>
            </div>
        @else
            <p class="text-sm text-amber-900">
                <span class="mr-2 rounded bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-900">Pendiente</span>
                No hay planilla adjunta para la vigencia hasta {{ $empresa->limite->format('d/m/Y') }} ({{ PeriodoPlanilla::etiqueta($periodoVigente['anio'], $periodoVigente['mes']) }}).
            </p>
        @endif
    </div>

    @if ($empresa->planillaArchivos->isNotEmpty())
        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white">
            <p class="border-b border-zinc-100 bg-zinc-50 px-3 py-2 text-[11px] font-bold uppercase tracking-wide text-zinc-600">Historial de planillas adjuntas</p>
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-100 text-[10px] font-bold uppercase tracking-wide text-zinc-600">
                    <tr>
                        <th class="px-3 py-2">Periodo</th>
                        <th class="px-3 py-2">Vigencia hasta</th>
                        <th class="px-3 py-2">Archivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($empresa->planillaArchivos->take(6) as $archivo)
                        <tr class="{{ $archivo->esPeriodoVigenteActual() ? 'bg-emerald-50/50' : '' }}">
                            <td class="px-3 py-2 font-medium text-zinc-900">
                                {{ PeriodoPlanilla::etiqueta($archivo->periodo_anio, $archivo->periodo_mes) }}
                                @if ($archivo->esPeriodoVigenteActual())
                                    <span class="ml-1 text-[9px] font-bold uppercase text-emerald-700">Vigente</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-zinc-700">{{ $archivo->vigencia_hasta?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-3 py-2">
                                <a href="{{ route('planillas.archivo.descargar', $archivo) }}" class="font-medium text-emerald-800 underline hover:text-emerald-950">
                                    {{ $archivo->nombre_original ?? 'Descargar' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($empresa->planilla_archivos_count > 6)
                <p class="border-t border-zinc-100 px-3 py-2 text-[11px] text-zinc-500">+ {{ $empresa->planilla_archivos_count - 6 }} registro(s) más en el módulo Planillas.</p>
            @endif
        </div>
    @else
        <p class="text-sm text-zinc-500">No hay planillas de seguridad social registradas para esta empresa.</p>
    @endif

    <p class="mt-3 text-xs text-zinc-600">
        @if (auth()->user()?->puedeEditar())
            <a href="{{ route('planillas.index', ['q' => $empresa->nombre, 'abrir' => $empresa->id]) }}" class="font-semibold text-emerald-800 underline hover:text-emerald-950">Ir a Planillas</a> para adjuntar o gestionar los archivos mensuales.
        @endif
    </p>
</div>
