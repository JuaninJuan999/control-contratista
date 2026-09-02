@php
    $navLinkClass = $navLinkClass ?? 'shrink-0 rounded-lg px-2 py-1.5 text-xs font-medium transition md:px-3 md:py-2 md:text-sm';
    $navActiveClass = 'bg-emerald-700 text-white';
    $navInactiveClass = 'text-zinc-700 hover:bg-zinc-100';

    $enlaces = [
        ['route' => 'dashboard', 'pattern' => 'dashboard', 'label' => 'Dashboard'],
        ['route' => 'empresas.index', 'pattern' => 'empresas.*', 'label' => 'Empresas'],
        ['route' => 'planillas.index', 'pattern' => 'planillas.*', 'label' => 'Planillas'],
        ['route' => 'contratistas-externos.index', 'pattern' => 'contratistas-externos.*', 'label' => 'Externos'],
        ['route' => 'contratistas-internos.index', 'pattern' => 'contratistas-internos.*', 'label' => 'Internos'],
        ['route' => 'vehiculos.index', 'pattern' => 'vehiculos.*', 'label' => 'Vehículos'],
    ];
@endphp

@foreach ($enlaces as $enlace)
    <a
        href="{{ route($enlace['route']) }}"
        class="{{ $navLinkClass }} {{ request()->routeIs($enlace['pattern']) ? $navActiveClass : $navInactiveClass }}"
    >
        {{ $enlace['label'] }}
    </a>
@endforeach

@include('layouts._workbeef_link')
