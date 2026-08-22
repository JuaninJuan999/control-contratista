@php
    $contratista = $contratista ?? null;
    $empresaSeleccionadaId = old('empresa_id', $contratista?->empresa_id);
    $empresaSeleccionada = $empresas->firstWhere('id', (int) $empresaSeleccionadaId);
    $sinEmpresas = $empresas->isEmpty();
    $inputId = $inputId ?? 'empresa_id_busqueda';
    $opciones = $empresas->map(fn ($emp) => ['id' => $emp->id, 'nombre' => $emp->nombre])->values();
@endphp

<div
    class="relative"
    data-empresa-busqueda
    data-input-id="{{ $inputId }}"
    data-opciones='@json($opciones)'
    data-valor-inicial="{{ $empresaSeleccionadaId ?? '' }}"
    data-texto-inicial="{{ $empresaSeleccionada?->nombre ?? '' }}"
>
    <label for="{{ $inputId }}" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Empresa</label>
    <input
        type="text"
        id="{{ $inputId }}"
        value="{{ $empresaSeleccionada?->nombre ?? '' }}"
        autocomplete="off"
        placeholder="{{ $sinEmpresas ? 'Sin empresas disponibles' : 'Escriba para buscar empresa…' }}"
        @disabled($sinEmpresas)
        class="{{ $inputClass }} disabled:cursor-not-allowed disabled:bg-zinc-100"
        data-empresa-busqueda-input
        role="combobox"
        aria-autocomplete="list"
        aria-expanded="false"
        aria-controls="{{ $inputId }}-lista"
    >
    <input
        type="hidden"
        name="empresa_id"
        id="empresa_id"
        value="{{ $empresaSeleccionadaId ?? '' }}"
        @if ($sinEmpresas) disabled @else required @endif
        data-empresa-busqueda-valor
    >
    <ul
        id="{{ $inputId }}-lista"
        class="absolute z-20 mt-1 hidden max-h-52 w-full overflow-y-auto rounded-md border border-zinc-200 bg-white py-1 text-sm shadow-lg"
        data-empresa-busqueda-lista
        role="listbox"
    ></ul>
    <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">
        Escriba para filtrar. Lista administrada en
        <a href="{{ route('empresas.index') }}" class="font-medium text-emerald-800 underline hover:text-emerald-950">Empresas</a>.
    </p>
</div>

@once
    @include('contratistas._empresa_select_busqueda_script')
@endonce
