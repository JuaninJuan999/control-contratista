@php
    $tema = $vencida
        ? ['borde' => 'border-red-200', 'fondo' => 'bg-red-100/80', 'texto' => 'text-red-700', 'textoFuerte' => 'text-red-800', 'pill' => 'bg-red-600', 'activo' => 'border-red-400 ring-2 ring-red-300 ring-offset-1', 'seccion' => 'dash-alert-section--vencida', 'tarjeta' => 'dash-alert-card--vencida', 'emoji' => '🔴']
        : ['borde' => 'border-amber-200', 'fondo' => 'bg-amber-100/80', 'texto' => 'text-amber-700', 'textoFuerte' => 'text-amber-800', 'pill' => 'bg-amber-500', 'activo' => 'border-amber-400 ring-2 ring-amber-300 ring-offset-1', 'seccion' => 'dash-alert-section--proxima', 'tarjeta' => 'dash-alert-card--proxima', 'emoji' => '⏳'];
    $emojisTipo = [
        'empresas' => '🏢',
        'ind_rnd' => '🩺',
        'licencia' => '🪪',
        'manipulador' => '🍽️',
        'soat' => '🛡️',
        'tecnomecanica' => '🔧',
        'inspeccion' => '🏥',
    ];
    $total = $grupos->sum(fn ($g) => $g->count());
    $seccionDelay = $vencida ? '0.25s' : '0.35s';
@endphp

<section class="dash-alert-section {{ $tema['seccion'] }}" data-alert-section="{{ $prefijo }}" style="animation-delay: {{ $seccionDelay }}">
    <div class="mb-3 flex items-center justify-between gap-3">
        <h2 class="font-display text-lg font-semibold {{ $tema['textoFuerte'] }} md:text-xl">
            <span aria-hidden="true">{{ $tema['emoji'] }}</span> {{ $titulo }}
        </h2>
        <span class="rounded-full {{ $tema['pill'] }} px-2.5 py-0.5 text-xs font-bold text-white shadow-sm">{{ $total }}</span>
    </div>

    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-7">
        @foreach ($tipos as $tipoKey => $tipoLabel)
            @php $cantidad = $grupos->get($tipoKey)?->count() ?? 0; @endphp
            <button
                type="button"
                @disabled($cantidad === 0)
                data-alert-card="{{ $prefijo }}-{{ $tipoKey }}"
                class="dash-alert-card {{ $cantidad > 0 ? $tema['tarjeta'].' cursor-pointer' : 'dash-alert-card--inactiva' }}"
                style="animation-delay: {{ $loop->index * 0.04 + ($vencida ? 0.3 : 0.4) }}s"
            >
                <span class="text-xl leading-none" aria-hidden="true">{{ $emojisTipo[$tipoKey] ?? '📋' }}</span>
                <span class="text-2xl font-bold {{ $cantidad > 0 ? $tema['texto'] : 'text-zinc-400' }}">{{ $cantidad }}</span>
                <span class="text-[11px] font-semibold uppercase leading-tight tracking-wide {{ $cantidad > 0 ? 'text-zinc-700' : 'text-zinc-400' }}">{{ $tipoLabel }}</span>
            </button>
        @endforeach
    </div>

    <div class="mt-3">
        @foreach ($tipos as $tipoKey => $tipoLabel)
            @php $registros = $grupos->get($tipoKey); @endphp
            @if ($registros && $registros->count() > 0)
                <div class="alert-panel hidden" data-alert-panel="{{ $prefijo }}-{{ $tipoKey }}" hidden>
                    <div class="overflow-x-auto rounded-lg border {{ $tema['borde'] }} bg-white/70 backdrop-blur-sm">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="{{ $tema['fondo'] }} text-[11px] font-bold uppercase tracking-wide {{ $tema['textoFuerte'] }}">
                                    <th class="px-3 py-2">{{ $tipoLabel }}</th>
                                    <th class="px-3 py-2">Detalle</th>
                                    <th class="px-3 py-2 whitespace-nowrap">Fecha</th>
                                    <th class="px-3 py-2 whitespace-nowrap text-right">{{ $vencida ? 'Vencimiento' : 'Restante' }}</th>
                                    @if (auth()->user()?->puedeEditar())
                                    <th class="px-3 py-2 whitespace-nowrap text-center">Acción</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100/80">
                                @foreach ($registros as $item)
                                    <tr class="bg-white/50 hover:bg-white/80">
                                        <td class="px-3 py-2 font-medium text-zinc-900">
                                            <a href="{{ $item['url'] }}" class="hover:underline">{{ $item['titulo'] }}</a>
                                        </td>
                                        <td class="px-3 py-2 text-zinc-600">{{ $item['detalle'] }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-zinc-700">{{ $item['fecha']->format('d/m/Y') }}</td>
                                        <td class="px-3 py-2 whitespace-nowrap text-right font-bold {{ $tema['texto'] }}">
                                            @if ($vencida)
                                                Hace {{ abs($item['dias']) }} día{{ abs($item['dias']) === 1 ? '' : 's' }}
                                            @else
                                                {{ $item['dias'] === 0 ? 'Hoy' : 'En '.$item['dias'].' día'.($item['dias'] === 1 ? '' : 's') }}
                                            @endif
                                        </td>
                                        @if (auth()->user()?->puedeEditar())
                                        <td class="px-3 py-2 whitespace-nowrap text-center">
                                            <a href="{{ $item['editar_url'] }}" class="inline-flex items-center gap-1 rounded-md bg-emerald-700 px-2.5 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-800 hover:shadow-md">
                                                Renovar
                                            </a>
                                        </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach

        @if ($total === 0)
            <p class="py-6 text-center text-sm text-zinc-600">Sin novedades.</p>
        @else
            <p class="alert-hint py-4 text-center text-xs text-zinc-500">Haz clic en una tarjeta para ver el detalle.</p>
        @endif
    </div>
</section>
