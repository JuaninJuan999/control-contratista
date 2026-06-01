@if (auth()->user()?->puedeEditar())
<div class="inline-flex items-center gap-1" data-acciones-contratista onclick="event.stopPropagation()">
    <a
        href="{{ $editRoute }}"
        title="Editar"
        aria-label="Editar"
        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-base transition hover:bg-emerald-50"
    >✏️</a>
    <form action="{{ $toggleActivoRoute }}" method="post" class="inline">
        @csrf
        @method('PATCH')
        @if (isset($anio))
            <input type="hidden" name="anio" value="{{ $anio }}">
        @endif
        <button
            type="submit"
            title="{{ $contratista->activo ? 'Inactivar' : 'Reactivar' }}"
            aria-label="{{ $contratista->activo ? 'Inactivar' : 'Reactivar' }}"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-base transition {{ $contratista->activo ? 'hover:bg-amber-50' : 'hover:bg-zinc-100' }}"
            onclick="return confirm('{{ $contratista->activo ? '¿Inactivar este contratista?' : '¿Reactivar este contratista?' }}')"
        >{{ $contratista->activo ? '⏸️' : '▶️' }}</button>
    </form>
    @if (auth()->user()?->puedeEliminarContratistas())
    <form action="{{ $destroyRoute }}" method="post" class="inline" onsubmit="return confirm('¿Eliminar este contratista? Esta acción no se puede deshacer.');">
        @csrf
        @method('DELETE')
        @if (isset($anio))
            <input type="hidden" name="anio" value="{{ $anio }}">
        @endif
        <button
            type="submit"
            title="Eliminar"
            aria-label="Eliminar"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-base transition hover:bg-red-50"
        >🗑️</button>
    </form>
    @endif
</div>
@endif
