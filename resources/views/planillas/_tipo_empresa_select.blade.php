@if (auth()->user()?->puedeEditar())
    <form action="{{ route('planillas.tipo.update', $empresa) }}" method="post" class="inline">
        @csrf
        @method('PATCH')
        @if ($tipoFiltro)<input type="hidden" name="_filtro_tipo" value="{{ $tipoFiltro }}">@endif
        @if ($busqueda)<input type="hidden" name="_filtro_q" value="{{ $busqueda }}">@endif
        @if (isset($anioFiltro))<input type="hidden" name="_filtro_anio" value="{{ $anioFiltro }}">@endif
        <select name="tipo_empresa" class="rounded-md border border-zinc-300 bg-white px-2 py-1 text-xs font-semibold text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600" onchange="this.form.submit()">
            <option value="">Sin clasificar</option>
            @foreach (\App\Support\EmpresaTipo::OPCIONES as $valor => $etiqueta)
                <option value="{{ $valor }}" @selected($empresa->tipo_empresa === $valor)>{{ $etiqueta }}</option>
            @endforeach
        </select>
    </form>
@else
    @if ($empresa->tipo_empresa === 'INTERNA')
        <span class="rounded bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-800">Interna</span>
    @elseif ($empresa->tipo_empresa === 'EXTERNA')
        <span class="rounded bg-sky-100 px-2 py-0.5 text-[10px] font-bold uppercase text-sky-800">Externa</span>
    @else
        <span class="rounded bg-zinc-200 px-2 py-0.5 text-[10px] font-bold uppercase text-zinc-700">Sin clasificar</span>
    @endif
@endif
