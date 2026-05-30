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
    class="app-bg app-bg--sin-oscurecer flex h-dvh flex-col overflow-hidden bg-zinc-900 bg-fixed bg-contain bg-center bg-no-repeat text-zinc-900 antialiased"
    style="background-image: url('{{ asset('image/fond.jpg') }}');"
>
    <main class="flex min-h-0 flex-1 flex-col items-center justify-center px-4 py-3">
        @yield('content')
    </main>

    @include('layouts._footer', [
        'footerOscuro' => true,
        'footerClass' => 'relative z-10 shrink-0 px-4 pb-3 pt-1 text-center text-[11px] font-semibold leading-snug text-white drop-shadow-[0_1px_6px_rgba(0,0,0,0.9)] sm:text-xs',
    ])
</body>
</html>
