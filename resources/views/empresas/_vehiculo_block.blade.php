@php
    $vehiculo = is_array($vehiculo ?? null) ? $vehiculo : [];
    $empresaNombre = old('nombre', $empresaNombre ?? '');
@endphp

<div class="rounded-lg border border-zinc-200 bg-zinc-50/80 p-3" data-vehiculo-index="{{ $index }}">
    <div class="mb-2 flex items-center justify-between gap-2">
        <span class="text-xs font-bold uppercase tracking-wide text-emerald-800">Vehículo</span>
        <button type="button" class="btn-quitar-vehiculo text-xs font-medium text-red-700 underline hover:text-red-900">
            Quitar
        </button>
    </div>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Vehículo (placa)</label>
            <input
                type="text"
                name="vehiculos[{{ $index }}][placa]"
                value="{{ old("vehiculos.{$index}.placa", $vehiculo['placa'] ?? '') }}"
                maxlength="16"
                placeholder="Ej. ABC123"
                class="{{ $inputClass }} uppercase"
            >
        </div>
        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">SOAT (fecha fin)</label>
            <input
                type="date"
                name="vehiculos[{{ $index }}][soat_fin]"
                value="{{ old("vehiculos.{$index}.soat_fin", $vehiculo['soat_fin'] ?? '') }}"
                class="{{ $inputClass }}"
            >
        </div>
        <div class="md:col-span-4">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Tecnomecánica (fecha fin)</label>
            <input
                type="date"
                name="vehiculos[{{ $index }}][tecnomecanica_fin]"
                value="{{ old("vehiculos.{$index}.tecnomecanica_fin", $vehiculo['tecnomecanica_fin'] ?? '') }}"
                class="{{ $inputClass }}"
            >
        </div>
        <div class="md:col-span-6">
            <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Empresa</label>
            <input
                type="text"
                readonly
                tabindex="-1"
                value="{{ $empresaNombre !== '' ? $empresaNombre : '— (nombre de la empresa arriba)' }}"
                class="{{ $inputClass }} vehiculo-empresa-nombre cursor-default bg-zinc-100 text-zinc-700"
                aria-label="Empresa asociada"
            >
        </div>
        <div class="md:col-span-12">
            <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-800">Documentos del vehículo</p>
            <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Opcionales. PDF o imagen (máx. 5 MB cada uno).</p>
        </div>
        @foreach (\App\Models\Vehiculo::DOCUMENTOS as $campoDoc => $etiquetaDoc)
            @continue($campoDoc === 'inspeccion_sanitaria_archivo')
            <div class="md:col-span-6">
                <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Adjuntar {{ $etiquetaDoc }}</label>
                <input
                    type="file"
                    name="vehiculos[{{ $index }}][{{ $campoDoc }}]"
                    accept=".pdf,.jpg,.jpeg,.png"
                    class="{{ $inputClass }} file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900"
                >
            </div>
        @endforeach

        @php $inspeccionSi = (bool) old("vehiculos.{$index}.inspeccion_sanitaria", $vehiculo['inspeccion_sanitaria'] ?? false); @endphp
        <div class="md:col-span-12" data-inspeccion-root>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Inspección Sanitaria</label>
                    <select
                        name="vehiculos[{{ $index }}][inspeccion_sanitaria]"
                        class="{{ $inputClass }} js-inspeccion-select"
                    >
                        <option value="0" @selected(! $inspeccionSi)>No</option>
                        <option value="1" @selected($inspeccionSi)>Sí</option>
                    </select>
                </div>
                <div class="js-inspeccion-campos md:col-span-4 {{ $inspeccionSi ? '' : 'hidden' }}">
                    <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha de vencimiento</label>
                    <input
                        type="date"
                        name="vehiculos[{{ $index }}][inspeccion_sanitaria_fin]"
                        value="{{ old("vehiculos.{$index}.inspeccion_sanitaria_fin", $vehiculo['inspeccion_sanitaria_fin'] ?? '') }}"
                        class="{{ $inputClass }}"
                    >
                </div>
                <div class="js-inspeccion-campos md:col-span-4 {{ $inspeccionSi ? '' : 'hidden' }}">
                    <label class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Adjuntar Inspección sanitaria</label>
                    <input
                        type="file"
                        name="vehiculos[{{ $index }}][inspeccion_sanitaria_archivo]"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="{{ $inputClass }} file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900"
                    >
                </div>
            </div>
        </div>
    </div>
</div>
