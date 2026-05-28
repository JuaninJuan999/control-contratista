@extends('layouts.app')

@section('title', 'Vehículos — '.config('app.name'))

@section('content')
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Vehículos</h1>
            <a
                href="{{ route('vehiculos.create') }}"
                class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800"
            >
                Nuevo vehículo
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto rounded-lg border border-zinc-200">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                        <th class="px-3 py-3">Placa</th>
                        <th class="px-3 py-3">Empresa</th>
                        <th class="px-3 py-3">SOAT (fin)</th>
                        <th class="px-3 py-3">Estado SOAT</th>
                        <th class="px-3 py-3">Tecnomecánica (fin)</th>
                        <th class="px-3 py-3">Estado tecnomecánica</th>
                        <th class="px-3 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($vehiculos as $v)
                        <tr class="bg-white hover:bg-zinc-50/80">
                            <td class="px-3 py-2 font-medium text-zinc-900">{{ $v->placa }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $v->empresa?->nombre ?? '—' }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-zinc-800">{{ $v->soat_fin->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">
                                @if ($v->soat_estado === 'VIGENTE')
                                    <span class="font-bold text-emerald-700">VIGENTE</span>
                                @else
                                    <span class="font-bold text-red-700">VENCIDA</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap text-zinc-800">{{ $v->tecnomecanica_fin->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">
                                @if ($v->tecnomecanica_estado === 'VIGENTE')
                                    <span class="font-bold text-emerald-700">VIGENTE</span>
                                @else
                                    <span class="font-bold text-red-700">VENCIDA</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <a
                                    href="{{ route('vehiculos.edit', $v) }}"
                                    class="rounded-md border border-emerald-700 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-900 hover:bg-emerald-100"
                                >
                                    Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-zinc-500">
                                No hay vehículos registrados.
                                <a href="{{ route('vehiculos.create') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Registrar el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
