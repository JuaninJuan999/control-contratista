@if (auth()->user()?->puedeEditar())
<div class="flex flex-wrap items-center gap-2" data-acciones-contratista onclick="event.stopPropagation()">
    <a
        href="{{ $editRoute }}"
        class="rounded-md border border-emerald-700 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-900 hover:bg-emerald-100"
    >
        Editar
    </a>
    <form action="{{ $toggleActivoRoute }}" method="post" class="inline">
        @csrf
        @method('PATCH')
        @if (isset($anio))
            <input type="hidden" name="anio" value="{{ $anio }}">
        @endif
        <button
            type="submit"
            class="rounded-md border px-2.5 py-1 text-xs font-semibold {{ $contratista->activo ? 'border-red-300 bg-red-50 text-red-800 hover:bg-red-100' : 'border-zinc-300 bg-zinc-50 text-zinc-800 hover:bg-zinc-100' }}"
            onclick="return confirm('{{ $contratista->activo ? '¿Inactivar este contratista?' : '¿Reactivar este contratista?' }}')"
        >
            {{ $contratista->activo ? 'Inactivar' : 'Reactivar' }}
        </button>
    </form>
</div>
@endif
