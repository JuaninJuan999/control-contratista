@extends('layouts.app')

@section('title', 'Editar contratista interno — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-xl font-semibold text-zinc-950 md:text-2xl">Editar contratista interno</h1>
        <a href="{{ route('contratistas-internos.index') }}" class="text-xs font-medium text-emerald-800 underline hover:text-emerald-950 md:text-sm">
            Volver al listado
        </a>
    </div>

    <div class="max-w-4xl rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-5">
        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-900 md:text-sm">
                <p class="font-semibold">Revisa los datos:</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('contratistas-internos.update', $contratistaInterno) }}" method="post" enctype="multipart/form-data" class="flex flex-col gap-3">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
                <div class="md:col-span-5">
                    <label for="nombres_apellidos" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Nombres y apellidos</label>
                    <input type="text" name="nombres_apellidos" id="nombres_apellidos" value="{{ old('nombres_apellidos', $contratistaInterno->nombres_apellidos) }}" required maxlength="255" class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div class="sm:grid sm:grid-cols-[minmax(0,140px)_1fr] sm:gap-3 md:col-span-7 md:grid md:grid-cols-12 md:gap-3">
                    <div class="md:col-span-5">
                        <label for="tipo_documento" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Tipo de documento</label>
                        <select name="tipo_documento" id="tipo_documento" required class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                            @foreach (\App\Support\TiposDocumento::OPCIONES as $val => $label)
                                <option value="{{ $val }}" @selected(old('tipo_documento', $contratistaInterno->tipo_documento) === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-3 sm:mt-0 md:col-span-7">
                        <label for="numero_documento" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Documento</label>
                        <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', $contratistaInterno->numero_documento) }}" required maxlength="32" class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                </div>

                <div class="md:col-span-6">
                    <label for="empresa_id" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Empresa</label>
                    <select name="empresa_id" id="empresa_id" required class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                        @foreach ($empresas as $emp)
                            <option value="{{ $emp->id }}" @selected(old('empresa_id', $contratistaInterno->empresa_id) == $emp->id)>{{ $emp->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-6">
                    <label for="arl" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">ARL</label>
                    <input type="text" name="arl" id="arl" value="{{ old('arl', $contratistaInterno->arl) }}" required maxlength="120" class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>

                @include('contratistas._campos_adicionales', [
                    'contratista' => $contratistaInterno,
                    'inputClass' => 'mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600',
                    'selectClass' => 'mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600',
                ])
            </div>

            <p class="text-[11px] leading-tight text-zinc-500">El control mensual (meses EN–DI) se marca en el listado.</p>

            <button type="submit" class="mt-1 w-full rounded-md bg-emerald-700 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800 sm:w-auto sm:px-6">
                Guardar cambios
            </button>
        </form>
    </div>

    @include('contratistas._campos_adicionales_script')
@endsection
