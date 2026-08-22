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
    $planillaSeleccionada = old('planilla', $empresa?->planilla ?? '');
    $esIndependienteInicial = $planillaSeleccionada === \App\Support\PlanillaTipo::INDEPENDIENTE;
    $tipoEmpresaSeleccionado = old('tipo_empresa', $empresa?->tipo_empresa ?? '');
    $esInternaInicial = $tipoEmpresaSeleccionado === \App\Support\EmpresaTipo::INTERNA;
    $esExternaInicial = $tipoEmpresaSeleccionado === \App\Support\EmpresaTipo::EXTERNA;
@endphp

<div class="rounded-lg border border-emerald-200 bg-emerald-50/50 p-3 md:p-4">
    <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-emerald-900">Paso 1 — Clasificación de la empresa</p>
    <div>
        <label for="tipo_empresa" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Interna o externa <span class="text-red-600">*</span></label>
        <select
            name="tipo_empresa"
            id="tipo_empresa"
            required
            class="{{ $inputClass }}"
        >
            <option value="">Seleccionar…</option>
            @foreach (\App\Support\EmpresaTipo::OPCIONES as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected($tipoEmpresaSeleccionado === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>
    <p id="clasificacion-ayuda-interna" class="mt-2 text-[11px] leading-snug text-zinc-600 {{ $esInternaInicial ? '' : 'hidden' }}">
        <strong>Interna:</strong> elija si la planilla SS es compartida (dependiente) o por empleado (independiente). Los correos reciben alertas según el tipo.
    </p>
    <p id="clasificacion-ayuda-externa" class="mt-2 text-[11px] leading-snug text-zinc-600 {{ $esExternaInicial ? '' : 'hidden' }}">
        <strong>Externa:</strong> solo se registran el nombre, las personas externas y los vehículos.
    </p>
</div>

<div class="bloque-solo-interna rounded-lg border border-emerald-200 bg-emerald-50/50 p-3 md:p-4 {{ $esInternaInicial ? '' : 'hidden' }}">
    <p class="mb-2 text-[11px] font-bold uppercase tracking-wide text-emerald-900">Paso 2 — Tipo de planilla</p>
    <div>
        <label for="planilla" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Planilla de seguridad social <span class="text-red-600">*</span></label>
        <select
            name="planilla"
            id="planilla"
            @if ($esInternaInicial) required @endif
            class="{{ $inputClass }}"
        >
            <option value="">Seleccionar…</option>
            @if ($planillaSeleccionada && ! array_key_exists($planillaSeleccionada, \App\Support\PlanillaTipo::OPCIONES))
                <option value="{{ $planillaSeleccionada }}" selected>{{ $planillaSeleccionada }}</option>
            @endif
            @foreach (\App\Support\PlanillaTipo::OPCIONES as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected($planillaSeleccionada === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>
    <p id="planilla-ayuda-dependiente" class="mt-2 text-[11px] leading-snug text-zinc-600 {{ $esIndependienteInicial ? 'hidden' : '' }}">
        <strong>Dependiente:</strong> la empresa tiene una sola fecha límite y planilla mensual compartida para todos los internos vinculados.
    </p>
    <p id="planilla-ayuda-independiente" class="mt-2 text-[11px] leading-snug text-zinc-600 {{ $esIndependienteInicial ? '' : 'hidden' }}">
        <strong>Independiente:</strong> cada contratista interno lleva su propia fecha límite y planilla. No necesita fecha límite a nivel empresa; podrá registrarla al crear cada persona.
    </p>
    <div id="bloque-limite-empresa" class="mt-3 {{ $esIndependienteInicial ? 'hidden' : '' }}">
        <label for="limite" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha límite empresa <span id="limite-requerido" class="text-red-600">*</span></label>
        <input
            type="date"
            name="limite"
            id="limite"
            value="{{ old('limite', $limiteValor ?? '') }}"
            class="{{ $inputClass }}"
        >
    </div>
</div>

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

<div class="bloque-solo-interna flex flex-col gap-3 {{ $esInternaInicial ? '' : 'hidden' }}">
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
        <p class="mt-1 text-[11px] leading-tight text-zinc-500">Puedes registrar varios correos de contacto. A estos correos llegan las alertas de planilla.</p>
    </div>
</div>

<script>
    (function () {
        var INTERNA = @json(\App\Support\EmpresaTipo::INTERNA);
        var EXTERNA = @json(\App\Support\EmpresaTipo::EXTERNA);
        var INDEPENDIENTE = @json(\App\Support\PlanillaTipo::INDEPENDIENTE);

        var tipoEmpresa = document.getElementById('tipo_empresa');
        var bloquesSoloInterna = document.querySelectorAll('.bloque-solo-interna');
        var ayudaInterna = document.getElementById('clasificacion-ayuda-interna');
        var ayudaExterna = document.getElementById('clasificacion-ayuda-externa');
        var planilla = document.getElementById('planilla');
        var bloqueLimite = document.getElementById('bloque-limite-empresa');
        var limiteInput = document.getElementById('limite');
        var nitInput = document.getElementById('nit');
        var telefonoInput = document.getElementById('telefono');
        var ayudaDep = document.getElementById('planilla-ayuda-dependiente');
        var ayudaInd = document.getElementById('planilla-ayuda-independiente');
        var limiteReq = document.getElementById('limite-requerido');
        var lista = document.getElementById('correos-lista');
        var btnAgregar = document.getElementById('btn-agregar-correo');
        var inputClass = @json($inputClass);

        function clasificacion() {
            return tipoEmpresa ? tipoEmpresa.value : '';
        }

        function actualizarPlanilla() {
            if (!planilla) return;
            var interna = clasificacion() === INTERNA;
            var esIndependiente = planilla.value === INDEPENDIENTE;
            if (bloqueLimite) bloqueLimite.classList.toggle('hidden', esIndependiente);
            if (ayudaDep) ayudaDep.classList.toggle('hidden', esIndependiente);
            if (ayudaInd) ayudaInd.classList.toggle('hidden', !esIndependiente);
            if (limiteInput) {
                // Un campo oculto con required bloquea el envío sin mostrar mensaje.
                limiteInput.required = interna && !esIndependiente;
                if (esIndependiente) limiteInput.value = '';
            }
            if (limiteReq) limiteReq.classList.toggle('hidden', esIndependiente);
        }

        function limpiarCorreos() {
            if (!lista) return;
            lista.querySelectorAll('input[type="email"]').forEach(function (input) {
                input.value = '';
            });
        }

        function actualizarPorClasificacion() {
            var valor = clasificacion();
            var interna = valor === INTERNA;
            var externa = valor === EXTERNA;

            bloquesSoloInterna.forEach(function (bloque) {
                bloque.classList.toggle('hidden', !interna);
            });
            if (ayudaInterna) ayudaInterna.classList.toggle('hidden', !interna);
            if (ayudaExterna) ayudaExterna.classList.toggle('hidden', !externa);

            if (planilla) planilla.required = interna;

            // Solo se limpia al elegir externa: sin seleccion hay que conservar lo
            // que el usuario ya escribio (y lo que restaura old() tras un error).
            if (externa) {
                if (planilla) planilla.value = '';
                if (limiteInput) limiteInput.value = '';
                if (nitInput) nitInput.value = '';
                if (telefonoInput) telefonoInput.value = '';
                limpiarCorreos();
            }

            actualizarPlanilla();
        }

        if (planilla) planilla.addEventListener('change', actualizarPlanilla);
        if (tipoEmpresa) tipoEmpresa.addEventListener('change', actualizarPorClasificacion);
        actualizarPorClasificacion();

        if (!lista || !btnAgregar) return;

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
