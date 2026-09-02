<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="icon" href="{{ asset('image/usuario.png') }}" type="image/png" sizes="any">
    <title>@yield('title', config('app.name'))</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="app-bg min-h-dvh overflow-x-hidden bg-zinc-900 bg-fixed bg-center bg-no-repeat font-sans text-zinc-900 antialiased"
    style="background-image: url('{{ asset('image/fond.jpg') }}');"
>
    <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white shadow-sm">
        <div class="flex w-full items-center gap-2 px-3 py-2.5 sm:px-4 md:gap-3 md:px-6 md:py-3">
            <div class="flex min-w-0 flex-1 items-center gap-2 md:gap-3">
                <img src="{{ asset('image/colbeef.png') }}" alt="Logo institucional" class="h-8 w-auto shrink-0 sm:h-9 md:h-10">
                <a href="{{ route('dashboard') }}" class="truncate text-sm font-semibold text-zinc-900 sm:text-base md:text-lg">{{ config('app.name') }}</a>
            </div>

            <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                @include('layouts._busqueda_global')
                @include('layouts._menu_usuario')
                <button
                    type="button"
                    id="nav-movil-btn"
                    class="inline-flex items-center justify-center rounded-lg border border-zinc-300 bg-white p-2 text-zinc-700 shadow-sm transition hover:bg-zinc-50 lg:hidden"
                    aria-expanded="false"
                    aria-controls="nav-movil-panel"
                    aria-label="Abrir menú de navegación"
                >
                    <svg class="nav-movil-icon size-5" data-icon="abrir" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
                    </svg>
                    <svg class="nav-movil-icon hidden size-5" data-icon="cerrar" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                    </svg>
                </button>
            </div>
        </div>

        <nav class="hidden min-w-0 items-center gap-0.5 overflow-x-auto px-3 pb-2.5 text-xs font-medium md:gap-1 md:px-6 md:text-sm lg:flex [&::-webkit-scrollbar]:hidden" style="-ms-overflow-style: none; scrollbar-width: none;">
            @include('layouts._nav_links')
        </nav>

        <div
            id="nav-movil-panel"
            class="hidden border-t border-zinc-200 bg-white px-3 py-3 lg:hidden"
            hidden
        >
            <nav class="flex flex-col gap-1">
                @include('layouts._nav_links', [
                    'navLinkClass' => 'rounded-lg px-3 py-2.5 text-sm font-medium transition',
                ])
            </nav>
        </div>
    </header>

    <main class="app-main @yield('containerClass')">
        @yield('content')
    </main>

    @include('layouts._footer')

    @include('layouts._busqueda_global_panel')
    @include('layouts._busqueda_resaltar_util')
    @include('layouts._busqueda_global_script')
    @include('layouts._menu_usuario_script')
    @include('layouts._nav_movil_script')

    @auth
        @if (config('usabilidad.cerrar_sesion_por_inactividad'))
            @include('layouts._sesion_inactividad_script')
        @endif
    @endauth
</body>
</html>
