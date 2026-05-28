<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardaContratistaConDocumentos;
use App\Http\Requests\StoreContratistaExternoRequest;
use App\Http\Requests\UpdateContratistaExternoRequest;
use App\Models\ContratistaExterno;
use App\Models\Empresa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContratistaExternoController extends Controller
{
    use GuardaContratistaConDocumentos;

    public function index(): View
    {
        $contratistasExternos = ContratistaExterno::query()
            ->with('empresa:id,nombre')
            ->orderByDesc('activo')
            ->orderByDesc('fecha_ultima_ir')
            ->orderBy('nombres_apellidos')
            ->get();

        return view('contratistas_externos.index', compact('contratistasExternos'));
    }

    public function create(): View
    {
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('contratistas_externos.create', compact('empresas'));
    }

    public function store(StoreContratistaExternoRequest $request): RedirectResponse
    {
        $this->crearContratistaConDocumentos(
            ContratistaExterno::class,
            'externos',
            $request->validated(),
            $request
        );

        return redirect()
            ->route('contratistas-externos.index')
            ->with('success', 'Contratista externo registrado correctamente.');
    }

    public function edit(ContratistaExterno $contratistasExterno): View
    {
        $contratistaExterno = $contratistasExterno;
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('contratistas_externos.edit', compact('contratistaExterno', 'empresas'));
    }

    public function update(UpdateContratistaExternoRequest $request, ContratistaExterno $contratistasExterno): RedirectResponse
    {
        $this->actualizarContratistaConDocumentos(
            $contratistasExterno,
            'externos',
            $request->validated(),
            $request
        );

        return redirect()
            ->route('contratistas-externos.index')
            ->with('success', 'Contratista externo actualizado correctamente.');
    }

    public function toggleActivo(ContratistaExterno $contratistasExterno): RedirectResponse
    {
        $contratistasExterno->update(['activo' => ! $contratistasExterno->activo]);

        $mensaje = $contratistasExterno->activo
            ? 'Contratista externo reactivado correctamente.'
            : 'Contratista externo inactivado correctamente.';

        return redirect()
            ->route('contratistas-externos.index')
            ->with('success', $mensaje);
    }
}
