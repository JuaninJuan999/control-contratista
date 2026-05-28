@extends('layouts.app')

@section('title', 'Dashboard — '.config('app.name'))

@section('content')
    <div class="rounded-xl border border-zinc-200 bg-white p-8 shadow-lg">
        <h1 class="font-display text-2xl font-semibold text-zinc-900 md:text-3xl">Dashboard</h1>
        <p class="mt-3 max-w-2xl leading-relaxed text-zinc-600">
            Este espacio quedará reservado para <strong class="text-zinc-800">gráficas e indicadores</strong> cuando avancemos con el seguimiento de inducciones y reinducciones.
        </p>
        <p class="mt-4 text-sm text-zinc-500">
            Para registrar contratistas <strong>externos</strong>, usa el menú <strong>Externos</strong>. El módulo de internos se añadirá cuando lo definamos.
        </p>
    </div>
@endsection
