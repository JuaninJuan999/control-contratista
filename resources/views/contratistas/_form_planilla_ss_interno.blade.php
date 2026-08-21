@php
    use App\Support\PlanillaTipo;

    $contratista = $contratista ?? null;
    $tipoSeleccionado = old('tipo_planilla', $contratista?->tipo_planilla ?? PlanillaTipo::DEPENDIENTE);
    $mostrarLimite = $tipoSeleccionado === PlanillaTipo::INDEPENDIENTE;
    $limiteValor = old('limite', $contratista?->limite?->format('Y-m-d') ?? '');
    $archivoVigente = $contratista?->esPlanillaIndependiente()
        ? $contratista->archivoPlanillaVigenteActual()
        : null;
@endphp

<div class="md:col-span-12 mt-1 border-t border-zinc-200 pt-3">
    <p class="text-xs font-bold uppercase tracking-wide text-emerald-800">Seguridad social (planilla)</p>
    <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Dependiente usa la fecha límite de la empresa. Independiente lleva vigencia y planilla propias.</p>
</div>

<div class="md:col-span-4">
    <label for="tipo_planilla" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Tipo de planilla</label>
    <select name="tipo_planilla" id="tipo_planilla" required class="{{ $selectClass }}" data-planilla-ss-tipo>
        @foreach (PlanillaTipo::OPCIONES as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected($tipoSeleccionado === $valor)>{{ $etiqueta }}</option>
        @endforeach
    </select>
</div>

<div class="md:col-span-4 {{ $mostrarLimite ? '' : 'hidden' }}" data-planilla-ss-limite-wrap>
    <label for="limite" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha límite SS</label>
    <input
        type="date"
        name="limite"
        id="limite"
        value="{{ $limiteValor }}"
        @disabled(! $mostrarLimite)
        class="{{ $inputClass }}"
        data-planilla-ss-limite
    >
</div>

<div class="md:col-span-4 {{ $mostrarLimite ? '' : 'hidden' }}" data-planilla-ss-archivo-wrap>
    <label for="planilla_ss_archivo" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Planilla SS (PDF/Excel)</label>
    <input
        type="file"
        name="planilla_ss_archivo"
        id="planilla_ss_archivo"
        accept=".pdf,.xlsx,.xls"
        class="{{ $inputClass }} file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900"
        data-planilla-ss-archivo
    >
    @if ($archivoVigente)
        <p class="mt-1 text-[11px] text-zinc-600">
            Vigente:
            <a href="{{ route('contratistas-internos.planilla.descargar', $archivoVigente) }}" class="font-medium text-emerald-800 underline hover:text-emerald-950">
                {{ $archivoVigente->nombre_original ?? 'Descargar' }}
            </a>
        </p>
    @endif
</div>

<script>
    (function () {
        var select = document.querySelector('[data-planilla-ss-tipo]');
        var empresaSelect = document.getElementById('empresa_id');
        var limiteWrap = document.querySelector('[data-planilla-ss-limite-wrap]');
        var archivoWrap = document.querySelector('[data-planilla-ss-archivo-wrap]');
        var limiteInput = document.querySelector('[data-planilla-ss-limite]');
        var empresasPlanilla = @json($empresas->mapWithKeys(fn ($e) => [$e->id => $e->planilla])->all());

        function esIndependiente() {
            return select && select.value === @json(PlanillaTipo::INDEPENDIENTE);
        }

        function aplicar() {
            var independiente = esIndependiente();
            [limiteWrap, archivoWrap].forEach(function (el) {
                if (!el) return;
                el.classList.toggle('hidden', !independiente);
            });
            if (limiteInput) {
                limiteInput.disabled = !independiente;
                limiteInput.required = independiente;
            }
        }

        function prellenarDesdeEmpresa() {
            if (!select || !empresaSelect || select.value) {
                return;
            }
        }

        if (select) {
            select.addEventListener('change', aplicar);
        }

        if (empresaSelect && select) {
            empresaSelect.addEventListener('change', function () {
                var planillaEmpresa = empresasPlanilla[empresaSelect.value];
                if (planillaEmpresa && select.value === '') {
                    select.value = planillaEmpresa;
                }
                aplicar();
            });
        }

        aplicar();
    })();
</script>
