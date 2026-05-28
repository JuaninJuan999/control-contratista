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
    class="min-h-screen bg-zinc-900 bg-fixed bg-contain bg-center bg-no-repeat font-sans text-zinc-900 antialiased"
    style="background-image: url('{{ asset('image/fond.jpg') }}');"
>
    <header class="border-b border-zinc-200 bg-white px-6 py-4 shadow-sm">
        <div class="mx-auto flex @yield('containerClass', 'max-w-6xl') flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-6">
                <a href="{{ route('dashboard') }}" class="text-lg font-semibold text-zinc-900">{{ config('app.name') }}</a>
                <nav class="flex flex-wrap gap-1 text-sm font-medium">
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-lg px-3 py-2 transition {{ request()->routeIs('dashboard') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                    >
                        Dashboard
                    </a>
                    <a
                        href="{{ route('empresas.index') }}"
                        class="rounded-lg px-3 py-2 transition {{ request()->routeIs('empresas.*') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                    >
                        Empresas
                    </a>
                    <a
                        href="{{ route('contratistas-externos.index') }}"
                        class="rounded-lg px-3 py-2 transition {{ request()->routeIs('contratistas-externos.*') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                    >
                        Externos
                    </a>
                    <a
                        href="{{ route('contratistas-internos.index') }}"
                        class="rounded-lg px-3 py-2 transition {{ request()->routeIs('contratistas-internos.*') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                    >
                        Internos
                    </a>
                    <a
                        href="{{ route('vehiculos.index') }}"
                        class="rounded-lg px-3 py-2 transition {{ request()->routeIs('vehiculos.*') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                    >
                        Vehículos
                    </a>
                    @if (auth()->user()?->puedeAccederModuloUsuarios())
                    <a
                        href="{{ route('usuarios.index') }}"
                        class="rounded-lg px-3 py-2 transition {{ request()->routeIs('usuarios.*') ? 'bg-emerald-700 text-white' : 'text-zinc-700 hover:bg-zinc-100' }}"
                    >
                        Usuarios
                    </a>
                    @endif
                </nav>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm text-zinc-600">{{ auth()->user()->username ?? auth()->user()->name }}</span>
                <span class="rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700">{{ auth()->user()->etiquetaRol() }}</span>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-800 hover:bg-zinc-50">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto @yield('containerClass', 'max-w-6xl') px-6 py-10">
        @yield('content')
    </main>
</body>
</html>
