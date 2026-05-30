@extends('layouts.guest')

@section('title', 'Iniciar sesión — '.config('app.name'))

@section('content')
<div class="mx-auto w-full max-w-md">
    <div class="mb-4 flex justify-center">
        <img
            src="{{ asset('image/colbeef.png') }}"
            alt="Logo institucional"
            class="h-14 w-auto drop-shadow-[0_2px_12px_rgba(255,255,255,0.85)] sm:h-16"
        >
    </div>
    <h1 class="text-center font-display text-2xl font-semibold leading-snug tracking-tight text-zinc-950 drop-shadow-[0_1px_12px_rgba(255,255,255,0.95)] sm:text-3xl md:text-4xl">
        {{ config('app.name') }}
    </h1>
    <p class="mx-auto mt-2 max-w-md text-center font-sans text-sm font-medium leading-relaxed tracking-wide text-zinc-800 drop-shadow-[0_1px_8px_rgba(255,255,255,0.92)] sm:mt-3 sm:text-[0.95rem]">
        Control de Contratistas Internos y Externos
    </p>

    <div class="mt-5 rounded-xl border border-white/40 bg-transparent p-5 shadow-xl ring-1 ring-white/20 sm:mt-6 sm:p-6">
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-400/50 bg-red-500/25 px-3 py-2 text-sm text-red-950 backdrop-blur-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.store') }}" method="post" class="flex flex-col gap-4">
                @csrf

                <div>
                    <label for="username" class="block text-sm font-semibold text-zinc-950">Usuario</label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        autocomplete="username"
                        required
                        autofocus
                        class="mt-1 w-full rounded-lg border border-zinc-300 bg-transparent px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none transition-colors duration-150 focus:border-zinc-900 focus:bg-white focus:ring-1 focus:ring-zinc-900"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-zinc-950">Contraseña</label>
                    <div class="relative mt-1">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            class="w-full rounded-lg border border-zinc-300 bg-transparent py-2 pl-3 pr-11 text-sm text-zinc-900 shadow-sm outline-none transition-colors duration-150 focus:border-zinc-900 focus:bg-white focus:ring-1 focus:ring-zinc-900"
                        >
                        <button
                            type="button"
                            id="toggle-password"
                            class="absolute right-2 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-zinc-600 hover:bg-white/50 hover:text-zinc-900 focus-visible:outline focus-visible:ring-2 focus-visible:ring-zinc-900"
                            aria-controls="password"
                            aria-pressed="false"
                            aria-label="Mostrar contraseña"
                            title="Mostrar contraseña"
                        >
                            <span id="toggle-password-show" class="block" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 5 12 5c4.638 0 8.573 2.487 9.963 6.578.207.579.207 1.281 0 1.859C20.577 16.49 16.64 19 12 19c-4.638 0-8.573-2.487-9.964-6.579a1.015 1.015 0 010-.098z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <span id="toggle-password-hide" class="hidden" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 5 12 5c4.638 0 8.573 2.487 9.963 6.578.207.579.207 1.281 0 1.859C20.577 16.49 16.64 19 12 19c-4.638 0-8.573-2.487-9.964-6.579a1.015 1.015 0 010-.098z M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 20L20 4" />
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                <label class="inline-flex cursor-pointer items-center gap-2 text-sm font-semibold text-zinc-950">
                    <input type="checkbox" name="remember" value="1" class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900" {{ old('remember') ? 'checked' : '' }}>
                    Recordarme en este equipo
                </label>

                <button type="submit" class="w-full rounded-lg bg-zinc-900 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-800">
                    Entrar
                </button>
            </form>
        </div>
</div>

<script>
    (function () {
        var input = document.getElementById('password');
        var btn = document.getElementById('toggle-password');
        var iconShow = document.getElementById('toggle-password-show');
        var iconHide = document.getElementById('toggle-password-hide');
        if (!input || !btn || !iconShow || !iconHide) return;

        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
            btn.setAttribute('title', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
            iconShow.classList.toggle('hidden', show);
            iconHide.classList.toggle('hidden', !show);
        });
    })();
</script>
@endsection
