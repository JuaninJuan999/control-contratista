@extends('layouts.app')

@section('title', 'Búsqueda — '.config('app.name'))

@section('content')
    <div class="mb-6">
        <h1 class="font-display text-2xl font-semibold text-zinc-900">Resultados de búsqueda</h1>
        @if ($termino !== '')
            <p class="mt-1 text-sm text-zinc-600">Término: <strong>{{ $termino }}</strong></p>
        @endif
    </div>

    @if ($termino === '' || mb_strlen($termino) < 2)
        <p class="rounded-lg border border-zinc-200 bg-white px-4 py-6 text-sm text-zinc-600">
            Escriba al menos 2 caracteres para buscar empresas, contratistas o vehículos.
        </p>
    @elseif ($resultados === [])
        <p class="rounded-lg border border-zinc-200 bg-white px-4 py-6 text-sm text-zinc-600">
            Sin resultados para «{{ $termino }}».
        </p>
    @else
        <ul class="divide-y divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm">
            @foreach ($resultados as $item)
                <li>
                    <a
                        href="{{ $item['url'] }}"
                        class="flex flex-col gap-1 px-4 py-3 transition hover:bg-emerald-50 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <span>
                            <span class="font-medium text-zinc-900">{{ $item['label'] }}</span>
                            @if (! empty($item['sublabel']))
                                <span class="mt-0.5 block text-sm text-zinc-500">{{ $item['sublabel'] }}</span>
                            @endif
                        </span>
                        <span class="shrink-0 rounded bg-zinc-100 px-2 py-0.5 text-xs font-bold uppercase text-zinc-600">
                            {{ $item['tipo_etiqueta'] }}
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
