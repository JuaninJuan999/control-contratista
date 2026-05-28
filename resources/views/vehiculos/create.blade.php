@extends('layouts.app')

@section('title', 'Nuevo vehículo — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-xl font-semibold text-zinc-950 md:text-2xl">Nuevo vehículo</h1>
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

        @if ($empresas->isEmpty())
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                Aún no hay empresas en el sistema.
                <a href="{{ route('empresas.create') }}" class="font-semibold text-emerald-800 underline hover:text-emerald-950">Crea una empresa</a>
                para poder registrar un vehículo.
            </div>
        @endif

        <form action="{{ route('vehiculos.store') }}" method="post" class="flex flex-col gap-3">
            @csrf

            <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
                <div class="md:col-span-4">
                    <label for="placa" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Vehículo (placa)</label>
                    <input
                        type="text"
                        name="placa"
                        id="placa"
                        value="{{ old('placa') }}"
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
                        value="{{ old('soat_fin') }}"
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
                        value="{{ old('tecnomecanica_fin') }}"
                        required
                        class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                </div>
                <div class="md:col-span-12">
                    <label for="empresa_id" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Empresa</label>
                    <select
                        name="empresa_id"
                        id="empresa_id"
                        @if ($empresas->isEmpty()) disabled @else required @endif
                        class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600 disabled:cursor-not-allowed disabled:bg-zinc-100"
                    >
                        <option value="">— Seleccionar empresa —</option>
                        @foreach ($empresas as $emp)
                            <option value="{{ $emp->id }}" @selected(old('empresa_id') == $emp->id)>{{ $emp->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button
                type="submit"
                @disabled($empresas->isEmpty())
                class="mt-1 w-full rounded-md bg-emerald-700 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:px-6"
            >
                Guardar
            </button>
        </form>
    </div>
@endsection
