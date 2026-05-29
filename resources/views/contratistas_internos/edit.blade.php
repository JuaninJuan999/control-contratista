@extends('layouts.app')

@section('title', 'Editar contratista interno — '.config('app.name'))

@section('content')
    @php
        $inputClass = 'mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600';
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-xl font-semibold text-zinc-950 md:text-2xl">Editar contratista interno</h1>
        <a href="{{ route('contratistas-internos.index') }}" class="text-xs font-medium text-emerald-800 underline hover:text-emerald-950 md:text-sm">
            Volver al listado
        </a>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr,240px]">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-5">
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

            <form action="{{ route('contratistas-internos.update', $contratistaInterno) }}" method="post" enctype="multipart/form-data" class="flex flex-col gap-3" id="form-contratista-interno">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
                    @include('contratistas._form_campos_base', [
                        'contratista' => $contratistaInterno,
                        'inputClass' => $inputClass,
                        'selectClass' => $inputClass,
                    ])

                    @include('contratistas._campos_adicionales', [
                        'contratista' => $contratistaInterno,
                        'inputClass' => $inputClass,
                        'selectClass' => $inputClass,
                    ])
                </div>

                <button type="submit" class="mt-1 w-full rounded-md bg-emerald-700 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800 sm:w-auto sm:px-6">
                    Guardar cambios
                </button>
            </form>
        </div>

        @include('contratistas._preview_aside')
    </div>

    @include('contratistas._preview_script')
    @include('contratistas._campos_adicionales_script')
@endsection
