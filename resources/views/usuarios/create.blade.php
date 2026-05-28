@extends('layouts.app')

@section('title', 'Nuevo usuario — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h1 class="font-display text-xl font-semibold text-zinc-950 md:text-2xl">Nuevo usuario</h1>
        <a href="{{ route('usuarios.index') }}" class="text-xs font-medium text-emerald-800 underline hover:text-emerald-950 md:text-sm">
            Volver al listado
        </a>
    </div>

    <div class="max-w-2xl rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-5">
        @if ($errors->any())
            <div class="mb-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-900 md:text-sm">
                <p class="font-semibold">Revisa los datos:</p>
                <ul class="mt-1 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('usuarios.store') }}" method="post" class="flex flex-col gap-3" id="form-usuario">
            @csrf

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label for="nombre" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Nombre</label>
                    <input
                        type="text"
                        name="nombre"
                        id="nombre"
                        value="{{ old('nombre') }}"
                        required
                        maxlength="120"
                        autocomplete="given-name"
                        class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                </div>
                <div>
                    <label for="apellido" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Apellido</label>
                    <input
                        type="text"
                        name="apellido"
                        id="apellido"
                        value="{{ old('apellido') }}"
                        required
                        maxlength="120"
                        autocomplete="family-name"
                        class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                </div>
            </div>

            <div>
                <label for="username" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Username</label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    value="{{ old('username') }}"
                    required
                    maxlength="255"
                    autocomplete="username"
                    placeholder="nombre.apellido"
                    class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 font-mono text-sm lowercase text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
                <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Se sugiere automáticamente como <strong>nombre.apellido</strong>. Puedes editarlo.</p>
            </div>

            <div>
                <label for="email" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Correo electrónico</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    required
                    maxlength="255"
                    autocomplete="email"
                    class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                >
                <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Varios usuarios pueden compartir el mismo correo.</p>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Contraseña</label>
                <div class="relative mt-0.5">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        minlength="8"
                        maxlength="255"
                        autocomplete="new-password"
                        class="w-full rounded-md border border-zinc-300 bg-white py-1.5 pl-2.5 pr-10 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
                    >
                    <button
                        type="button"
                        id="toggle-password"
                        class="absolute inset-y-0 right-0 flex items-center px-2.5 text-xs font-semibold text-emerald-800 hover:text-emerald-950"
                        aria-label="Mostrar u ocultar contraseña"
                    >
                        Ver
                    </button>
                </div>
                <p class="mt-0.5 text-[11px] leading-tight text-zinc-500">Mínimo 8 caracteres.</p>
            </div>

            @include('usuarios._campo_rol')

            <button type="submit" class="mt-1 w-full rounded-md bg-emerald-700 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-800 sm:w-auto sm:px-6">
                Guardar usuario
            </button>
        </form>
    </div>

    <script>
        (function () {
            var nombreInput = document.getElementById('nombre');
            var apellidoInput = document.getElementById('apellido');
            var usernameInput = document.getElementById('username');
            var passwordInput = document.getElementById('password');
            var togglePassword = document.getElementById('toggle-password');
            var usernameManual = {{ old('username') ? 'true' : 'false' }};

            function slugPart(value) {
                return value
                    .trim()
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '.')
                    .replace(/^\.+|\.+$/g, '');
            }

            function sugerirUsername() {
                if (usernameManual || !nombreInput || !apellidoInput || !usernameInput) {
                    return;
                }

                var partes = [slugPart(nombreInput.value), slugPart(apellidoInput.value)].filter(Boolean);
                usernameInput.value = partes.join('.');
            }

            if (usernameInput) {
                usernameInput.addEventListener('input', function () {
                    usernameManual = usernameInput.value.trim() !== '';
                });
            }

            if (nombreInput) {
                nombreInput.addEventListener('input', sugerirUsername);
                nombreInput.addEventListener('blur', sugerirUsername);
            }

            if (apellidoInput) {
                apellidoInput.addEventListener('input', sugerirUsername);
                apellidoInput.addEventListener('blur', sugerirUsername);
            }

            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    var visible = passwordInput.type === 'text';
                    passwordInput.type = visible ? 'password' : 'text';
                    togglePassword.textContent = visible ? 'Ver' : 'Ocultar';
                });
            }
        })();
    </script>
@endsection
