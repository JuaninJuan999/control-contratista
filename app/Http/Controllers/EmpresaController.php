<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardaContratistaConDocumentos;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use App\Models\ContratistaExterno;
use App\Models\ContratistaInterno;
use App\Models\Empresa;
use App\Models\Vehiculo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class EmpresaController extends Controller
{
    use GuardaContratistaConDocumentos;

    public function index(): View
    {
        $empresas = Empresa::query()
            ->with([
                'contratistasExternos' => fn ($query) => $query->orderBy('nombres_apellidos'),
                'contratistasInternos' => fn ($query) => $query->orderBy('nombres_apellidos'),
                'vehiculos' => fn ($query) => $query->orderBy('placa'),
            ])
            ->withCount(['contratistasExternos', 'contratistasInternos', 'vehiculos'])
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('empresas.index', compact('empresas'));
    }

    public function create(): View
    {
        return view('empresas.create');
    }

    public function store(StoreEmpresaRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $personas = $validated['personas'] ?? [];
        $vehiculos = $validated['vehiculos'] ?? [];
        unset($validated['personas'], $validated['vehiculos']);

        $empresa = DB::transaction(function () use ($validated, $personas, $vehiculos, $request) {
            $empresa = Empresa::query()->create($validated);

            foreach ($personas as $index => $persona) {
                $tipo = ($persona['tipo_contratista'] ?? 'externo') === 'interno' ? 'interno' : 'externo';
                unset($persona['tipo_contratista']);
                $persona['empresa_id'] = $empresa->id;

                if ($tipo === 'interno') {
                    $this->crearContratistaConDocumentos(
                        ContratistaInterno::class,
                        'internos',
                        $persona,
                        $request,
                        "personas.{$index}"
                    );

                    continue;
                }

                $this->crearContratistaConDocumentos(
                    ContratistaExterno::class,
                    'externos',
                    $persona,
                    $request,
                    "personas.{$index}"
                );
            }

            foreach ($vehiculos as $vehiculo) {
                Vehiculo::query()->create([
                    ...$vehiculo,
                    'empresa_id' => $empresa->id,
                ]);
            }

            return $empresa;
        });

        $mensaje = 'Empresa creada correctamente.';
        $externos = collect($personas)->where('tipo_contratista', 'externo')->count();
        $internos = collect($personas)->where('tipo_contratista', 'interno')->count();

        if ($externos > 0) {
            $mensaje .= ' Se registraron '.$externos.' contratista(s) externo(s).';
        }
        if ($internos > 0) {
            $mensaje .= ' Se registraron '.$internos.' contratista(s) interno(s).';
        }
        if (count($vehiculos) > 0) {
            $mensaje .= ' Se registraron '.count($vehiculos).' vehículo(s).';
        }

        return redirect()
            ->route('empresas.index')
            ->with('success', $mensaje);
    }

    public function edit(Empresa $empresa): View
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(UpdateEmpresaRequest $request, Empresa $empresa): RedirectResponse
    {
        $empresa->update($request->validated());

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa actualizada correctamente.');
    }

    public function destroy(Empresa $empresa): RedirectResponse
    {
        if ($empresa->contratistasExternos()->exists() || $empresa->contratistasInternos()->exists() || $empresa->vehiculos()->exists()) {
            return redirect()
                ->route('empresas.index')
                ->with('error', 'No se puede eliminar: hay contratistas o vehículos asociados a esta empresa.');
        }

        $empresa->delete();

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa eliminada.');
    }
}
