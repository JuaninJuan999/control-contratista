<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('image/usuario.png') }}" type="image/png" sizes="any">
    <title>@yield('title', config('app.name'))</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="app-bg min-h-screen bg-zinc-900 bg-fixed bg-contain bg-center bg-no-repeat font-sans text-zinc-900 antialiased"
    style="background-image: url('{{ asset('image/fond.jpg') }}');"
>
    <header class="border-b border-zinc-200 bg-white py-3 pl-4 pr-4 shadow-sm md:pl-6 md:pr-6">
        <div class="flex w-full items-center gap-2 md:gap-3">
            <div class="flex shrink-0 items-center gap-2 md:gap-3">
                <img src="{{ asset('image/colbeef.png') }}" alt="Logo institucional" class="h-9 w-auto shrink-0 md:h-10">
                <a href="{{ route('dashboard') }}" class="hidden shrink-0 whitespace-nowrap text-base font-semibold text-zinc-900 sm:inline md:text-lg">{{ config('app.name') }}</a>
            </div>

            <nav class="flex min-w-0 flex-1 items-center gap-0.5 overflow-x-auto text-xs font-medium md:gap-1 md:text-sm [&::-webkit-scrollbar]:hidden" style="-ms-overflow-style: none; scrollbar-width: none;">
                <a
                    href="{{ route('dashboard') }}"
                    class="shrink-0 rounded-lg px-2 py-1.5 transition md:px-3 md:py-2 {{ request()->routeIs('dashboard') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                >
                    Dashboard
                </a>
                <a
                    href="{{ route('empresas.index') }}"
                    class="shrink-0 rounded-lg px-2 py-1.5 transition md:px-3 md:py-2 {{ request()->routeIs('empresas.*') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                >
                    Empresas
                </a>
                <a
                    href="{{ route('contratistas-externos.index') }}"
                    class="shrink-0 rounded-lg px-2 py-1.5 transition md:px-3 md:py-2 {{ request()->routeIs('contratistas-externos.*') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                >
                    Externos
                </a>
                <a
                    href="{{ route('contratistas-internos.index') }}"
                    class="shrink-0 rounded-lg px-2 py-1.5 transition md:px-3 md:py-2 {{ request()->routeIs('contratistas-internos.*') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                >
                    Internos
                </a>
                <a
                    href="{{ route('vehiculos.index') }}"
                    class="shrink-0 rounded-lg px-2 py-1.5 transition md:px-3 md:py-2 {{ request()->routeIs('vehiculos.*') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                >
                    Vehículos
                </a>
            </nav>

            <div class="flex shrink-0 items-center gap-2">
                @include('layouts._busqueda_global')
                @include('layouts._menu_usuario')
            </div>
        </div>
    </header>

    <main class="mx-auto @yield('containerClass', 'max-w-6xl') px-6 py-10">
        @yield('content')
    </main>

    @include('layouts._busqueda_global_panel')
    @include('layouts._busqueda_resaltar_util')
    @include('layouts._busqueda_global_script')
    @include('layouts._menu_usuario_script')
</body>
</html>
