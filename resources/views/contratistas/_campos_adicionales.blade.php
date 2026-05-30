@php
    use App\Support\LicenciaConduccionCategorias;
    use App\Services\ContratistaDocumentoStorage;

    $namePrefix = $namePrefix ?? '';
    $contratista = $contratista ?? null;
    $fieldName = function (string $name) use ($namePrefix): string {
        return $namePrefix === '' ? $name : "{$namePrefix}[{$name}]";
    };
    $modelValue = function (string $name) use ($contratista): mixed {
        if ($contratista === null) {
            return null;
        }
        $valor = $contratista->{$name} ?? null;
        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('Y-m-d');
        }
        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        return $valor;
    };
    $oldValue = function (string $name, mixed $default = '') use ($namePrefix, $modelValue): mixed {
        $default = $modelValue($name) ?? $default;
        if ($namePrefix === '') {
            return old($name, $default);
        }
        $key = str_replace(['[', ']'], ['.', ''], $namePrefix).'.'.$name;

        return old($key, $default);
    };
    $archivoActual = function (?string $ruta, string $etiqueta) {
        if ($ruta === null || $ruta === '') {
            return;
        }
        $url = ContratistaDocumentoStorage::urlPublica($ruta);
        echo '<p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Archivo actual: <a href="'.e($url).'" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 underline hover:text-emerald-800">'.e($etiqueta).'</a>. Sube uno nuevo solo si deseas reemplazarlo.</p>';
    };
    $idSuffix = $namePrefix === '' ? 'principal' : preg_replace('/[^a-z0-9]+/i', '-', $namePrefix);
    $manipuladorSi = filter_var($oldValue('manipulador_alimentos', false), FILTER_VALIDATE_BOOLEAN);
    $licenciaSi = filter_var($oldValue('licencia_conduccion', false), FILTER_VALIDATE_BOOLEAN);
    $categoriasSeleccionadas = $oldValue('licencia_categoria', []);
    if (! is_array($categoriasSeleccionadas)) {
        $categoriasSeleccionadas = ($categoriasSeleccionadas === '' || $categoriasSeleccionadas === null) ? [] : [$categoriasSeleccionadas];
    }

    $vencimientosPorCategoria = $oldValue('licencia_vencimientos', null);
    if (! is_array($vencimientosPorCategoria)) {
        $vencimientosPorCategoria = $contratista?->licenciaVencimientosFormateados() ?? [];
    }
@endphp

<div class="campos-adicionales-contratista col-span-full mt-1 border-t border-zinc-200 pt-3" data-campos-adicionales="{{ $idSuffix }}">
    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-emerald-800">Información adicional</p>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha de nacimiento</label>
            <input type="date" name="{{ $fieldName('fecha_nacimiento') }}" value="{{ $oldValue('fecha_nacimiento') }}" class="{{ $inputClass }}">
        </div>
        <div class="md:col-span-8">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Cargo</label>
            <input type="text" name="{{ $fieldName('cargo') }}" value="{{ $oldValue('cargo') }}" maxlength="255" class="{{ $inputClass }}">
        </div>

        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Manipulador de alimentos</label>
            <select name="{{ $fieldName('manipulador_alimentos') }}" class="{{ $selectClass ?? $inputClass }} js-manipulador-select" data-suffix="{{ $idSuffix }}">
                <option value="0" @selected(! $manipuladorSi)>No</option>
                <option value="1" @selected($manipuladorSi)>Sí</option>
            </select>
        </div>
        <div class="js-manipulador-campos md:col-span-4 {{ $manipuladorSi ? '' : 'hidden' }}" data-suffix="{{ $idSuffix }}">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Vigencia manipulador de alimentos</label>
            <input type="date" name="{{ $fieldName('manipulador_vigencia') }}" value="{{ $oldValue('manipulador_vigencia') }}" class="{{ $inputClass }}">
        </div>
        <div class="js-manipulador-campos md:col-span-4 {{ $manipuladorSi ? '' : 'hidden' }}" data-suffix="{{ $idSuffix }}">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Documento del manipulador de alimentos</label>
            <input type="file" name="{{ $fieldName('manipulador_archivo') }}" accept=".pdf,.jpg,.jpeg,.png" class="{{ $inputClass }} file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900">
            <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">PDF o imagen (máx. 5 MB).</p>
            @php $archivoActual($contratista?->manipulador_archivo, 'Ver documento'); @endphp
        </div>

        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Licencia de conducción</label>
            <select name="{{ $fieldName('licencia_conduccion') }}" class="{{ $selectClass ?? $inputClass }} js-licencia-select" data-suffix="{{ $idSuffix }}">
                <option value="0" @selected(! $licenciaSi)>No</option>
                <option value="1" @selected($licenciaSi)>Sí</option>
            </select>
        </div>
        <div class="js-licencia-campos col-span-full grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3 {{ $licenciaSi ? '' : 'hidden' }}" data-suffix="{{ $idSuffix }}">
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Adjuntar licencia</label>
                <input type="file" name="{{ $fieldName('licencia_archivo') }}" accept=".pdf,.jpg,.jpeg,.png" class="{{ $inputClass }} file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900">
                @php $archivoActual($contratista?->licencia_archivo, 'Ver licencia'); @endphp
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Categoría de licencia</label>
                <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Puede seleccionar varias.</p>
                <div class="mt-1 grid grid-cols-2 gap-x-3 gap-y-1 rounded-md border border-zinc-300 bg-white px-2.5 py-2">
                    @foreach (LicenciaConduccionCategorias::OPCIONES as $val => $label)
                        @php
                            $fechaCategoria = $vencimientosPorCategoria[$val] ?? '';
                            $seleccionada = in_array($val, $categoriasSeleccionadas, true) || $fechaCategoria !== '';
                        @endphp
                        <label class="flex items-center gap-1.5 text-xs text-zinc-800">
                            <input
                                type="checkbox"
                                name="{{ $fieldName('licencia_categoria') }}[]"
                                value="{{ $val }}"
                                @checked($seleccionada)
                                class="js-licencia-cat-check rounded border-zinc-300 text-emerald-700 focus:ring-emerald-600"
                                data-categoria="{{ $val }}"
                                data-suffix="{{ $idSuffix }}"
                            >
                            <span>{{ $val }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha vencimiento licencia</label>
                <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Por cada categoría seleccionada.</p>
                <div class="js-licencia-vencimientos-panel mt-1 space-y-1.5" data-suffix="{{ $idSuffix }}">
                    @php $hayVencimientosVisibles = false; @endphp
                    @foreach (LicenciaConduccionCategorias::OPCIONES as $val => $label)
                        @php
                            $fechaCategoria = $vencimientosPorCategoria[$val] ?? '';
                            $seleccionada = in_array($val, $categoriasSeleccionadas, true) || $fechaCategoria !== '';
                            $estadoCategoria = LicenciaConduccionCategorias::etiquetaEstado($fechaCategoria);
                            $hayVencimientosVisibles = $hayVencimientosVisibles || $seleccionada;
                        @endphp
                        <div
                            class="js-licencia-vencimiento-item flex items-center gap-2 {{ $seleccionada ? '' : 'hidden' }}"
                            data-categoria="{{ $val }}"
                        >
                            <span class="w-7 shrink-0 text-xs font-bold text-zinc-700">{{ $val }}</span>
                            <input
                                type="date"
                                name="{{ $fieldName('licencia_vencimientos') }}[{{ $val }}]"
                                value="{{ $fechaCategoria }}"
                                class="js-licencia-cat-fecha {{ $inputClass }} min-w-0 flex-1"
                            >
                            <span
                                class="js-licencia-cat-estado shrink-0 inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-bold uppercase {{ $estadoCategoria === 'VIGENTE' ? 'bg-emerald-100 text-emerald-800' : ($estadoCategoria === 'VENCIDA' ? 'bg-red-100 text-red-800' : 'hidden') }}"
                            >{{ $estadoCategoria ?? '' }}</span>
                        </div>
                    @endforeach
                    <p class="js-licencia-vencimientos-vacio text-xs text-zinc-400 {{ $hayVencimientosVisibles ? 'hidden' : '' }}">
                        Seleccione una categoría.
                    </p>
                </div>
            </div>
            <div class="md:col-span-6">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Adjuntar cédula</label>
                <input type="file" name="{{ $fieldName('cedula_archivo') }}" accept=".pdf,.jpg,.jpeg,.png" class="{{ $inputClass }} file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900">
                <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Cédula de la persona. PDF o imagen (máx. 5 MB).</p>
                @php $archivoActual($contratista?->cedula_archivo, 'Ver cédula'); @endphp
            </div>
        </div>
    </div>
</div>
