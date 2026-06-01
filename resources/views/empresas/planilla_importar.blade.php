@extends('layouts.app')

@section('title', 'Importar planilla — '.$empresa->nombre)

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-display text-xl font-semibold text-zinc-950 md:text-2xl">Importar planilla Excel</h1>
            <p class="mt-1 text-sm text-zinc-600">{{ $empresa->nombre }}</p>
        </div>
        <a href="{{ route('empresas.edit', $empresa) }}" class="text-xs font-medium text-emerald-800 underline hover:text-emerald-950 md:text-sm">
            Volver a editar empresa
        </a>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-5">
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                    {{ session('error') }}
                </div>
            @endif

            @if ($empresa->limite === null)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                    Esta empresa no tiene <strong>fecha límite</strong>. Defínala en editar empresa antes de importar.
                </div>
            @else
                <p class="mb-4 text-sm text-zinc-700">
                    Fecha límite actual: <strong>{{ $empresa->limite->format('d/m/Y') }}</strong>.
                    El mes <strong>{{ \App\Models\ContratistaInterno::MESES[(int) $empresa->limite->month] ?? '—' }}</strong>
                    se marcará en el control mensual.
                </p>
            @endif

            <form action="{{ route('empresas.planilla.preview', $empresa) }}" method="post" enctype="multipart/form-data" class="flex flex-col gap-4">
                @csrf
                <div>
                    <label for="planilla" class="mb-1 block text-sm font-semibold text-zinc-950">Archivo Excel (.xlsx)</label>
                    <input
                        type="file"
                        name="planilla"
                        id="planilla"
                        accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                        required
                        class="block w-full text-sm text-zinc-800 file:mr-3 file:rounded-md file:border-0 file:bg-emerald-700 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-800"
                    >
                    @error('planilla')
                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-md bg-emerald-700 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800 sm:w-auto sm:px-6" @disabled($empresa->limite === null)>
                    Ver vista previa
                </button>
            </form>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50/60 p-4 md:p-5">
            <h2 class="text-sm font-bold uppercase tracking-wide text-emerald-900">Plantilla oficial</h2>
            <p class="mt-2 text-sm text-zinc-700">
                Descargue el Excel con las columnas correctas y tres filas de ejemplo. Borre los ejemplos antes de pegar su planilla de seguridad social.
            </p>
            <a
                href="{{ route('empresas.planilla.plantilla') }}"
                class="mt-4 inline-flex items-center gap-2 rounded-md bg-white px-4 py-2.5 text-sm font-semibold text-emerald-900 shadow ring-1 ring-emerald-200 hover:bg-emerald-100"
            >
                Descargar plantilla Excel
            </a>

            <div class="mt-5 overflow-x-auto rounded-lg border border-emerald-100 bg-white">
                <table class="min-w-full text-left text-xs">
                    <thead class="bg-emerald-700 text-[10px] font-bold uppercase text-white">
                        <tr>
                            @foreach (\App\Support\PlanillaContratistasColumnas::ENCABEZADOS as $col)
                                <th class="px-2 py-2">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-800">
                        <tr>
                            <td class="px-2 py-1.5">1234567890</td>
                            <td class="px-2 py-1.5">CC</td>
                            <td class="px-2 py-1.5">JUAN PÉREZ GÓMEZ</td>
                            <td class="px-2 py-1.5">EXTERNO</td>
                            <td class="px-2 py-1.5">SURA</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
