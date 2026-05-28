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
                <select name="{{ $fieldName('licencia_categoria') }}" class="{{ $selectClass ?? $inputClass }}">
                    <option value="">— Seleccionar —</option>
                    @foreach (LicenciaConduccionCategorias::OPCIONES as $val => $label)
                        <option value="{{ $val }}" @selected($oldValue('licencia_categoria') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha vencimiento licencia</label>
                <input type="date" name="{{ $fieldName('licencia_vencimiento') }}" value="{{ $oldValue('licencia_vencimiento') }}" class="{{ $inputClass }}">
            </div>
            <div class="md:col-span-6">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Adjuntar vencimiento de licencia</label>
                <input type="file" name="{{ $fieldName('licencia_vencimiento_archivo') }}" accept=".pdf,.jpg,.jpeg,.png" class="{{ $inputClass }} file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900">
                @php $archivoActual($contratista?->licencia_vencimiento_archivo, 'Ver vencimiento'); @endphp
            </div>
            <div class="md:col-span-6">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Tarjeta de propiedad</label>
                <input type="file" name="{{ $fieldName('tarjeta_propiedad_archivo') }}" accept=".pdf,.jpg,.jpeg,.png" class="{{ $inputClass }} file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900">
                <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">PDF o imagen (máx. 5 MB).</p>
                @php $archivoActual($contratista?->tarjeta_propiedad_archivo, 'Ver tarjeta de propiedad'); @endphp
            </div>
        </div>
    </div>
</div>
