<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Models\Empresa;
use App\Models\Vehiculo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class VehiculoController extends Controller
{
    public function index(): View
    {
        $vehiculos = Vehiculo::query()
            ->with('empresa:id,nombre')
            ->orderBy('placa')
            ->get();

        return view('vehiculos.index', compact('vehiculos'));
    }

    public function create(): View
    {
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('vehiculos.create', compact('empresas'));
    }

    public function store(StoreVehiculoRequest $request): RedirectResponse
    {
        Vehiculo::query()->create($request->validated());

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo registrado correctamente.');
    }

    public function edit(Vehiculo $vehiculo): View
    {
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('vehiculos.edit', compact('vehiculo', 'empresas'));
    }

    public function update(UpdateVehiculoRequest $request, Vehiculo $vehiculo): RedirectResponse
    {
        $vehiculo->update($request->validated());

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }
}
