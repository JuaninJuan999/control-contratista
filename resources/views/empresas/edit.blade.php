@extends('layouts.app')

@section('title', 'Editar empresa — '.config('app.name'))

@section('content')
    @php
        $contratistas = collect();
        foreach ($empresa->contratistasExternos as $c) {
            $contratistas->push(['clave' => 'externo-'.$c->id, 'nombre' => $c->nombres_apellidos, 'tipo' => 'Externo', 'doc' => $c->tipo_documento.' '.$c->numero_documento]);
        }
        foreach ($empresa->contratistasInternos as $c) {
            $contratistas->push(['clave' => 'interno-'.$c->id, 'nombre' => $c->nombres_apellidos, 'tipo' => 'Interno', 'doc' => $c->tipo_documento.' '.$c->numero_documento]);
        }
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-xl font-semibold text-zinc-950 md:text-2xl">Editar empresa</h1>
        <a href="{{ route('empresas.index') }}" class="text-xs font-medium text-emerald-800 underline hover:text-emerald-950 md:text-sm">
            Volver al listado
        </a>
    </div>

    <div class="max-w-2xl rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-5">
        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-900 md:text-sm">
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('empresas.update', $empresa) }}" method="post" class="flex flex-col gap-3" id="form-empresa">
            @csrf
            @method('PUT')
            @include('empresas._form', ['empresa' => $empresa])

            <div id="personas-vigentes-inputs"></div>

            <button type="button" id="btn-actualizar-empresa" class="mt-1 w-full rounded-md bg-emerald-700 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800 sm:w-auto sm:px-6">
                Actualizar empresa
            </button>
        </form>
    </div>

    {{-- Modal de control mensual --}}
    <div id="modal-vigencia" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true">
        <div class="flex max-h-[85vh] w-full max-w-lg flex-col rounded-xl bg-white shadow-2xl">
            <div class="border-b border-zinc-200 px-5 py-4">
                <h2 class="font-display text-lg font-semibold text-zinc-950">Contratistas vigentes</h2>
                <p class="mt-0.5 text-xs text-zinc-600">
                    Marca los contratistas que están vigentes con la nueva fecha límite. Se registrará el control mensual en
                    <strong id="modal-mes-label" class="text-emerald-800">—</strong>.
                </p>
                <p id="modal-sin-limite" class="mt-1 hidden text-xs font-medium text-amber-700">
                    No hay fecha límite definida: no se marcará ningún mes en el control mensual.
                </p>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-3">
                @if ($contratistas->isEmpty())
                    <p class="py-6 text-center text-sm text-zinc-500">Esta empresa no tiene contratistas vinculados.</p>
                @else
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wide text-zinc-500">{{ $contratistas->count() }} contratista(s)</span>
                        <div class="flex gap-2 text-xs">
                            <button type="button" id="modal-marcar-todos" class="font-medium text-emerald-800 underline hover:text-emerald-950">Marcar todos</button>
                            <button type="button" id="modal-desmarcar-todos" class="font-medium text-zinc-600 underline hover:text-zinc-800">Desmarcar todos</button>
                        </div>
                    </div>
                    <ul class="divide-y divide-zinc-100 rounded-lg border border-zinc-200">
                        @foreach ($contratistas as $c)
                            <li>
                                <label class="flex cursor-pointer items-center gap-3 px-3 py-2.5 hover:bg-zinc-50">
                                    <input type="checkbox" class="modal-persona-check size-4 rounded border-zinc-300 text-emerald-700 focus:ring-emerald-600" value="{{ $c['clave'] }}" checked>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-zinc-900">{{ $c['nombre'] }}</span>
                                        <span class="block text-[11px] text-zinc-500">{{ $c['tipo'] }} · {{ $c['doc'] }}</span>
                                    </span>
                                </label>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-2 text-[11px] leading-tight text-zinc-500">Los no marcados quedarán en <span class="font-semibold text-red-600">rojo</span> en ese mes hasta que se cambien manualmente.</p>
                @endif
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-zinc-200 px-5 py-3">
                <button type="button" id="modal-cancelar" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50">Cancelar</button>
                <button type="button" id="modal-confirmar" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800">Confirmar y guardar</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var form = document.getElementById('form-empresa');
            var btnAbrir = document.getElementById('btn-actualizar-empresa');
            var modal = document.getElementById('modal-vigencia');
            var btnCancelar = document.getElementById('modal-cancelar');
            var btnConfirmar = document.getElementById('modal-confirmar');
            var btnTodos = document.getElementById('modal-marcar-todos');
            var btnNinguno = document.getElementById('modal-desmarcar-todos');
            var limiteInput = document.getElementById('limite');
            var mesLabel = document.getElementById('modal-mes-label');
            var sinLimite = document.getElementById('modal-sin-limite');
            var contenedorInputs = document.getElementById('personas-vigentes-inputs');
            if (!form || !btnAbrir || !modal) return;

            var meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

            function actualizarMesLabel() {
                var val = limiteInput ? limiteInput.value : '';
                if (!val) {
                    if (mesLabel) mesLabel.textContent = '—';
                    if (sinLimite) sinLimite.classList.remove('hidden');
                    return;
                }
                var partes = val.split('-');
                if (partes.length === 3) {
                    var mesIdx = parseInt(partes[1], 10) - 1;
                    if (mesLabel) mesLabel.textContent = (meses[mesIdx] || '?') + ' ' + partes[0];
                }
                if (sinLimite) sinLimite.classList.add('hidden');
            }

            function abrirModal() {
                actualizarMesLabel();
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function cerrarModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function enviar() {
                contenedorInputs.innerHTML = '';
                modal.querySelectorAll('.modal-persona-check:checked').forEach(function (chk) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'personas_vigentes[]';
                    input.value = chk.value;
                    contenedorInputs.appendChild(input);
                });
                form.submit();
            }

            btnAbrir.addEventListener('click', abrirModal);
            if (btnCancelar) btnCancelar.addEventListener('click', cerrarModal);
            if (btnConfirmar) btnConfirmar.addEventListener('click', enviar);
            modal.addEventListener('click', function (e) { if (e.target === modal) cerrarModal(); });
            if (btnTodos) btnTodos.addEventListener('click', function () {
                modal.querySelectorAll('.modal-persona-check').forEach(function (c) { c.checked = true; });
            });
            if (btnNinguno) btnNinguno.addEventListener('click', function () {
                modal.querySelectorAll('.modal-persona-check').forEach(function (c) { c.checked = false; });
            });
        })();
    </script>
@endsection
