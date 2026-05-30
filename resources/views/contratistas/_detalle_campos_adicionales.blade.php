@php
    use App\Support\LicenciaConduccionCategorias;
    use App\Services\ContratistaDocumentoStorage;

    $archivoEnlace = function (?string $ruta, string $etiqueta) {
        if ($ruta === null || $ruta === '') {
            return '—';
        }

        $url = ContratistaDocumentoStorage::urlPublica($ruta);
        $nombre = basename($ruta);

        return '<a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 underline hover:text-emerald-800">'.e($etiqueta ?: $nombre).'</a>';
    };

    $badgeEstado = function ($fecha) {
        if (! $fecha) {
            return '';
        }

        $hoy = \Illuminate\Support\Carbon::now()->startOfDay();
        $vigente = $fecha->copy()->startOfDay()->greaterThanOrEqualTo($hoy);
        $texto = $vigente ? 'VIGENTE' : 'VENCIDA';
        $clase = $vigente ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800';

        return '<span class="ml-2 inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase '.$clase.'">'.$texto.'</span>';
    };
@endphp

<div class="col-span-full mt-1 border-t border-zinc-200 pt-3">
    <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-emerald-800">Información adicional</p>
    <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fecha de nacimiento</dt>
            <dd class="mt-0.5 text-zinc-900">{{ $contratista->fecha_nacimiento?->format('d/m/Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Cargo</dt>
            <dd class="mt-0.5 text-zinc-900">{{ $contratista->cargo ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Manipulador de alimentos</dt>
            <dd class="mt-0.5 text-zinc-900">{{ $contratista->manipulador_alimentos ? 'Sí' : 'No' }}</dd>
        </div>
        @if ($contratista->manipulador_alimentos)
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vigencia manipulador</dt>
                <dd class="mt-0.5 text-zinc-900">{{ $contratista->manipulador_vigencia?->format('d/m/Y') ?? '—' }}{!! $badgeEstado($contratista->manipulador_vigencia) !!}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento manipulador</dt>
                <dd class="mt-0.5 text-zinc-900">{!! $archivoEnlace($contratista->manipulador_archivo, 'Ver documento') !!}</dd>
            </div>
        @endif
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Licencia de conducción</dt>
            <dd class="mt-0.5 text-zinc-900">{{ $contratista->licencia_conduccion ? 'Sí' : 'No' }}</dd>
        </div>
        @if ($contratista->licencia_conduccion)
            <div class="col-span-full">
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Categorías y vencimientos</dt>
                <dd class="mt-1 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 text-zinc-900">
                    @php $vencimientos = $contratista->licenciaVencimientosFormateados(); @endphp
                    @if ($vencimientos === [])
                        <span class="text-zinc-900">—</span>
                    @else
                        @foreach ($vencimientos as $categoria => $fechaTexto)
                            @php
                                $estado = LicenciaConduccionCategorias::etiquetaEstado($fechaTexto);
                                $fechaMostrar = \Illuminate\Support\Carbon::parse($fechaTexto)->format('d/m/Y');
                            @endphp
                            <div class="rounded-md border border-zinc-200 bg-zinc-50 px-2.5 py-2 text-sm">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <span class="font-semibold">{{ $categoria }}</span>
                                    <span class="text-zinc-600">{{ $fechaMostrar }}</span>
                                    @if ($estado)
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $estado === 'VIGENTE' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">{{ $estado }}</span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-xs leading-tight text-zinc-500">{{ LicenciaConduccionCategorias::OPCIONES[$categoria] ?? $categoria }}</p>
                            </div>
                        @endforeach
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento licencia</dt>
                <dd class="mt-0.5 text-zinc-900">{!! $archivoEnlace($contratista->licencia_archivo, 'Ver licencia') !!}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Cédula</dt>
                <dd class="mt-0.5 text-zinc-900">{!! $archivoEnlace($contratista->cedula_archivo, 'Ver cédula') !!}</dd>
            </div>
        @endif
    </dl>
</div>
