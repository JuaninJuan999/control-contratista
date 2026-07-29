@extends('layouts.app')

@section('title', 'Contratistas externos — '.config('app.name'))

@section('containerClass', 'max-w-none w-full')

@section('content')
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Contratistas externos</h1>
            @if (auth()->user()?->puedeEditar())
            <a href="{{ route('contratistas-externos.create') }}" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                Nuevo externo
            </a>
            @endif
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                {{ session('error') }}
            </div>
        @endif

        @include('contratistas._filtros_contratistas_panel', ['filtrosTipo' => 'externo'])

        @include('contratistas._tabla_index', [
            'contratistas' => $contratistasExternos,
            'tipo' => 'externo',
            'mostrarControlMensual' => false,
            'habilitarFiltrosCliente' => true,
        ])
    </div>

    @include('contratistas._index_expandible_script')
    @include('contratistas._filtros_contratistas_script', ['filtrosTipo' => 'externo'])
@endsection
