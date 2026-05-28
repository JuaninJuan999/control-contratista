@php
    $inputClass = 'mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600';
    $selectClass = $inputClass;
    $personasIniciales = old('personas', []);
    if (! is_array($personasIniciales)) {
        $personasIniciales = [];
    }
    $tiposDocumento = \App\Support\TiposDocumento::OPCIONES;
    $empresaNombre = old('nombre', '');
@endphp

<div class="border-t border-zinc-200 pt-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-sm font-semibold text-zinc-950">Persona</h2>
            <select
                id="persona-tipo-default"
                class="rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600 md:text-sm"
            >
                <option value="externo">Externo</option>
                <option value="interno">Interno</option>
            </select>
        </div>
        <button
            type="button"
            id="btn-agregar-persona"
            class="inline-flex items-center gap-1.5 rounded-md border border-emerald-700 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900 hover:bg-emerald-100"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4" aria-hidden="true">
                <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
            </svg>
            Agregar persona
        </button>
    </div>
    <p class="mt-1 text-[11px] leading-tight text-zinc-500">Opcional. Elige si es interno o externo antes de agregar.</p>

    <div id="personas-lista" class="mt-3 flex flex-col gap-3">
        @foreach ($personasIniciales as $index => $persona)
            @include('empresas._persona_block', [
                'index' => $index,
                'persona' => is_array($persona) ? $persona : [],
                'inputClass' => $inputClass,
                'selectClass' => $selectClass,
                'tiposDocumento' => $tiposDocumento,
                'empresaNombre' => $empresaNombre,
            ])
        @endforeach
    </div>
</div>

<template id="persona-plantilla">
    @include('empresas._persona_block', [
        'index' => '__INDEX__',
        'persona' => [],
        'inputClass' => $inputClass,
        'selectClass' => $selectClass,
        'tiposDocumento' => $tiposDocumento,
        'empresaNombre' => $empresaNombre,
    ])
</template>

@include('contratistas._campos_adicionales_script')

<script>
    (function () {
        var lista = document.getElementById('personas-lista');
        var btnAgregar = document.getElementById('btn-agregar-persona');
        var plantilla = document.getElementById('persona-plantilla');
        var nombreEmpresaInput = document.getElementById('nombre');
        var tipoDefaultSelect = document.getElementById('persona-tipo-default');
        if (!lista || !btnAgregar || !plantilla) return;

        var placeholderEmpresa = '— (nombre de la empresa arriba)';

        function etiquetaTipo(valor) {
            return valor === 'interno' ? 'Interno' : 'Externo';
        }

        function aplicarTipoEnBloque(bloque, valor) {
            if (!bloque) return;
            var campo = bloque.querySelector('.persona-tipo-campo');
            var etiqueta = bloque.querySelector('.persona-tipo-etiqueta');
            var esInterno = valor === 'interno';
            if (campo) campo.value = valor;
            if (etiqueta) etiqueta.textContent = etiquetaTipo(valor);
            bloque.setAttribute('data-persona-tipo', valor);
            bloque.querySelectorAll('.persona-campos-externo').forEach(function (el) {
                el.classList.toggle('hidden', esInterno);
            });
            bloque.querySelectorAll('.persona-campos-interno').forEach(function (el) {
                el.classList.toggle('hidden', !esInterno);
            });
            bloque.querySelectorAll('.persona-campo-externo').forEach(function (el) {
                el.disabled = esInterno;
            });
            bloque.querySelectorAll('.persona-campo-interno').forEach(function (el) {
                el.disabled = !esInterno;
            });
        }

        function tipoSeleccionado() {
            if (!tipoDefaultSelect) return 'externo';
            return tipoDefaultSelect.value === 'interno' ? 'interno' : 'externo';
        }

        function nombreEmpresaActual() {
            if (!nombreEmpresaInput) return '';
            return nombreEmpresaInput.value.trim();
        }

        function actualizarEmpresaEnPersonas() {
            var nombre = nombreEmpresaActual();
            var texto = nombre !== '' ? nombre : placeholderEmpresa;
            lista.querySelectorAll('.persona-empresa-nombre').forEach(function (input) {
                input.value = texto;
            });
        }

        var siguienteIndice = lista.querySelectorAll('[data-persona-index]').length;

        function obtenerIndicesUsados() {
            return Array.from(lista.querySelectorAll('[data-persona-index]')).map(function (el) {
                return parseInt(el.getAttribute('data-persona-index'), 10);
            });
        }

        function actualizarSiguienteIndice() {
            var indices = obtenerIndicesUsados();
            siguienteIndice = indices.length ? Math.max.apply(null, indices) + 1 : 0;
        }

        function agregarPersona() {
            var html = plantilla.innerHTML.replace(/__INDEX__/g, String(siguienteIndice));
            var wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            var bloque = wrapper.firstElementChild;
            if (!bloque) return;
            lista.appendChild(bloque);
            siguienteIndice += 1;
            aplicarTipoEnBloque(bloque, tipoSeleccionado());
            actualizarEmpresaEnPersonas();
            var input = bloque.querySelector('input[name*="nombres_apellidos"]');
            if (input) input.focus();
        }

        btnAgregar.addEventListener('click', agregarPersona);

        lista.addEventListener('click', function (event) {
            if (!event.target.classList.contains('btn-quitar-persona')) return;
            var bloque = event.target.closest('[data-persona-index]');
            if (!bloque) return;
            bloque.remove();
            actualizarSiguienteIndice();
        });

        actualizarSiguienteIndice();
        actualizarEmpresaEnPersonas();
        lista.querySelectorAll('[data-persona-index]').forEach(function (bloque) {
            var tipo = bloque.getAttribute('data-persona-tipo') || 'externo';
            aplicarTipoEnBloque(bloque, tipo);
        });

        if (nombreEmpresaInput) {
            nombreEmpresaInput.addEventListener('input', actualizarEmpresaEnPersonas);
            nombreEmpresaInput.addEventListener('change', actualizarEmpresaEnPersonas);
        }
    })();
</script>
