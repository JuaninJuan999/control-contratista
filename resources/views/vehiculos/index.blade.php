@extends('layouts.app')

@section('title', 'Vehículos — '.config('app.name'))

@section('content')
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Vehículos</h1>
            @if (auth()->user()?->puedeEditar())
            <a
                href="{{ route('vehiculos.create') }}"
                class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800"
            >
                Nuevo vehículo
            </a>
            @endif
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
                        <th class="px-3 py-3">Documentos</th>
                        @if (auth()->user()?->puedeEditar())
                        <th class="px-3 py-3">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($vehiculos as $v)
                        <tr
                            data-vehiculo-fila="vehiculo-{{ $v->id }}"
                            class="bg-white hover:bg-zinc-50/80"
                        >
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
                                @php $tieneDocs = false; @endphp
                                <div class="flex flex-col gap-0.5">
                                    @foreach (\App\Models\Vehiculo::DOCUMENTOS as $campoDoc => $etiquetaDoc)
                                        @if ($v->{$campoDoc})
                                            @php $tieneDocs = true; @endphp
                                            <a href="{{ \App\Services\VehiculoDocumentoStorage::urlPublica($v->{$campoDoc}) }}" target="_blank" rel="noopener noreferrer" class="text-xs font-medium text-emerald-700 underline hover:text-emerald-800">{{ $etiquetaDoc }}</a>
                                        @endif
                                    @endforeach
                                    @unless ($tieneDocs)
                                        <span class="text-zinc-400">—</span>
                                    @endunless
                                    @if ($v->inspeccion_sanitaria && $v->inspeccion_sanitaria_fin)
                                        <span class="text-[11px] text-zinc-500">
                                            Insp. sanitaria: {{ $v->inspeccion_sanitaria_fin->format('d/m/Y') }} —
                                            <span class="font-bold {{ $v->inspeccion_sanitaria_estado === 'VIGENTE' ? 'text-emerald-700' : 'text-red-700' }}">{{ $v->inspeccion_sanitaria_estado }}</span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            @if (auth()->user()?->puedeEditar())
                            <td class="px-3 py-2">
                                <a
                                    href="{{ route('vehiculos.edit', $v) }}"
                                    class="rounded-md border border-emerald-700 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-900 hover:bg-emerald-100"
                                >
                                    Editar
                                </a>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->puedeEditar() ? 8 : 7 }}" class="px-3 py-8 text-center text-zinc-500">
                                No hay vehículos registrados.
                                @if (auth()->user()?->puedeEditar())
                                <a href="{{ route('vehiculos.create') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Registrar el primero</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        (function () {
            var params = new URLSearchParams(window.location.search);
            var abrir = params.get('abrir');

            if (!abrir || !abrir.startsWith('vehiculo-')) {
                return;
            }

            setTimeout(function () {
                var fila = document.querySelector('[data-vehiculo-fila="' + abrir + '"]');

                if (!fila) {
                    return;
                }

                if (window.resaltarFilaBusqueda) {
                    window.resaltarFilaBusqueda(fila);
                }
            }, 120);
        })();
    </script>
@endsection
