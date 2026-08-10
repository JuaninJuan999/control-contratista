@extends('layouts.app')

@section('title', 'Dashboard — '.config('app.name'))

@section('content')
    <div class="mb-5">
        <h1 class="login-text-glow font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Dashboard</h1>
        <p class="login-text-glow mt-1 text-sm font-medium text-zinc-900">Resumen del sistema y alertas de vencimientos.</p>
    </div>

    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:gap-4">
        <a href="{{ route('empresas.index') }}" class="dash-stat-card dash-stat-card--empresas hover:border-emerald-300" style="animation-delay: 0.05s">
            <p class="text-2xl leading-none" aria-hidden="true">🏢</p>
            <p class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-emerald-800/70">Empresas</p>
            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-2">
                <div>
                    <p class="font-display text-3xl font-bold tabular-nums leading-none text-emerald-900">{{ $totales['empresas'] }}</p>
                    <p class="mt-0.5 text-xs text-emerald-800/60">Registradas</p>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wide text-violet-800/80">Interna</span>
                        <span class="font-display text-lg font-bold tabular-nums text-violet-900">{{ $totales['empresas_internas'] }}</span>
                    </div>
                    <div class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wide text-sky-800/80">Externa</span>
                        <span class="font-display text-lg font-bold tabular-nums text-sky-900">{{ $totales['empresas_externas'] }}</span>
                    </div>
                    @if ($totales['empresas_sin_clasificar'] > 0)
                    <div class="flex items-baseline justify-between gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wide text-zinc-600">Sin clasificar</span>
                        <span class="font-display text-lg font-bold tabular-nums text-zinc-700">{{ $totales['empresas_sin_clasificar'] }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </a>
        <a href="{{ route('contratistas-externos.index') }}" class="dash-stat-card dash-stat-card--externos hover:border-sky-300" style="animation-delay: 0.1s">
            <p class="text-2xl leading-none" aria-hidden="true">👷</p>
            <p class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-sky-800/70">Contratistas externos</p>
            <p class="mt-1 font-display text-3xl font-bold tabular-nums text-sky-900">{{ $totales['contratistas_externos'] }}</p>
            <p class="mt-0.5 text-xs text-sky-800/60">Registrados</p>
        </a>
        <a href="{{ route('contratistas-internos.index') }}" class="dash-stat-card dash-stat-card--internos hover:border-violet-300" style="animation-delay: 0.15s">
            <p class="text-2xl leading-none" aria-hidden="true">👥</p>
            <p class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-violet-800/70">Contratistas internos</p>
            <p class="mt-1 font-display text-3xl font-bold tabular-nums text-violet-900">{{ $totales['contratistas_internos'] }}</p>
            <p class="mt-0.5 text-xs text-violet-800/60">Registrados</p>
        </a>
        <a href="{{ route('vehiculos.index') }}" class="dash-stat-card dash-stat-card--vehiculos hover:border-amber-300" style="animation-delay: 0.2s">
            <p class="text-2xl leading-none" aria-hidden="true">🚗</p>
            <p class="mt-1.5 text-[11px] font-bold uppercase tracking-wide text-amber-800/70">Vehículos</p>
            <p class="mt-1 font-display text-3xl font-bold tabular-nums text-amber-900">{{ $totales['vehiculos'] }}</p>
            <p class="mt-0.5 text-xs text-amber-800/60">Registrados</p>
        </a>
    </div>

    <div class="flex flex-col gap-5">
        @include('dashboard._pendientes_induccion', ['pendientesInduccion' => $pendientesInduccion])

        @include('dashboard._seccion_alertas', [
            'titulo' => 'Vencidas',
            'prefijo' => 'vencida',
            'grupos' => $vencidas,
            'tipos' => $tipos,
            'vencida' => true,
        ])

        @include('dashboard._seccion_alertas', [
            'titulo' => 'Próximas a vencer',
            'prefijo' => 'proxima',
            'grupos' => $proximas,
            'tipos' => $tipos,
            'vencida' => false,
        ])

        {{-- Gráficas por estado --}}
        <section class="rounded-xl border border-zinc-200 bg-white p-4 shadow-lg md:p-5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-display text-lg font-semibold text-zinc-950 md:text-xl">Distribución por estado</h2>
                <div class="flex flex-wrap items-center gap-3 text-xs text-zinc-600">
                    <span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-sm bg-emerald-500"></span> Vigente</span>
                    <span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-sm bg-amber-400"></span> Próximo a vencer</span>
                    <span class="inline-flex items-center gap-1.5"><span class="size-3 rounded-sm bg-red-500"></span> Vencido</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @php
                    $emojisEstadisticas = [
                        'empresas' => '🏢',
                        'ind_rnd' => '🩺',
                        'licencia' => '🪪',
                        'manipulador' => '🍽️',
                        'soat' => '🛡️',
                        'tecnomecanica' => '🔧',
                        'inspeccion' => '🏥',
                    ];
                @endphp
                @foreach ($estadisticas as $tipoKey => $est)
                    <div class="rounded-lg border border-zinc-200 p-3">
                        <p class="mb-2 text-center text-sm font-semibold text-zinc-800">
                            <span class="mr-1" aria-hidden="true">{{ $emojisEstadisticas[$tipoKey] ?? '📊' }}</span>{{ $est['label'] }}
                        </p>
                        @if ($est['total'] === 0)
                            <p class="py-10 text-center text-xs text-zinc-400">Sin datos</p>
                        @else
                            <div class="relative mx-auto h-40 w-40">
                                <canvas data-chart="{{ $tipoKey }}"></canvas>
                            </div>
                            <div class="mt-3 space-y-1 text-xs">
                                @php
                                    $total = $est['total'];
                                    $pct = fn ($n) => $total > 0 ? round($n / $total * 100) : 0;
                                @endphp
                                <div class="flex items-center justify-between"><span class="text-zinc-600">Vigente</span><span class="font-semibold text-emerald-700">{{ $est['vigente'] }} ({{ $pct($est['vigente']) }}%)</span></div>
                                <div class="flex items-center justify-between"><span class="text-zinc-600">Próximo a vencer</span><span class="font-semibold text-amber-600">{{ $est['proximo'] }} ({{ $pct($est['proximo']) }}%)</span></div>
                                <div class="flex items-center justify-between"><span class="text-zinc-600">Vencido</span><span class="font-semibold text-red-600">{{ $est['vencido'] }} ({{ $pct($est['vencido']) }}%)</span></div>
                                <div class="flex items-center justify-between border-t border-zinc-100 pt-1"><span class="text-zinc-600">Total</span><span class="font-semibold text-zinc-800">{{ $total }}</span></div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <script id="dashboard-stats" type="application/json">{!! json_encode($estadisticas) !!}</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

    <script>
        (function () {
            function initCharts() {
                if (typeof Chart === 'undefined') {
                    return setTimeout(initCharts, 100);
                }
                var data = {};
                try {
                    data = JSON.parse(document.getElementById('dashboard-stats').textContent || '{}');
                } catch (e) {
                    return;
                }
                document.querySelectorAll('[data-chart]').forEach(function (canvas) {
                    var key = canvas.getAttribute('data-chart');
                    var est = data[key];
                    if (!est) return;
                    new Chart(canvas, {
                        type: 'doughnut',
                        data: {
                            labels: ['Vigente', 'Próximo a vencer', 'Vencido'],
                            datasets: [{
                                data: [est.vigente, est.proximo, est.vencido],
                                backgroundColor: ['#10b981', '#fbbf24', '#ef4444'],
                                borderColor: '#ffffff',
                                borderWidth: 2,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            var total = est.total || 0;
                                            var val = ctx.parsed || 0;
                                            var pct = total > 0 ? Math.round(val / total * 100) : 0;
                                            return ctx.label + ': ' + val + ' (' + pct + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                });
            }
            initCharts();

            document.addEventListener('click', function (event) {
                var card = event.target.closest('[data-alert-card]');
                if (!card) return;

                var id = card.getAttribute('data-alert-card');
                var section = card.closest('[data-alert-section]');
                if (!section) return;

                var panel = section.querySelector('[data-alert-panel="' + id + '"]');
                var yaAbierto = panel && !panel.hidden;

                section.querySelectorAll('[data-alert-panel]').forEach(function (p) {
                    p.hidden = true;
                    p.classList.add('hidden');
                });
                section.querySelectorAll('[data-alert-card]').forEach(function (c) {
                    c.classList.remove('ring-2', 'ring-offset-1', 'border-red-400', 'border-amber-400', 'ring-red-300', 'ring-amber-300');
                });

                var hint = section.querySelector('.alert-hint');

                if (panel && !yaAbierto) {
                    panel.hidden = false;
                    panel.classList.remove('hidden');
                    var esVencida = section.getAttribute('data-alert-section') === 'vencida';
                    card.classList.add('ring-2', 'ring-offset-1');
                    card.classList.add(esVencida ? 'border-red-400' : 'border-amber-400');
                    card.classList.add(esVencida ? 'ring-red-300' : 'ring-amber-300');
                    if (hint) hint.classList.add('hidden');
                } else if (hint) {
                    hint.classList.remove('hidden');
                }
            });
        })();
    </script>
@endsection
