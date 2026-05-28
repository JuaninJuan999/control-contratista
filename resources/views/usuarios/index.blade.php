@extends('layouts.app')

@section('title', 'Gestión de usuarios — '.config('app.name'))

@section('content')
    <div class="rounded-lg border border-zinc-200 bg-white p-4 shadow-lg md:p-6">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-display text-2xl font-semibold text-zinc-950 md:text-3xl">Gestión de usuarios</h1>
            @if (auth()->user()?->puedeEditar())
            <a href="{{ route('usuarios.create') }}" class="rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-800">
                Nuevo usuario
            </a>
            @endif
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto rounded-lg border border-zinc-200">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="bg-emerald-700 text-xs font-bold uppercase tracking-wide text-white">
                        <th class="px-3 py-3">Nombre</th>
                        <th class="px-3 py-3">Apellido</th>
                        <th class="px-3 py-3">Username</th>
                        <th class="px-3 py-3">Correo electrónico</th>
                        <th class="px-3 py-3">Rol</th>
                        <th class="px-3 py-3">Estado</th>
                        <th class="px-3 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($usuarios as $usuario)
                        <tr class="bg-white hover:bg-zinc-50/80 {{ ! $usuario->activo ? 'opacity-60' : '' }}">
                            <td class="px-3 py-2 font-medium text-zinc-900">{{ $usuario->nombre ?: '—' }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $usuario->apellido ?: '—' }}</td>
                            <td class="px-3 py-2 font-mono text-zinc-800">{{ $usuario->username }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $usuario->email }}</td>
                            <td class="px-3 py-2 text-zinc-800">{{ $usuario->etiquetaRol() }}</td>
                            <td class="px-3 py-2">
                                @if ($usuario->activo)
                                    <span class="rounded bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-800">Activo</span>
                                @else
                                    <span class="rounded bg-zinc-200 px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-700">Inactivo</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                @include('usuarios._acciones_usuario', ['usuario' => $usuario])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-8 text-center text-zinc-500">
                                No hay usuarios registrados.
                                <a href="{{ route('usuarios.create') }}" class="font-medium text-emerald-700 underline hover:text-emerald-800">Crear el primero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
