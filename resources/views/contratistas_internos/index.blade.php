@extends('layouts.app')

@section('title', 'Contratistas internos — '.config('app.name'))

@section('containerClass', 'max-w-[min(1920px,calc(100vw-3rem))]')

@section('content')
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Contratistas internos</h1>
            @if (auth()->user()?->puedeEditar())
            <a href="{{ route('contratistas-internos.create') }}" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                Nuevo interno
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

        <form method="get" class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label for="anio" class="block text-xs font-semibold text-zinc-950">Año de control</label>
                <select name="anio" id="anio" class="mt-0.5 rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600" onchange="this.form.submit()">
                    @for ($y = now()->year + 1; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" @selected($anio === $y)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <p class="text-xs text-zinc-600 md:text-sm">Clic en el nombre para ver el detalle.@if (auth()->user()?->puedeEditar()) Clic en un mes para marcar <strong>OK</strong>.@endif</p>
        </form>

        @php $totalColumnas = 5 + count(\App\Models\ContratistaInterno::MESES) + 1 + (auth()->user()?->puedeEditar() ? 1 : 0); @endphp

        <div class="overflow-x-auto rounded-lg border border-zinc-200">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                        <th class="min-w-[12rem] px-3 py-3">Nombres y apellidos</th>
                        <th class="px-3 py-3">Tipo doc.</th>
                        <th class="px-3 py-3">Documento</th>
                        <th class="min-w-[10rem] px-3 py-3">Empresa</th>
                        <th class="px-3 py-3">ARL</th>
                        @foreach (\App\Models\ContratistaInterno::MESES as $mes => $abrev)
                            <th class="w-10 px-1 py-3 text-center">{{ $abrev }}</th>
                        @endforeach
                        <th class="px-3 py-3">Registro</th>
                        @if (auth()->user()?->puedeEditar())
                        <th class="px-3 py-3">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($contratistasInternos as $c)
                        <tr
                            class="cursor-pointer bg-white hover:bg-zinc-50/80 {{ ! $c->activo ? 'opacity-60' : '' }}"
                            data-contratista-toggle="interno-{{ $c->id }}"
                            aria-expanded="false"
                        >
                            <td class="px-3 py-2 font-medium text-zinc-900">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="contratista-chevron size-4 shrink-0 text-emerald-700 transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $c->nombres_apellidos }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-zinc-800">{{ $c->tipo_documento }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $c->numero_documento }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $c->empresa?->nombre ?? '—' }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $c->arl }}</td>
                            @foreach (\App\Models\ContratistaInterno::MESES as $mes => $abrev)
                                <td class="px-1 py-2 text-center">
                                    @if (auth()->user()?->puedeEditar())
                                    <form action="{{ route('contratistas-internos.toggle-mes', $c) }}" method="post" class="inline" onclick="event.stopPropagation()">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="anio" value="{{ $anio }}">
                                        <input type="hidden" name="mes" value="{{ $mes }}">
                                        <button
                                            type="submit"
                                            title="{{ $c->mesRegistrado($anio, $mes) ? 'Quitar OK de '.$abrev : 'Marcar OK en '.$abrev }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded text-xs font-bold transition {{ $c->mesRegistrado($anio, $mes) ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'text-zinc-300 hover:bg-zinc-100 hover:text-zinc-500' }}"
                                        >
                                            {{ $c->mesRegistrado($anio, $mes) ? 'OK' : '·' }}
                                        </button>
                                    </form>
                                    @else
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded text-xs font-bold {{ $c->mesRegistrado($anio, $mes) ? 'bg-emerald-100 text-emerald-800' : 'text-zinc-300' }}">
                                        {{ $c->mesRegistrado($anio, $mes) ? 'OK' : '·' }}
                                    </span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-3 py-2">
                                @if ($c->activo)
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-800">Activo</span>
                                @else
                                    <span class="rounded bg-zinc-200 px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-700">Inactivo</span>
                                @endif
                            </td>
                            @if (auth()->user()?->puedeEditar())
                            <td class="px-3 py-2">
                                @include('contratistas._acciones_contratista', [
                                    'contratista' => $c,
                                    'editRoute' => route('contratistas-internos.edit', $c),
                                    'toggleActivoRoute' => route('contratistas-internos.toggle-activo', $c),
                                    'anio' => $anio,
                                ])
                            </td>
                            @endif
                        </tr>
                        <tr class="hidden bg-zinc-50/50" data-contratista-panel="interno-{{ $c->id }}" hidden>
                            <td colspan="{{ $totalColumnas }}" class="border-t border-zinc-100 px-4 py-4">
                                <dl class="mb-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nombres y apellidos</dt><dd class="mt-0.5 font-medium text-zinc-900">{{ $c->nombres_apellidos }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento</dt><dd class="mt-0.5 text-zinc-900">{{ $c->tipo_documento }} {{ $c->numero_documento }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Empresa</dt><dd class="mt-0.5 text-zinc-900">{{ $c->empresa?->nombre ?? '—' }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">ARL</dt><dd class="mt-0.5 text-zinc-900">{{ $c->arl }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Registro</dt><dd class="mt-0.5 text-zinc-900">{{ $c->activo ? 'Activo' : 'Inactivo' }}</dd></div>
                                </dl>
                                @include('contratistas._detalle_campos_adicionales', ['contratista' => $c])
                                <p class="mb-2 mt-3 text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Control mensual {{ $anio }}</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach (\App\Models\ContratistaInterno::MESES as $mes => $abrev)
                                        <span class="inline-flex h-7 min-w-7 items-center justify-center rounded text-[10px] font-bold {{ $c->mesRegistrado($anio, $mes) ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-100 text-zinc-400' }}" title="{{ $abrev }}">
                                            {{ $c->mesRegistrado($anio, $mes) ? 'OK' : $abrev }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $totalColumnas }}" class="px-3 py-8 text-center text-zinc-500">
                                No hay contratistas internos registrados.
                                @if (auth()->user()?->puedeEditar())
                                <a href="{{ route('contratistas-internos.create') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Registrar el primero</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('contratistas._index_expandible_script')
@endsection
