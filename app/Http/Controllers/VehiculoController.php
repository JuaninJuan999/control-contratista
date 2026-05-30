<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehiculoRequest;
use App\Http\Requests\UpdateVehiculoRequest;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Services\VehiculoDocumentoStorage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index(): View
    {
        $vehiculos = Vehiculo::query()
            ->with('empresa:id,nombre')
            ->orderBy('placa')
            ->get();

        $empresas = Empresa::query()
            ->whereHas('vehiculos')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('vehiculos.index', compact('vehiculos', 'empresas'));
    }

    public function create(): View
    {
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('vehiculos.create', compact('empresas'));
    }

    public function store(StoreVehiculoRequest $request): RedirectResponse
    {
        $campos = array_keys(Vehiculo::DOCUMENTOS);
        $datos = collect($request->validated())->except($campos)->all();

        if (! ($datos['inspeccion_sanitaria'] ?? false)) {
            $datos['inspeccion_sanitaria_fin'] = null;
        }

        $vehiculo = Vehiculo::query()->create($datos);

        $rutas = $this->guardarDocumentos($request, $vehiculo);
        if (! $vehiculo->inspeccion_sanitaria) {
            unset($rutas['inspeccion_sanitaria_archivo']);
        }
        if ($rutas !== []) {
            $vehiculo->update($rutas);
        }

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
        $campos = array_keys(Vehiculo::DOCUMENTOS);
        $datos = collect($request->validated())->except($campos)->all();

        $datos = array_merge($datos, $this->guardarDocumentos($request, $vehiculo));

        if (! ($datos['inspeccion_sanitaria'] ?? false)) {
            $datos['inspeccion_sanitaria_fin'] = null;
            $datos['inspeccion_sanitaria_archivo'] = null;
        }

        $vehiculo->update($datos);

        return redirect()
            ->route('vehiculos.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    /**
     * Guarda los archivos enviados y devuelve el mapa campo => ruta.
     *
     * @return array<string, string>
     */
    private function guardarDocumentos(Request $request, Vehiculo $vehiculo): array
    {
        $rutas = [];

        foreach (array_keys(Vehiculo::DOCUMENTOS) as $campo) {
            if ($request->hasFile($campo)) {
                $rutas[$campo] = VehiculoDocumentoStorage::guardar($vehiculo->id, $request->file($campo));
            }
        }

        return $rutas;
    }
}
