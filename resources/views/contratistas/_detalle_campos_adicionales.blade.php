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
                <dd class="mt-0.5 text-zinc-900">{{ $contratista->manipulador_vigencia?->format('d/m/Y') ?? '—' }}</dd>
            </div>
        @endif
        <div>
            <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Licencia de conducción</dt>
            <dd class="mt-0.5 text-zinc-900">{{ $contratista->licencia_conduccion ? 'Sí' : 'No' }}</dd>
        </div>
        @if ($contratista->licencia_conduccion)
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Categoría licencia</dt>
                <dd class="mt-0.5 text-zinc-900">{{ LicenciaConduccionCategorias::OPCIONES[$contratista->licencia_categoria] ?? $contratista->licencia_categoria ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vencimiento licencia</dt>
                <dd class="mt-0.5 text-zinc-900">{{ $contratista->licencia_vencimiento?->format('d/m/Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento licencia</dt>
                <dd class="mt-0.5 text-zinc-900">{!! $archivoEnlace($contratista->licencia_archivo, 'Ver licencia') !!}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento vencimiento</dt>
                <dd class="mt-0.5 text-zinc-900">{!! $archivoEnlace($contratista->licencia_vencimiento_archivo, 'Ver vencimiento') !!}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Tarjeta de propiedad</dt>
                <dd class="mt-0.5 text-zinc-900">{!! $archivoEnlace($contratista->tarjeta_propiedad_archivo, 'Ver tarjeta') !!}</dd>
            </div>
        @endif
    </dl>
</div>
