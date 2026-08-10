@php
    use App\Support\PeriodoPlanilla;

    $archivosHistorial = $empresa->planillaArchivos;
    $periodoVigente = $empresa->periodoVigenciaActual();
    $archivoVigente = $empresa->archivoPlanillaVigenteActual();
    $planillaAdjunta = $empresa->planillaVigenteAdjunta();
@endphp

<div class="grid gap-4 lg:grid-cols-2">
    <div>
        <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-zinc-600">Historial de planillas adjuntas</h3>

        @if ($archivosHistorial->isEmpty())
            <p class="rounded-lg border border-dashed border-zinc-300 bg-white px-3 py-4 text-sm text-zinc-500">
                Aún no hay registros mensuales para esta empresa.
            </p>
        @else
            <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white">
                <table class="w-full text-left text-xs">
                    <thead class="bg-zinc-100 text-[10px] font-bold uppercase tracking-wide text-zinc-600">
                        <tr>
                            <th class="px-3 py-2">Periodo</th>
                            <th class="px-3 py-2">Vigencia hasta</th>
                            <th class="px-3 py-2">Archivo</th>
                            <th class="px-3 py-2">Subido</th>
                            @if (auth()->user()?->puedeEditar())
                            <th class="px-3 py-2 text-center">Acción</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach ($archivosHistorial as $archivo)
                            <tr class="{{ $archivo->esPeriodoVigenteActual() ? 'bg-emerald-50/60' : '' }}">
                                <td class="px-3 py-2 font-semibold text-zinc-900">
                                    {{ PeriodoPlanilla::etiqueta($archivo->periodo_anio, $archivo->periodo_mes) }}
                                    @if ($archivo->esPeriodoVigenteActual())
                                        <span class="ml-1 rounded bg-emerald-200 px-1.5 py-0.5 text-[9px] font-bold uppercase text-emerald-900">Vigente</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-zinc-700">{{ $archivo->vigencia_hasta?->format('d/m/Y') ?? '—' }}</td>
                                <td class="px-3 py-2">
                                    <a href="{{ route('planillas.archivo.descargar', $archivo) }}" class="font-medium text-emerald-800 underline hover:text-emerald-950" title="{{ $archivo->nombre_original }}">
                                        {{ $archivo->nombre_original ?? 'Descargar' }}
                                    </a>
                                    <span class="ml-1 text-zinc-400">{{ $archivo->tamanoLegible() }}</span>
                                </td>
                                <td class="px-3 py-2 text-zinc-600">{{ $archivo->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                @if (auth()->user()?->puedeEditar())
                                <td class="px-3 py-2 text-center">
                                    <form action="{{ route('planillas.archivo.destroy', $archivo) }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar el registro de {{ $archivo->periodoEtiqueta() }}?');">
                                        @csrf
                                        @method('DELETE')
                                        @if ($tipoFiltro)<input type="hidden" name="_filtro_tipo" value="{{ $tipoFiltro }}">@endif
                                        @if ($busqueda)<input type="hidden" name="_filtro_q" value="{{ $busqueda }}">@endif
                                        <input type="hidden" name="_filtro_anio" value="{{ $anioFiltro }}">
                                        <button type="submit" class="text-red-700 hover:text-red-900" title="Eliminar">🗑️</button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div>
        @if (auth()->user()?->puedeEditar())
            <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-zinc-600">Adjuntar planilla de seguridad social</h3>

            @if ($empresa->limite === null)
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-3 text-sm text-amber-950">
                    Defina la <strong>fecha límite</strong> de la empresa en
                    <a href="{{ route('empresas.edit', $empresa) }}" class="font-semibold underline hover:text-amber-900">Empresas → Editar</a>
                    para vincular la vigencia mensual de la planilla.
                </div>
            @elseif ($empresa->estado_limite === 'VENCIDA')
                <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-3 text-sm text-red-950">
                    <p class="font-semibold">La fecha límite venció el {{ $empresa->limite->format('d/m/Y') }}.</p>
                    <p class="mt-1">
                        Renueve la vigencia en
                        <a href="{{ route('empresas.edit', $empresa) }}" class="font-semibold underline hover:text-red-900">Empresas → Editar</a>
                        (por ejemplo, al {{ $empresa->limite->copy()->addMonth()->format('d/m/Y') }}) y luego adjunte aquí la nueva seguridad social.
                    </p>
                </div>
            @else
                <form action="{{ route('planillas.archivo.store', $empresa) }}" method="post" enctype="multipart/form-data" class="rounded-lg border border-zinc-200 bg-white p-4">
                    @csrf
                    @if ($tipoFiltro)<input type="hidden" name="_filtro_tipo" value="{{ $tipoFiltro }}">@endif
                    @if ($busqueda)<input type="hidden" name="_filtro_q" value="{{ $busqueda }}">@endif
                    <input type="hidden" name="_filtro_anio" value="{{ $anioFiltro }}">

                    <p class="mb-3 text-xs text-zinc-600">
                        Vigencia hasta: <strong>{{ $empresa->limite->format('d/m/Y') }}</strong>
                        (periodo <strong>{{ $empresa->periodoVigenciaEtiqueta() }}</strong>).
                        @if ($archivoVigente)
                            Ya existe un archivo para esta vigencia; al subir otro se <strong>reemplazará</strong>.
                        @else
                            Debe adjuntar la planilla que corresponde a esta fecha límite.
                        @endif
                    </p>

                    <div class="mb-3">
                        <label for="archivo_{{ $empresa->id }}" class="mb-1 block text-xs font-semibold text-zinc-800">Archivo (PDF o Excel)</label>
                        <input
                            type="file"
                            name="archivo"
                            id="archivo_{{ $empresa->id }}"
                            accept=".pdf,.xlsx,.xls"
                            required
                            class="w-full text-sm text-zinc-700 file:mr-2 file:rounded file:border-0 file:bg-emerald-700 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-emerald-800"
                        >
                    </div>

                    <button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                        Guardar planilla de vigencia
                    </button>
                </form>
            @endif
        @else
            <p class="text-sm text-zinc-500">Solo lectura: puede descargar los archivos del historial.</p>
        @endif
    </div>
</div>
