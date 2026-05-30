@extends('layouts.app')

@section('title', 'Tiempo de usabilidad — '.config('app.name'))

@section('content')
    @php
        use App\Support\DuracionFormateada;
    @endphp

    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Tiempo de usabilidad</h1>
                <p class="mt-1 text-sm text-zinc-600">Solo superadministrador. Mide el tiempo activo de cada usuario en el sistema.</p>
            </div>
        </div>

        <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-800">Tiempo total (filtro)</p>
                <p class="mt-1 text-xl font-semibold text-emerald-950">{{ DuracionFormateada::desdeSegundos($totalSegundos) }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wide text-zinc-600">Sesiones registradas</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950">{{ $sesiones->count() }}</p>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wide text-zinc-600">Usuarios con actividad</p>
                <p class="mt-1 text-xl font-semibold text-zinc-950">{{ $resumenUsuarios->count() }}</p>
            </div>
            <div class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3">
                <p class="text-[11px] font-bold uppercase tracking-wide text-sky-800">En línea ahora</p>
                <p class="mt-1 text-xl font-semibold text-sky-950">{{ $sesionesActivas->count() }}</p>
            </div>
        </div>

        <form method="get" class="mb-4 rounded-lg border border-zinc-200 bg-zinc-50 p-4">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="usuario" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Usuario</label>
                    <select name="usuario" id="usuario" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" @selected($usuarioId === $usuario->id)>
                                {{ $usuario->username }}@if ($usuario->nombre || $usuario->apellido) — {{ trim($usuario->nombre.' '.$usuario->apellido) }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="desde" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Desde</label>
                    <input type="date" name="desde" id="desde" value="{{ $desde->format('Y-m-d') }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div>
                    <label for="hasta" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-zinc-600">Hasta</label>
                    <input type="date" name="hasta" id="hasta" value="{{ $hasta->format('Y-m-d') }}" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                        Filtrar
                    </button>
                    <a href="{{ route('usabilidad.index') }}" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-semibold text-zinc-800 hover:bg-zinc-50">
                        Limpiar
                    </a>
                </div>
            </div>
            <p class="mt-2 text-xs text-zinc-500">Se considera inactividad tras {{ (int) config('usabilidad.inactividad_segundos', 900) / 60 }} minutos sin uso.</p>
        </form>

        @if ($sesionesActivas->isNotEmpty())
            <div class="mb-4 rounded-lg border border-sky-200 bg-sky-50/70 p-4">
                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-sky-800">Usuarios con sesión abierta</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($sesionesActivas as $activa)
                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-medium text-sky-900 ring-1 ring-sky-200">
                            {{ $activa->user?->username ?? '—' }}
                            <span class="ml-2 text-sky-600">desde {{ $activa->iniciada_at->format('d/m/Y H:i') }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($totalSegundos > 0)
            <section class="mb-6">
                <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-emerald-800">Gráficas</h2>
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-lg border border-zinc-200 bg-white p-4">
                        <p class="mb-3 text-sm font-semibold text-zinc-800">Tiempo activo por usuario (minutos)</p>
                        <div class="relative h-64 sm:h-72">
                            <canvas id="grafica-usuarios"></canvas>
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">Compara quién usa más el sistema en el periodo filtrado.</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4">
                        <p class="mb-3 text-sm font-semibold text-zinc-800">Distribución del tiempo por usuario</p>
                        <div class="relative mx-auto h-64 w-full max-w-xs sm:h-72">
                            <canvas id="grafica-distribucion"></canvas>
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">Porcentaje del tiempo total entre los usuarios activos.</p>
                    </div>
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 lg:col-span-2">
                        <p class="mb-3 text-sm font-semibold text-zinc-800">Actividad por día</p>
                        <div class="relative h-72">
                            <canvas id="grafica-dias"></canvas>
                        </div>
                        <p class="mt-2 text-xs text-zinc-500">Barras: minutos activos. Línea: cantidad de sesiones iniciadas.</p>
                    </div>
                </div>
            </section>
        @endif

        <h2 class="mb-2 text-sm font-bold uppercase tracking-wide text-emerald-800">Resumen por usuario</h2>
        <div class="mb-6 overflow-x-auto rounded-lg border border-zinc-200">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                        <th class="px-3 py-3">Usuario</th>
                        <th class="px-3 py-3">Nombre</th>
                        <th class="px-3 py-3">Rol</th>
                        <th class="px-3 py-3">Sesiones</th>
                        <th class="px-3 py-3">Tiempo activo</th>
                        <th class="px-3 py-3">Última actividad</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($resumenUsuarios as $fila)
                        <tr class="bg-white hover:bg-zinc-50/80">
                            <td class="px-3 py-2 font-mono font-medium text-zinc-900">{{ $fila['usuario']?->username ?? '—' }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ trim(($fila['usuario']?->nombre ?? '').' '.($fila['usuario']?->apellido ?? '')) ?: '—' }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $fila['usuario']?->etiquetaRol() ?? '—' }}</td>
                            <td class="px-3 py-2 tabular-nums text-zinc-800">{{ $fila['sesiones'] }}</td>
                            <td class="px-3 py-2 font-semibold text-emerald-800">{{ DuracionFormateada::desdeSegundos($fila['segundos']) }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $fila['ultima_actividad']?->format('d/m/Y H:i') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-zinc-500">No hay actividad registrada en el rango seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <h2 class="mb-2 text-sm font-bold uppercase tracking-wide text-emerald-800">Detalle de sesiones</h2>
        <div class="overflow-x-auto rounded-lg border border-zinc-200">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                        <th class="px-3 py-3">Usuario</th>
                        <th class="px-3 py-3">Inicio</th>
                        <th class="px-3 py-3">Última actividad</th>
                        <th class="px-3 py-3">Fin</th>
                        <th class="px-3 py-3">Tiempo activo</th>
                        <th class="px-3 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($sesiones as $sesion)
                        <tr class="bg-white hover:bg-zinc-50/80">
                            <td class="px-3 py-2 font-mono text-zinc-900">{{ $sesion->user?->username ?? '—' }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $sesion->iniciada_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $sesion->ultima_actividad_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $sesion->finalizada_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="px-3 py-2 font-semibold text-emerald-800">{{ DuracionFormateada::desdeSegundos($sesion->segundos_activos) }}</td>
                            <td class="px-3 py-2">
                                @if ($sesion->estaAbierta())
                                    <span class="rounded bg-sky-100 px-2 py-0.5 text-[10px] font-bold uppercase text-sky-800">En curso</span>
                                @else
                                    <span class="rounded bg-zinc-100 px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-700">Cerrada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-zinc-500">No hay sesiones en el rango seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($totalSegundos > 0)
        <script id="usabilidad-graficas" type="application/json">{!! json_encode($graficas) !!}</script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        <script>
            (function () {
                function formatearMinutosHoras(minutos) {
                    minutos = Number(minutos) || 0;

                    if (minutos >= 60) {
                        var horas = Math.floor(minutos / 60);
                        var min = Math.round(minutos % 60);

                        return min > 0 ? horas + ' h ' + min + ' min' : horas + ' h';
                    }

                    if (minutos === Math.floor(minutos)) {
                        return minutos + ' min';
                    }

                    return minutos.toFixed(1) + ' min';
                }

                function initGraficasUsabilidad() {
                    if (typeof Chart === 'undefined' || typeof ChartDataLabels === 'undefined') {
                        return setTimeout(initGraficasUsabilidad, 100);
                    }

                    Chart.register(ChartDataLabels);

                    var datos = {};

                    try {
                        datos = JSON.parse(document.getElementById('usabilidad-graficas').textContent || '{}');
                    } catch (e) {
                        return;
                    }

                    var canvasUsuarios = document.getElementById('grafica-usuarios');
                    if (canvasUsuarios && datos.por_usuario?.labels?.length) {
                        new Chart(canvasUsuarios, {
                            type: 'bar',
                            data: {
                                labels: datos.por_usuario.labels,
                                datasets: [{
                                    label: 'Minutos activos',
                                    data: datos.por_usuario.minutos,
                                    backgroundColor: datos.por_usuario.colores,
                                    borderRadius: 6,
                                }],
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    datalabels: {
                                        anchor: 'end',
                                        align: 'end',
                                        offset: 4,
                                        color: '#374151',
                                        font: { weight: '600', size: 11 },
                                        formatter: function (value) {
                                            return formatearMinutosHoras(value);
                                        },
                                    },
                                },
                                scales: {
                                    x: {
                                        beginAtZero: true,
                                        title: { display: true, text: 'Minutos / horas' },
                                        ticks: {
                                            callback: function (value) {
                                                return formatearMinutosHoras(value);
                                            },
                                        },
                                    },
                                },
                            },
                        });
                    }

                    var canvasDistribucion = document.getElementById('grafica-distribucion');
                    if (canvasDistribucion && datos.distribucion?.labels?.length) {
                        new Chart(canvasDistribucion, {
                            type: 'doughnut',
                            data: {
                                labels: datos.distribucion.labels,
                                datasets: [{
                                    data: datos.distribucion.segundos,
                                    backgroundColor: datos.distribucion.colores,
                                    borderColor: '#ffffff',
                                    borderWidth: 2,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'bottom' },
                                    datalabels: {
                                        color: '#ffffff',
                                        font: { weight: 'bold', size: 12 },
                                        formatter: function (value, ctx) {
                                            var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);

                                            if (total <= 0) {
                                                return '';
                                            }

                                            var pct = Math.round(value / total * 100);

                                            if (pct < 3) {
                                                return '';
                                            }

                                            return pct + '%';
                                        },
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function (ctx) {
                                                var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                                var val = ctx.parsed || 0;
                                                var pct = total > 0 ? Math.round(val / total * 100) : 0;

                                                return ctx.label + ': ' + formatearMinutosHoras(val / 60) + ' (' + pct + '%)';
                                            },
                                        },
                                    },
                                },
                            },
                        });
                    }

                    var canvasDias = document.getElementById('grafica-dias');
                    if (canvasDias && datos.por_dia?.labels?.length) {
                        new Chart(canvasDias, {
                            type: 'bar',
                            data: {
                                labels: datos.por_dia.labels,
                                datasets: [
                                    {
                                        type: 'bar',
                                        label: 'Minutos activos',
                                        data: datos.por_dia.minutos,
                                        backgroundColor: 'rgba(16, 185, 129, 0.75)',
                                        borderRadius: 4,
                                        yAxisID: 'y',
                                        datalabels: {
                                            anchor: 'end',
                                            align: 'end',
                                            offset: 2,
                                            color: '#047857',
                                            font: { weight: '600', size: 10 },
                                            formatter: function (value) {
                                                return value > 0 ? formatearMinutosHoras(value) : '';
                                            },
                                        },
                                    },
                                    {
                                        type: 'line',
                                        label: 'Sesiones',
                                        data: datos.por_dia.sesiones,
                                        borderColor: '#0ea5e9',
                                        backgroundColor: '#0ea5e9',
                                        tension: 0.25,
                                        yAxisID: 'y1',
                                        datalabels: {
                                            anchor: 'end',
                                            align: 'top',
                                            offset: 4,
                                            color: '#0369a1',
                                            font: { weight: '600', size: 10 },
                                            formatter: function (value) {
                                                return value > 0 ? value : '';
                                            },
                                        },
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    datalabels: {
                                        display: function (ctx) {
                                            return Number(ctx.dataset.data[ctx.dataIndex]) > 0;
                                        },
                                    },
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        position: 'left',
                                        title: { display: true, text: 'Minutos / horas' },
                                        ticks: {
                                            callback: function (value) {
                                                return formatearMinutosHoras(value);
                                            },
                                        },
                                    },
                                    y1: {
                                        beginAtZero: true,
                                        position: 'right',
                                        grid: { drawOnChartArea: false },
                                        title: { display: true, text: 'Sesiones' },
                                        ticks: { stepSize: 1 },
                                    },
                                },
                            },
                        });
                    }
                }

                initGraficasUsabilidad();
            })();
        </script>
    @endif
@endsection
