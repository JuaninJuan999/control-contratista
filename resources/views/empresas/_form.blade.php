@php
    $empresa = $empresa ?? null;
    $inputClass = 'mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600';
    $correosIniciales = old('correos');
    if ($correosIniciales === null) {
        $correosIniciales = $empresa && is_array($empresa->correos) && count($empresa->correos) > 0
            ? $empresa->correos
            : [''];
    }
    $limiteValor = old('limite');
    if ($limiteValor === null && $empresa?->limite) {
        $limiteValor = $empresa->limite->format('Y-m-d');
    }
@endphp

<div>
    <label for="nombre" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Nombre o razón social</label>
    <input
        type="text"
        name="nombre"
        id="nombre"
        value="{{ old('nombre', $empresa?->nombre ?? '') }}"
        required
        maxlength="255"
        class="{{ $inputClass }}"
    >
</div>

<div class="grid gap-3 sm:grid-cols-2">
    <div>
        <label for="nit" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">NIT <span class="font-normal text-zinc-500">(opcional)</span></label>
        <input
            type="text"
            name="nit"
            id="nit"
            value="{{ old('nit', $empresa?->nit ?? '') }}"
            maxlength="32"
            autocomplete="off"
            class="{{ $inputClass }}"
        >
    </div>
    <div>
        <label for="telefono" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Teléfono</label>
        <input
            type="text"
            name="telefono"
            id="telefono"
            value="{{ old('telefono', $empresa?->telefono ?? '') }}"
            maxlength="50"
            autocomplete="tel"
            class="{{ $inputClass }}"
        >
    </div>
</div>

<div>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Correos</label>
        <button type="button" id="btn-agregar-correo" class="text-xs font-medium text-emerald-800 underline hover:text-emerald-950">
            + Agregar correo
        </button>
    </div>
    <div id="correos-lista" class="mt-1 flex flex-col gap-2">
        @foreach ($correosIniciales as $index => $correo)
            <div class="correo-fila flex gap-2">
                <input
                    type="email"
                    name="correos[]"
                    value="{{ $correo }}"
                    maxlength="255"
                    placeholder="correo@empresa.com"
                    class="{{ $inputClass }} mt-0"
                >
                <button
                    type="button"
                    class="btn-quitar-correo shrink-0 rounded-md border border-zinc-300 px-2 text-xs text-zinc-600 hover:bg-zinc-50 {{ count($correosIniciales) <= 1 ? 'invisible pointer-events-none' : '' }}"
                    title="Quitar correo"
                >
                    Quitar
                </button>
            </div>
        @endforeach
    </div>
    <p class="mt-1 text-[11px] leading-tight text-zinc-500">Puedes registrar varios correos de contacto.</p>
</div>

<div class="grid gap-3 sm:grid-cols-2">
    <div>
        <label for="limite" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Límite</label>
        <input
            type="date"
            name="limite"
            id="limite"
            value="{{ old('limite', $limiteValor ?? '') }}"
            class="{{ $inputClass }}"
        >
    </div>
    <div>
        <label for="planilla" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Planilla</label>
        <input
            type="text"
            name="planilla"
            id="planilla"
            value="{{ old('planilla', $empresa?->planilla ?? '') }}"
            maxlength="255"
            class="{{ $inputClass }}"
        >
    </div>
</div>

<script>
    (function () {
        var lista = document.getElementById('correos-lista');
        var btnAgregar = document.getElementById('btn-agregar-correo');
        if (!lista || !btnAgregar) return;

        var inputClass = @json($inputClass);

        function actualizarBotonesQuitar() {
            var filas = lista.querySelectorAll('.correo-fila');
            var ocultar = filas.length <= 1;
            filas.forEach(function (fila) {
                var btn = fila.querySelector('.btn-quitar-correo');
                if (!btn) return;
                btn.classList.toggle('invisible', ocultar);
                btn.classList.toggle('pointer-events-none', ocultar);
            });
        }

        function crearFila(valor) {
            var fila = document.createElement('div');
            fila.className = 'correo-fila flex gap-2';
            fila.innerHTML =
                '<input type="email" name="correos[]" maxlength="255" placeholder="correo@empresa.com" class="' + inputClass + ' mt-0" value="' + (valor || '').replace(/"/g, '&quot;') + '">' +
                '<button type="button" class="btn-quitar-correo shrink-0 rounded-md border border-zinc-300 px-2 text-xs text-zinc-600 hover:bg-zinc-50" title="Quitar correo">Quitar</button>';
            return fila;
        }

        btnAgregar.addEventListener('click', function () {
            lista.appendChild(crearFila(''));
            actualizarBotonesQuitar();
            var inputs = lista.querySelectorAll('input[type="email"]');
            if (inputs.length) inputs[inputs.length - 1].focus();
        });

        lista.addEventListener('click', function (event) {
            if (!event.target.classList.contains('btn-quitar-correo')) return;
            var fila = event.target.closest('.correo-fila');
            if (!fila || lista.querySelectorAll('.correo-fila').length <= 1) return;
            fila.remove();
            actualizarBotonesQuitar();
        });

        actualizarBotonesQuitar();
    })();
</script>
