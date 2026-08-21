@php
    /** @var \App\Models\ContratistaInterno $contratista */
    /** @var int $anio */
    /** @var int $mes */
    /** @var bool $puedeEditar */

    $ui = $contratista->controlMesSsUi($anio, $mes, $puedeEditar);
    $clasesEstado = match ($ui['estado']) {
        'ok' => 'bg-emerald-100 text-emerald-800',
        'rechazado' => 'bg-red-100 text-red-700',
        default => 'bg-zinc-200 text-zinc-600',
    };
    $clasesHover = $ui['editable']
        ? ($ui['estado'] === 'ok'
            ? 'hover:bg-emerald-200'
            : ($ui['estado'] === 'rechazado' ? 'hover:bg-red-200' : 'hover:bg-zinc-300 hover:text-zinc-800'))
        : '';
    $clasesBadge = match ($ui['urgencia']) {
        'proxima' => 'bg-amber-500 text-white',
        'vigente' => 'bg-emerald-700 text-white',
        default => 'bg-zinc-500 text-white',
    };
@endphp

<td class="px-0.5 py-2 text-center">
    @if ($ui['editable'] && $puedeEditar)
        <form action="{{ route('contratistas-internos.toggle-mes', $contratista) }}" method="post" class="inline" onclick="event.stopPropagation()">
            @csrf
            @method('PATCH')
            <input type="hidden" name="anio" value="{{ $anio }}">
            <input type="hidden" name="mes" value="{{ $mes }}">
            <button
                type="submit"
                title="{{ $ui['titulo'] }}"
                class="relative inline-flex h-7 min-w-7 items-center justify-center rounded px-0.5 text-[10px] font-bold transition {{ $clasesEstado }} {{ $clasesHover }}"
            >
                {{ $ui['estado'] === 'rechazado' ? '✕' : $ui['abrev'] }}
            </button>
        </form>
    @else
        <span
            title="{{ $ui['titulo'] }}"
            class="relative inline-flex h-7 min-w-7 items-center justify-center rounded px-0.5 text-[10px] font-bold {{ $clasesEstado }}"
        >
            {{ $ui['estado'] === 'rechazado' ? '✕' : $ui['abrev'] }}
            @if ($ui['mostrar_badge'])
                <span class="absolute -right-1.5 -top-1.5 inline-flex min-h-[14px] min-w-[14px] items-center justify-center rounded-full px-0.5 text-[9px] font-bold leading-none shadow {{ $clasesBadge }}">
                    {{ $ui['dias'] }}
                </span>
            @endif
        </span>
    @endif
</td>
