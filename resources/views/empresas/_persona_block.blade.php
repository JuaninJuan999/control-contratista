@php
    $persona = is_array($persona ?? null) ? $persona : [];
    $tipoDefault = old("personas.{$index}.tipo_documento", $persona['tipo_documento'] ?? 'CC');
    $tipoContratista = old("personas.{$index}.tipo_contratista", $persona['tipo_contratista'] ?? 'externo');
    $tipoContratista = $tipoContratista === 'interno' ? 'interno' : 'externo';
    $empresaNombre = old('nombre', $empresaNombre ?? '');
    $esInterno = $tipoContratista === 'interno';
@endphp

<div
    class="rounded-lg border border-zinc-200 bg-zinc-50/80 p-3"
    data-persona-index="{{ $index }}"
    data-persona-tipo="{{ $tipoContratista }}"
>
    <div class="mb-2 flex items-center justify-between gap-2">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-bold uppercase tracking-wide text-emerald-800">Persona</span>
            <span class="persona-tipo-etiqueta rounded-full bg-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-zinc-700 ring-1 ring-zinc-200">
                {{ $esInterno ? 'Interno' : 'Externo' }}
            </span>
            <input type="hidden" name="personas[{{ $index }}][tipo_contratista]" value="{{ $tipoContratista }}" class="persona-tipo-campo">
        </div>
        <button type="button" class="btn-quitar-persona text-xs font-medium text-red-700 underline hover:text-red-900">
            Quitar
        </button>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
        <div class="md:col-span-5">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Nombres y apellidos</label>
            <input
                type="text"
                name="personas[{{ $index }}][nombres_apellidos]"
                value="{{ old("personas.{$index}.nombres_apellidos", $persona['nombres_apellidos'] ?? '') }}"
                maxlength="255"
                class="{{ $inputClass }}"
            >
        </div>
        <div class="sm:grid sm:grid-cols-[minmax(0,140px)_1fr] sm:gap-3 md:col-span-7 md:grid md:grid-cols-12 md:gap-3">
            <div class="md:col-span-5">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Tipo de documento</label>
                <select name="personas[{{ $index }}][tipo_documento]" class="{{ $selectClass }}">
                    @foreach ($tiposDocumento as $val => $label)
                        <option value="{{ $val }}" @selected($tipoDefault === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-3 sm:mt-0 md:col-span-7">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Documento</label>
                <input
                    type="text"
                    name="personas[{{ $index }}][numero_documento]"
                    value="{{ old("personas.{$index}.numero_documento", $persona['numero_documento'] ?? '') }}"
                    maxlength="32"
                    class="{{ $inputClass }}"
                >
            </div>
        </div>

        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Empresa</label>
            <input
                type="text"
                readonly
                tabindex="-1"
                value="{{ $empresaNombre !== '' ? $empresaNombre : '— (nombre de la empresa arriba)' }}"
                class="{{ $inputClass }} persona-empresa-nombre cursor-default bg-zinc-100 text-zinc-700"
                aria-label="Empresa asociada"
            >
            <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Se asigna automáticamente a la empresa que estás creando.</p>
        </div>

        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">ARL</label>
            <input
                type="text"
                name="personas[{{ $index }}][arl]"
                value="{{ old("personas.{$index}.arl", $persona['arl'] ?? '') }}"
                maxlength="120"
                placeholder="Ej. SURA, Positiva, Colmena…"
                class="{{ $inputClass }}"
            >
        </div>
        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha de última I/R</label>
            <input
                type="date"
                name="personas[{{ $index }}][fecha_ultima_ir]"
                value="{{ old("personas.{$index}.fecha_ultima_ir", $persona['fecha_ultima_ir'] ?? '') }}"
                class="{{ $inputClass }}"
            >
        </div>
        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Vigencia (días)</label>
            <input
                type="number"
                name="personas[{{ $index }}][vigencia_dias]"
                value="{{ old("personas.{$index}.vigencia_dias", $persona['vigencia_dias'] ?? 365) }}"
                min="1"
                max="3650"
                class="{{ $inputClass }}"
            >
            <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Predeterminado: 365 días. El control mensual (EN–DI) se marca en el listado.</p>
        </div>

        @include('contratistas._campos_adicionales', [
            'namePrefix' => 'personas['.$index.']',
            'inputClass' => $inputClass,
            'selectClass' => $selectClass,
        ])
    </div>
</div>
