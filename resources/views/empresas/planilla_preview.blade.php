@extends('layouts.app')

@section('title', 'Vista previa planilla — '.$empresa->nombre)

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-xl font-semibold text-zinc-950 md:text-2xl">Vista previa de importación</h1>
            <p class="mt-1 text-sm text-zinc-600">{{ $empresa->nombre }} · límite {{ $empresa->limite?->format('d/m/Y') ?? '—' }}</p>
        </div>
        <a href="{{ route('empresas.planilla.create', $empresa) }}" class="text-xs font-medium text-emerald-800 underline hover:text-emerald-950 md:text-sm">
            Cambiar archivo
        </a>
    </div>

    @if ($analisis->errores !== [])
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
            <p class="font-semibold">Errores que impiden importar:</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($analisis->errores as $error)
                    <li>Fila {{ $error['fila'] }} · {{ $error['documento'] }} — {{ $error['mensaje'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-4 grid gap-3 sm:grid-cols-3">
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
            <p class="text-2xl font-bold text-emerald-900">{{ count($analisis->actualizados) }}</p>
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Actualizados</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3">
            <p class="text-2xl font-bold text-zinc-900">{{ count($analisis->inactivados) }}</p>
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-700">Inactivados</p>
        </div>
        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
            <p class="text-2xl font-bold text-blue-900">{{ count($analisis->nuevos) }}</p>
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-800">Nuevos</p>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        @foreach ([
            ['titulo' => 'Actualizados (en planilla y en sistema)', 'items' => $analisis->actualizados, 'vacio' => 'Ninguno'],
            ['titulo' => 'Inactivados (en sistema, no en planilla)', 'items' => $analisis->inactivados, 'vacio' => 'Ninguno'],
            ['titulo' => 'Nuevos a registrar', 'items' => $analisis->nuevos, 'vacio' => 'Ninguno', 'nuevos' => true],
        ] as $bloque)
            <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-bold text-zinc-900">{{ $bloque['titulo'] }}</h2>
                @if ($bloque['items'] === [])
                    <p class="mt-2 text-sm text-zinc-500">{{ $bloque['vacio'] }}</p>
                @else
                    <ul class="mt-2 max-h-48 space-y-1 overflow-y-auto text-sm text-zinc-800">
                        @foreach ($bloque['items'] as $item)
                            <li class="rounded border border-zinc-100 px-2 py-1">
                                <span class="font-medium">{{ $item['documento'] ?? '' }}</span>
                                @if (! empty($item['nombre']))
                                    — {{ $item['nombre'] }}
                                @endif
                                @if (! empty($bloque['nuevos']) && ! empty($item['sin_fecha_induccion']))
                                    <span class="block text-xs text-sky-700">Se creará sin fecha I/R (pendiente en dashboard)</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        @if (! $analisis->tieneErroresBloqueantes())
            <form action="{{ route('empresas.planilla.importar', $empresa) }}" method="post">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <button type="submit" class="rounded-md bg-emerald-700 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                    Confirmar e importar
                </button>
            </form>
        @endif
        <a href="{{ route('empresas.planilla.create', $empresa) }}" class="rounded-md border border-zinc-300 px-4 py-2.5 text-sm font-medium text-zinc-800 hover:bg-zinc-50">
            Cancelar
        </a>
    </div>
@endsection
