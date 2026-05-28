@extends('layouts.app')

@section('title', 'Contratistas externos — '.config('app.name'))

@section('containerClass', 'max-w-[min(1920px,calc(100vw-3rem))]')

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

        <p class="mb-3 text-xs text-zinc-600 md:text-sm">Haz clic en un contratista para ver el detalle completo y los documentos adjuntos.</p>

        <div class="overflow-x-auto rounded-lg border border-zinc-200">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                        <th class="min-w-[14rem] px-3 py-3">Nombres y apellidos</th>
                        <th class="px-3 py-3">Tipo doc.</th>
                        <th class="px-3 py-3">Documento</th>
                        <th class="px-3 py-3">Empresa</th>
                        <th class="px-3 py-3">Última I/R</th>
                        <th class="px-3 py-3">Vigencia</th>
                        <th class="px-3 py-3">Días falt.</th>
                        <th class="px-3 py-3">Vencimiento</th>
                        <th class="px-3 py-3">Estado I/R</th>
                        <th class="px-3 py-3">Registro</th>
                        @if (auth()->user()?->puedeEditar())
                        <th class="px-3 py-3">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($contratistasExternos as $c)
                        <tr
                            class="cursor-pointer bg-white hover:bg-zinc-50/80 {{ ! $c->activo ? 'opacity-60' : '' }}"
                            data-contratista-toggle="externo-{{ $c->id }}"
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
                            <td class="px-3 py-2 text-zinc-800">{{ $c->fecha_ultima_ir->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 font-semibold text-zinc-900">{{ $c->vigencia_dias }} días</td>
                            <td class="px-3 py-2 font-semibold tabular-nums text-zinc-900">{{ $c->dias_faltantes }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $c->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td class="px-3 py-2">
                                @if ($c->estado === 'VIGENTE')
                                    <span class="font-bold text-emerald-700">VIGENTE</span>
                                @else
                                    <span class="font-bold text-red-700">VENCIDA</span>
                                @endif
                            </td>
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
                                    'editRoute' => route('contratistas-externos.edit', $c),
                                    'toggleActivoRoute' => route('contratistas-externos.toggle-activo', $c),
                                ])
                            </td>
                            @endif
                        </tr>
                        <tr class="hidden bg-zinc-50/50" data-contratista-panel="externo-{{ $c->id }}" hidden>
                            <td colspan="{{ auth()->user()?->puedeEditar() ? 11 : 10 }}" class="border-t border-zinc-100 px-4 py-4">
                                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Nombres y apellidos</dt><dd class="mt-0.5 font-medium text-zinc-900">{{ $c->nombres_apellidos }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Documento</dt><dd class="mt-0.5 text-zinc-900">{{ $c->tipo_documento }} {{ $c->numero_documento }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Empresa</dt><dd class="mt-0.5 text-zinc-900">{{ $c->empresa?->nombre ?? '—' }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Fecha última I/R</dt><dd class="mt-0.5 text-zinc-900">{{ $c->fecha_ultima_ir->format('d/m/Y') }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vigencia</dt><dd class="mt-0.5 text-zinc-900">{{ $c->vigencia_dias }} días</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Vencimiento</dt><dd class="mt-0.5 text-zinc-900">{{ $c->fecha_vencimiento->format('d/m/Y') }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Días faltantes</dt><dd class="mt-0.5 font-bold tabular-nums text-zinc-900">{{ $c->dias_faltantes }}</dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Estado I/R</dt><dd class="mt-0.5"><span class="font-bold {{ $c->estado === 'VIGENTE' ? 'text-emerald-700' : 'text-red-700' }}">{{ $c->estado }}</span></dd></div>
                                    <div><dt class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500">Registro</dt><dd class="mt-0.5 text-zinc-900">{{ $c->activo ? 'Activo' : 'Inactivo' }}</dd></div>
                                </dl>
                                @include('contratistas._detalle_campos_adicionales', ['contratista' => $c])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()?->puedeEditar() ? 11 : 10 }}" class="px-3 py-8 text-center text-zinc-500">
                                No hay contratistas externos registrados.
                                @if (auth()->user()?->puedeEditar())
                                <a href="{{ route('contratistas-externos.create') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Registrar el primero</a>
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
