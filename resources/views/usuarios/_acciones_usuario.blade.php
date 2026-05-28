@if ($usuario->puedeSerGestionadoPor(auth()->user()))
<div class="flex flex-wrap items-center gap-2">
    <a
        href="{{ route('usuarios.edit', $usuario) }}"
        class="rounded-md border border-emerald-700 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-900 hover:bg-emerald-100"
    >
        Editar
    </a>
    @if (! $usuario->is(auth()->user()))
        <form action="{{ route('usuarios.toggle-activo', $usuario) }}" method="post" class="inline">
            @csrf
            @method('PATCH')
            <button
                type="submit"
                class="rounded-md border px-2.5 py-1 text-xs font-semibold {{ $usuario->activo ? 'border-red-300 bg-red-50 text-red-800 hover:bg-red-100' : 'border-zinc-300 bg-zinc-50 text-zinc-800 hover:bg-zinc-100' }}"
                onclick="return confirm('{{ $usuario->activo ? '¿Inactivar este usuario?' : '¿Reactivar este usuario?' }}')"
            >
                {{ $usuario->activo ? 'Inactivar' : 'Reactivar' }}
            </button>
        </form>
        @if (auth()->user()?->puedeEliminarUsuarios())
            <form action="{{ route('usuarios.destroy', $usuario) }}" method="post" class="inline">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="rounded-md border border-red-700 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-900 hover:bg-red-100"
                    onclick="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')"
                >
                    Eliminar
                </button>
            </form>
        @endif
    @endif
</div>
@else
<span class="text-xs font-medium text-zinc-500">Protegido</span>
@endif
