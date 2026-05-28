@extends('layouts.app')

@section('title', 'Editar contratista externo — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-xl font-semibold text-zinc-950 md:text-2xl">Editar contratista externo</h1>
        <a href="{{ route('contratistas-externos.index') }}" class="text-xs font-medium text-emerald-800 underline hover:text-emerald-950 md:text-sm">
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

            <form action="{{ route('contratistas-externos.update', $contratistaExterno) }}" method="post" enctype="multipart/form-data" class="flex flex-col gap-3" id="form-contratista-externo">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:gap-x-3 md:gap-y-3">
                    <div class="md:col-span-5">
                        <label for="nombres_apellidos" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Nombres y apellidos</label>
                        <input type="text" name="nombres_apellidos" id="nombres_apellidos" value="{{ old('nombres_apellidos', $contratistaExterno->nombres_apellidos) }}" required maxlength="255" class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                    <div class="sm:grid sm:grid-cols-[minmax(0,140px)_1fr] sm:gap-3 md:col-span-7 md:grid md:grid-cols-12 md:gap-3">
                        <div class="md:col-span-5">
                            <label for="tipo_documento" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Tipo de documento</label>
                            <select name="tipo_documento" id="tipo_documento" required class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                                @foreach (\App\Support\TiposDocumento::OPCIONES as $val => $label)
                                    <option value="{{ $val }}" @selected(old('tipo_documento', $contratistaExterno->tipo_documento) === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-3 sm:mt-0 md:col-span-7">
                            <label for="numero_documento" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Documento</label>
                            <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', $contratistaExterno->numero_documento) }}" required maxlength="32" class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <label for="empresa_id" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Empresa</label>
                        <select name="empresa_id" id="empresa_id" required class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                            @foreach ($empresas as $emp)
                                <option value="{{ $emp->id }}" @selected(old('empresa_id', $contratistaExterno->empresa_id) == $emp->id)>{{ $emp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label for="fecha_ultima_ir" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Fecha de última I/R</label>
                        <input type="date" name="fecha_ultima_ir" id="fecha_ultima_ir" value="{{ old('fecha_ultima_ir', $contratistaExterno->fecha_ultima_ir->format('Y-m-d')) }}" required class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>
                    <div class="md:col-span-4">
                        <label for="vigencia_dias" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Vigencia (días)</label>
                        <input type="number" name="vigencia_dias" id="vigencia_dias" value="{{ old('vigencia_dias', $contratistaExterno->vigencia_dias) }}" required min="1" max="3650" class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                    </div>

                    @include('contratistas._campos_adicionales', [
                        'contratista' => $contratistaExterno,
                        'inputClass' => 'mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600',
                        'selectClass' => 'mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600',
                    ])
                </div>

                <button type="submit" class="mt-1 w-full rounded-md bg-emerald-700 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800 sm:w-auto sm:px-6">
                    Guardar cambios
                </button>
            </form>
        </div>

        <aside class="h-fit rounded-lg border border-zinc-200 bg-white p-4 shadow-lg">
            <h2 class="text-xs font-bold uppercase tracking-wide text-emerald-800">Vista previa</h2>
            <p class="mt-0.5 text-[11px] leading-snug text-zinc-500">Calculado con fecha y vigencia.</p>
            <dl class="mt-3 space-y-2 text-xs md:text-sm">
                <div>
                    <dt class="font-semibold text-zinc-600">Fecha de vencimiento</dt>
                    <dd id="preview-vencimiento" class="mt-0.5 font-medium text-zinc-900">—</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-600">Días faltantes</dt>
                    <dd id="preview-dias" class="mt-0.5 font-bold tabular-nums text-zinc-900">—</dd>
                </div>
                <div>
                    <dt class="font-semibold text-zinc-600">Estado</dt>
                    <dd id="preview-estado" class="mt-0.5 font-bold">—</dd>
                </div>
            </dl>
        </aside>
    </div>

    <script>
        (function () {
            var fechaInput = document.getElementById('fecha_ultima_ir');
            var vigenciaInput = document.getElementById('vigencia_dias');
            var pv = document.getElementById('preview-vencimiento');
            var pd = document.getElementById('preview-dias');
            var pe = document.getElementById('preview-estado');
            if (!fechaInput || !vigenciaInput || !pv || !pd || !pe) return;

            function stripTime(d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()); }
            function addDays(date, days) { var d = stripTime(date); d.setDate(d.getDate() + days); return d; }
            function formatDMY(d) { return d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear(); }

            function refresh() {
                var iso = fechaInput.value;
                var vig = parseInt(vigenciaInput.value, 10);
                if (!iso || !vig || vig < 1) {
                    pv.textContent = '—'; pd.textContent = '—'; pe.textContent = '—'; pe.className = 'mt-0.5 font-bold';
                    return;
                }
                var parts = iso.split('-');
                if (parts.length !== 3) return;
                var inicio = stripTime(new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2])));
                var venc = addDays(inicio, vig);
                var hoy = stripTime(new Date());
                var diffDias = Math.round((venc.getTime() - hoy.getTime()) / 86400000);
                pv.textContent = formatDMY(venc);
                pd.textContent = String(diffDias);
                if (diffDias >= 0) { pe.textContent = 'VIGENTE'; pe.className = 'mt-0.5 font-bold text-emerald-700'; }
                else { pe.textContent = 'VENCIDA'; pe.className = 'mt-0.5 font-bold text-red-700'; }
            }

            fechaInput.addEventListener('change', refresh);
            fechaInput.addEventListener('input', refresh);
            vigenciaInput.addEventListener('input', refresh);
            refresh();
        })();
    </script>

    @include('contratistas._campos_adicionales_script')
@endsection
