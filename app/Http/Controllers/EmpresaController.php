<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardaContratistaConDocumentos;
use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Requests\UpdateEmpresaRequest;
use App\Models\ContratistaExterno;
use App\Models\ContratistaInterno;
use App\Models\Empresa;
use App\Models\Vehiculo;
use App\Services\VehiculoDocumentoStorage;
use App\Support\EmpresaTipo;
use App\Support\TerminoBusqueda;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmpresaController extends Controller
{
    use GuardaContratistaConDocumentos;

    public function index(Request $request): View
    {
        // Se acepta "nombre" por compatibilidad: es como se llamaba el campo antes,
        // y las pestañas o enlaces guardados lo siguen enviando.
        $buscar = trim((string) ($request->query('buscar') ?? $request->query('nombre') ?? ''));
        $nit = trim((string) $request->query('nit', ''));
        $estadoSs = (string) $request->query('estado_ss', '');
        $tipoEmpresa = (string) $request->query('tipo_empresa', '');
        $planilla = (string) $request->query('planilla', '');

        $estadosSsValidos = ['VIGENTE', 'PRÓXIMA A VENCER', 'VENCIDA', 'SIN FECHA'];
        if (! in_array($estadoSs, $estadosSsValidos, true)) {
            $estadoSs = '';
        }

        if ($tipoEmpresa !== 'SIN_CLASIFICAR' && ! in_array($tipoEmpresa, EmpresaTipo::valores(), true)) {
            $tipoEmpresa = '';
        }

        $empresas = Empresa::query()
            ->with([
                'contratistasExternos' => fn ($q) => $q->orderBy('nombres_apellidos'),
                'contratistasInternos' => fn ($q) => $q->with('planillaArchivos')->orderBy('nombres_apellidos'),
                'vehiculos' => fn ($q) => $q->orderBy('placa'),
                'planillaArchivos' => fn ($q) => $q->orderByDesc('vigencia_hasta')->orderByDesc('periodo_anio')->orderByDesc('periodo_mes'),
            ])
            ->withCount(['contratistasExternos', 'contratistasInternos', 'vehiculos', 'planillaArchivos'])
            ->when($buscar !== '', fn ($q) => $q->buscarTexto($buscar))
            ->when($nit !== '', function ($q) use ($nit) {
                $patron = TerminoBusqueda::patron($nit);
                $digitos = TerminoBusqueda::digitos($nit);

                $q->where(function ($sub) use ($patron, $digitos) {
                    $sub->where('nit', 'ilike', $patron);

                    if ($digitos !== '') {
                        $sub->orWhere('nit', 'ilike', '%'.$digitos.'%');
                    }
                });
            })
            ->when($tipoEmpresa === 'SIN_CLASIFICAR', fn ($q) => $q->whereNull('tipo_empresa'))
            ->when(in_array($tipoEmpresa, EmpresaTipo::valores(), true), fn ($q) => $q->where('tipo_empresa', $tipoEmpresa))
            ->when($planilla !== '', function ($q) use ($planilla) {
                $q->where('planilla', $planilla);
            })
            ->when($estadoSs !== '', fn ($q) => $q->filtrarEstadoSsListado($estadoSs))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        $planillas = Empresa::query()
            ->where('tipo_empresa', EmpresaTipo::INTERNA)
            ->whereNotNull('planilla')
            ->where('planilla', '!=', '')
            ->distinct()
            ->orderBy('planilla')
            ->pluck('planilla');

        $hayFiltros = $buscar !== ''
            || $nit !== ''
            || $estadoSs !== ''
            || $tipoEmpresa !== ''
            || $planilla !== '';

        return view('empresas.index', compact(
            'empresas',
            'planillas',
            'buscar',
            'nit',
            'estadoSs',
            'tipoEmpresa',
            'planilla',
            'hayFiltros',
        ));
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

            foreach ($vehiculos as $index => $vehiculo) {
                foreach (array_keys(Vehiculo::DOCUMENTOS) as $campoDoc) {
                    unset($vehiculo[$campoDoc]);
                }

                $nuevoVehiculo = Vehiculo::query()->create([
                    ...$vehiculo,
                    'empresa_id' => $empresa->id,
                ]);

                $rutas = [];
                foreach (array_keys(Vehiculo::DOCUMENTOS) as $campoDoc) {
                    $archivo = $request->file("vehiculos.{$index}.{$campoDoc}");
                    if ($archivo !== null) {
                        $rutas[$campoDoc] = VehiculoDocumentoStorage::guardar($nuevoVehiculo->id, $archivo);
                    }
                }

                if (! $nuevoVehiculo->inspeccion_sanitaria) {
                    unset($rutas['inspeccion_sanitaria_archivo']);
                }

                if ($rutas !== []) {
                    $nuevoVehiculo->update($rutas);
                }
            }

            $empresa->load(['contratistasExternos', 'contratistasInternos']);
            $this->sincronizarControlMensual($empresa, $this->clavesTodosContratistas($empresa));

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
        $empresa->load([
            'contratistasExternos' => fn ($query) => $query->orderBy('nombres_apellidos'),
            'contratistasInternos' => fn ($query) => $query->with('planillaArchivos')->orderBy('nombres_apellidos'),
        ]);

        return view('empresas.edit', compact('empresa'));
    }

    public function update(UpdateEmpresaRequest $request, Empresa $empresa): RedirectResponse
    {
        $datos = $request->validated();
        unset($datos['personas_vigentes']);

        $empresa->update($datos);

        $this->sincronizarControlMensual($empresa, $request->input('personas_vigentes', []));

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Empresa actualizada correctamente.');
    }

    /**
     * Marca el mes/año de la fecha límite como OK para los contratistas
     * seleccionados como vigentes, y como rechazado (rojo) para el resto.
     *
     * @param  array<int, string>  $seleccionados  Valores tipo "externo-5" / "interno-3".
     */
    private function sincronizarControlMensual(Empresa $empresa, array $seleccionados): void
    {
        if ($empresa->limite === null) {
            return;
        }

        $anio = (int) $empresa->limite->year;
        $mes = (int) $empresa->limite->month;
        $vigentes = collect($seleccionados);

        $empresa->loadMissing(['contratistasExternos', 'contratistasInternos']);

        foreach ($empresa->contratistasExternos as $contratista) {
            $estado = $vigentes->contains('externo-'.$contratista->id) ? 'ok' : 'rechazado';
            $contratista->marcarMes($anio, $mes, $estado);
            $contratista->save();
        }

        foreach ($empresa->contratistasInternos as $contratista) {
            if ($contratista->esPlanillaIndependiente()) {
                continue;
            }

            $estado = $vigentes->contains('interno-'.$contratista->id) ? 'ok' : 'rechazado';
            $contratista->marcarMes($anio, $mes, $estado);
            $contratista->save();
        }
    }

    /**
     * @return list<string>
     */
    private function clavesTodosContratistas(Empresa $empresa): array
    {
        $empresa->loadMissing(['contratistasExternos', 'contratistasInternos']);

        return collect()
            ->merge(
                $empresa->contratistasExternos->map(fn (ContratistaExterno $contratista) => 'externo-'.$contratista->id)
            )
            ->merge(
                $empresa->contratistasInternos->map(fn (ContratistaInterno $contratista) => 'interno-'.$contratista->id)
            )
            ->values()
            ->all();
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
