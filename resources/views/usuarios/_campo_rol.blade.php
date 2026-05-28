<div>
    <label for="rol" class="block text-xs font-semibold text-zinc-950 md:text-[13px]">Rol</label>
    <select
        name="rol"
        id="rol"
        required
        class="mt-0.5 w-full rounded-md border border-zinc-300 bg-white px-2.5 py-1.5 text-sm text-zinc-900 shadow-sm focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600"
    >
        @foreach (\App\Support\UserRol::asignablesPara(auth()->user()) as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('rol', $rolDefault ?? \App\Support\UserRol::OPERATIVO) === $valor)>{{ $etiqueta }}</option>
        @endforeach
    </select>
</div>
