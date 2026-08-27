@php
    $modoFiltro = $modoFiltro ?? false;
    $filtrosTipo = $filtrosTipo ?? 'externo';
    $contratista = $contratista ?? null;
    $empresas = $empresas ?? collect();
    $inputClass = $inputClass ?? 'mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600';

    if ($modoFiltro) {
        $inputId = 'filtro-'.$filtrosTipo.'-empresa-busqueda';
        $hiddenId = 'filtro-'.$filtrosTipo.'-empresa';
        $listaId = $inputId.'-lista';
        $empresaSeleccionadaId = '';
        $empresaSeleccionada = null;
        $labelClass = 'mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600';
        $labelTexto = 'Empresa';
        $placeholder = $empresas->isEmpty() ? 'Sin empresas' : 'Escriba para buscar…';
    } else {
        $empresaSeleccionadaId = old('empresa_id', $contratista?->empresa_id);
        $empresaSeleccionada = $empresas->firstWhere('id', (int) $empresaSeleccionadaId);
        $inputId = $inputId ?? 'empresa_id_busqueda';
        $hiddenId = 'empresa_id';
        $listaId = $inputId.'-lista';
        $labelClass = 'block text-xs font-semibold text-zinc-950 md:text-[13px]';
        $labelTexto = 'Empresa';
        $placeholder = $empresas->isEmpty() ? 'Sin empresas disponibles' : 'Escriba para buscar empresa…';
    }

    $sinEmpresas = $empresas->isEmpty();
    $opciones = $empresas->map(fn ($emp) => ['id' => $emp->id, 'nombre' => $emp->nombre])->values();
@endphp

<div
    class="relative"
    data-empresa-busqueda
    data-input-id="{{ $inputId }}"
    data-opciones='@json($opciones)'
    data-valor-inicial="{{ $empresaSeleccionadaId ?? '' }}"
    data-texto-inicial="{{ $empresaSeleccionada?->nombre ?? '' }}"
    @if ($modoFiltro) data-permitir-todas="1" data-lista-flotante="1" @endif
>
    <label for="{{ $inputId }}" class="{{ $labelClass }}">{{ $labelTexto }}</label>
    <input
        type="text"
        id="{{ $inputId }}"
        value="{{ $empresaSeleccionada?->nombre ?? '' }}"
        autocomplete="off"
        placeholder="{{ $placeholder }}"
        @disabled($sinEmpresas)
        class="{{ $inputClass }} disabled:cursor-not-allowed disabled:bg-zinc-100"
        data-empresa-busqueda-input
        role="combobox"
        aria-autocomplete="list"
        aria-expanded="false"
        aria-controls="{{ $listaId }}"
    >
    <input
        type="hidden"
        @unless ($modoFiltro) name="empresa_id" @endunless
        id="{{ $hiddenId }}"
        value="{{ $empresaSeleccionadaId ?? '' }}"
        @if (! $modoFiltro && $sinEmpresas) disabled @elseif (! $modoFiltro) required @endif
        data-empresa-busqueda-valor
    >
    <ul
        id="{{ $listaId }}"
        class="{{ $modoFiltro ? 'fixed z-[9999]' : 'absolute z-20' }} mt-1 hidden max-h-60 min-w-[16rem] overflow-y-auto rounded-md border border-zinc-200 bg-white py-1 text-sm shadow-lg ring-1 ring-black/5"
        data-empresa-busqueda-lista
        role="listbox"
    ></ul>
    @unless ($modoFiltro)
        <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">
            Escriba para filtrar. Lista administrada en
            <a href="{{ route('empresas.index') }}" class="font-medium text-emerald-800 underline hover:text-emerald-950">Empresas</a>.
        </p>
    @endunless
</div>

@once
    @include('contratistas._empresa_select_busqueda_script')
@endonce
