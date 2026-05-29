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
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Categoría licencia</dt>
                <dd class="mt-0.5 text-zinc-900">
                    @php
                        $cats = $contratista->licencia_categoria;
                        $cats = is_array($cats) ? $cats : (($cats === null || $cats === '') ? [] : [$cats]);
                    @endphp
                    @if ($cats === [])
                        —
                    @else
                        <div class="grid grid-cols-2 gap-x-3 gap-y-0.5">
                            @foreach ($cats as $c)
                                <span>{{ LicenciaConduccionCategorias::OPCIONES[$c] ?? $c }}</span>
                            @endforeach
                        </div>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vencimiento licencia</dt>
                <dd class="mt-0.5 text-zinc-900">{{ $contratista->licencia_vencimiento?->format('d/m/Y') ?? '—' }}{!! $badgeEstado($contratista->licencia_vencimiento) !!}</dd>
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
