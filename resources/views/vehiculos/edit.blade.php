@extends('layouts.app')

@section('title', 'Editar vehículo — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-xl font-semibold text-zinc-950 md:text-2xl">Editar vehículo</h1>
        <a href="{{ route('vehiculos.index') }}" class="text-xs font-medium text-emerald-800 underline hover:text-emerald-950 md:text-sm">
            Volver al listado
        </a>
    </div>

    <div class="max-w-3xl rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-5">
        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-900 md:text-sm">
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="mb-3 text-xs text-zinc-600 md:text-sm">Actualiza las fechas de vencimiento del SOAT y la tecnomecánica cuando renueves la documentación.</p>

        <form action="{{ route('vehiculos.update', $vehiculo) }}" method="post" enctype="multipart/form-data" class="flex flex-col gap-3">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
                <div class="md:col-span-4">
                    <label for="placa" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Vehículo (placa)</label>
                    <input
                        type="text"
                        name="placa"
                        id="placa"
                        value="{{ old('placa', $vehiculo->placa) }}"
                        required
                        maxlength="16"
                        placeholder="Ej. ABC123"
                        class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm uppercase text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                </div>
                <div class="md:col-span-4">
                    <label for="soat_fin" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">SOAT (fecha fin)</label>
                    <input
                        type="date"
                        name="soat_fin"
                        id="soat_fin"
                        value="{{ old('soat_fin', $vehiculo->soat_fin->format('Y-m-d')) }}"
                        required
                        class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                </div>
                <div class="md:col-span-4">
                    <label for="tecnomecanica_fin" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Tecnomecánica (fecha fin)</label>
                    <input
                        type="date"
                        name="tecnomecanica_fin"
                        id="tecnomecanica_fin"
                        value="{{ old('tecnomecanica_fin', $vehiculo->tecnomecanica_fin->format('Y-m-d')) }}"
                        required
                        class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                </div>
                <div class="md:col-span-12">
                    <label for="empresa_id" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Empresa</label>
                    <select
                        name="empresa_id"
                        id="empresa_id"
                        required
                        class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                        @foreach ($empresas as $emp)
                            <option value="{{ $emp->id }}" @selected(old('empresa_id', $vehiculo->empresa_id) == $emp->id)>{{ $emp->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-12 mt-1 border-t border-zinc-200 pt-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-800">Documentos del vehículo</p>
                    <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">PDF o imagen (máx. 5 MB cada uno). Sube uno nuevo solo si deseas reemplazar el actual.</p>
                </div>
                @foreach (\App\Models\Vehiculo::DOCUMENTOS as $campo => $etiqueta)
                    @continue($campo === 'inspeccion_sanitaria_archivo')
                    <div class="md:col-span-6">
                        <label for="{{ $campo }}" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Adjuntar {{ $etiqueta }}</label>
                        <input
                            type="file"
                            name="{{ $campo }}"
                            id="{{ $campo }}"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600 file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900"
                        >
                        @if ($vehiculo->{$campo})
                            <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">
                                Archivo actual:
                                <a href="{{ \App\Services\VehiculoDocumentoStorage::urlPublica($vehiculo->{$campo}) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 underline hover:text-emerald-800">Ver {{ $etiqueta }}</a>.
                            </p>
                        @endif
                    </div>
                @endforeach

                @php $inspeccionSi = (bool) old('inspeccion_sanitaria', $vehiculo->inspeccion_sanitaria); @endphp
                <div class="md:col-span-12" data-inspeccion-root>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
                        <div class="md:col-span-4">
                            <label for="inspeccion_sanitaria" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Inspección Sanitaria</label>
                            <select
                                name="inspeccion_sanitaria"
                                id="inspeccion_sanitaria"
                                class="js-inspeccion-select mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                            >
                                <option value="0" @selected(! $inspeccionSi)>No</option>
                                <option value="1" @selected($inspeccionSi)>Sí</option>
                            </select>
                        </div>
                        <div class="js-inspeccion-campos md:col-span-4 {{ $inspeccionSi ? '' : 'hidden' }}">
                            <label for="inspeccion_sanitaria_fin" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha de vencimiento</label>
                            <input
                                type="date"
                                name="inspeccion_sanitaria_fin"
                                id="inspeccion_sanitaria_fin"
                                value="{{ old('inspeccion_sanitaria_fin', $vehiculo->inspeccion_sanitaria_fin?->format('Y-m-d')) }}"
                                class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                            >
                        </div>
                        <div class="js-inspeccion-campos md:col-span-4 {{ $inspeccionSi ? '' : 'hidden' }}">
                            <label for="inspeccion_sanitaria_archivo" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Adjuntar Inspección sanitaria</label>
                            <input
                                type="file"
                                name="inspeccion_sanitaria_archivo"
                                id="inspeccion_sanitaria_archivo"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600 file:mr-2 file:rounded file:border-0 file:bg-emerald-50 file:px-2 file:py-1 file:text-xs file:font-semibold file:text-emerald-900"
                            >
                            @if ($vehiculo->inspeccion_sanitaria_archivo)
                                <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">
                                    Archivo actual:
                                    <a href="{{ \App\Services\VehiculoDocumentoStorage::urlPublica($vehiculo->inspeccion_sanitaria_archivo) }}" target="_blank" rel="noopener noreferrer" class="font-medium text-emerald-700 underline hover:text-emerald-800">Ver Inspección sanitaria</a>.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @include('vehiculos._inspeccion_toggle_script')

            <button type="submit" class="mt-1 w-full rounded-md bg-emerald-700 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800 sm:w-auto sm:px-6">
                Guardar cambios
            </button>
        </form>
    </div>
@endsection
