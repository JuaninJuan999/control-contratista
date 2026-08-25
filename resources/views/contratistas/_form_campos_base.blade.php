@php
    $contratista = $contratista ?? null;
    $mostrarCamposIr = $mostrarCamposIr ?? true;
@endphp

<div class="md:col-span-5">
    <label for="nombres_apellidos" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Nombres y apellidos</label>
    <input type="text" name="nombres_apellidos" id="nombres_apellidos" value="{{ old('nombres_apellidos', $contratista?->nombres_apellidos) }}" required maxlength="255" class="{{ $inputClass }}">
</div>
<div class="sm:grid sm:grid-cols-[minmax(0,140px)_1fr] sm:gap-3 md:col-span-7 md:grid md:grid-cols-12 md:gap-3">
    <div class="md:col-span-5">
        <label for="tipo_documento" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Tipo de documento</label>
        <select name="tipo_documento" id="tipo_documento" required class="{{ $selectClass }}">
            @foreach (\App\Support\TiposDocumento::OPCIONES as $val => $label)
                <option value="{{ $val }}" @selected(old('tipo_documento', $contratista?->tipo_documento ?? 'CC') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="mt-3 sm:mt-0 md:col-span-7">
        <label for="numero_documento" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Documento</label>
        <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', $contratista?->numero_documento) }}" required maxlength="32" placeholder="Ej. 1234567890 o 1.234.567.890" class="{{ $inputClass }}">
        <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Puede ingresarlo con o sin puntos, espacios o guiones; se guardará sin separadores.</p>
    </div>
</div>

<div class="md:col-span-4">
    @include('contratistas._empresa_select_busqueda', [
        'contratista' => $contratista,
        'empresas' => $empresas,
        'inputClass' => $inputClass,
    ])
</div>
<div class="md:col-span-4">
    <label for="arl" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">ARL</label>
    <input type="text" name="arl" id="arl" value="{{ old('arl', $contratista?->arl) }}" required maxlength="120" placeholder="Ej. SURA, Positiva, Colmena…" class="{{ $inputClass }}">
</div>
@if ($mostrarCamposIr)
<div class="md:col-span-4">
    <label for="fecha_ultima_ir" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha de última I/R</label>
    <input type="date" name="fecha_ultima_ir" id="fecha_ultima_ir" value="{{ old('fecha_ultima_ir', $contratista?->fecha_ultima_ir?->format('Y-m-d')) }}" required class="{{ $inputClass }}">
</div>
<div class="md:col-span-4">
    <label for="vigencia_dias" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Vigencia (días)</label>
    <input type="number" name="vigencia_dias" id="vigencia_dias" value="{{ old('vigencia_dias', $contratista?->vigencia_dias ?? 365) }}" required min="1" max="3650" class="{{ $inputClass }}">
    <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Predeterminado: 365 días.</p>
</div>
@endif
