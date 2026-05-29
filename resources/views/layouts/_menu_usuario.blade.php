@php
    $usuario = auth()->user();
    $nombreUsuario = $usuario->username ?? $usuario->name;
    $puedeUsuarios = $usuario?->puedeAccederModuloUsuarios();
@endphp

<div class="relative shrink-0" id="menu-usuario">
    <button
        type="button"
        id="menu-usuario-btn"
        aria-expanded="false"
        aria-controls="menu-usuario-panel"
        aria-haspopup="true"
        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-700 bg-emerald-700 px-3 py-1.5 text-sm font-bold uppercase tracking-wide text-white shadow-sm transition hover:bg-emerald-800"
    >
        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M2 4.75A.75.75 0 0 1 2.75 4h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 4.75ZM2 10a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75A.75.75 0 0 1 2 10Zm0 5.25a.75.75 0 0 1 .75-.75h14.5a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
        </svg>
        Yo
    </button>

    <div
        id="menu-usuario-panel"
        class="absolute right-0 top-full z-[9999] mt-1 hidden min-w-[11rem] overflow-hidden rounded-lg border border-zinc-200 bg-white py-1 shadow-xl ring-1 ring-black/5"
        role="menu"
        hidden
    >
        <p class="border-b border-zinc-100 px-3 py-2.5 text-sm font-semibold text-zinc-900" role="none">
            {{ $nombreUsuario }}
        </p>

        @if ($puedeUsuarios)
            <a
                href="{{ route('usuarios.index') }}"
                role="menuitem"
                class="flex w-full px-3 py-2 text-left text-sm text-zinc-700 transition hover:bg-emerald-50 hover:text-emerald-900 {{ request()->routeIs('usuarios.*') ? 'bg-emerald-50 font-semibold text-emerald-900' : '' }}"
            >
                Usuarios
            </a>
        @endif

        <form action="{{ route('logout') }}" method="post" role="none">
            @csrf
            <button
                type="submit"
                role="menuitem"
                class="flex w-full px-3 py-2 text-left text-sm font-medium text-red-700 transition hover:bg-red-50"
            >
                Cerrar sesión
            </button>
        </form>
    </div>
</div>
