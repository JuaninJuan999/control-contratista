<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanillaArchivoRequest;
use App\Http\Requests\UpdateEmpresaTipoRequest;
use App\Models\Empresa;
use App\Models\EmpresaPlanillaArchivo;
use App\Services\PlanillaEmpresaStorage;
use App\Support\EmpresaTipo;
use App\Support\PlanillaTipo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlanillaController extends Controller
{
    public function index(Request $request): View
    {
        $tipoFiltro = $request->query('tipo_empresa');
        if ($tipoFiltro !== null && $tipoFiltro !== 'SIN_CLASIFICAR' && ! in_array($tipoFiltro, EmpresaTipo::valores(), true)) {
            $tipoFiltro = null;
        }

        $busqueda = trim((string) $request->query('q', ''));
        $anioFiltro = (int) $request->query('anio', now()->year);
        if ($anioFiltro < 2000 || $anioFiltro > 2100) {
            $anioFiltro = now()->year;
        }

        $empresas = Empresa::query()
            ->where('tipo_empresa', EmpresaTipo::INTERNA)
            ->where('planilla', PlanillaTipo::DEPENDIENTE)
            ->with(['planillaArchivos' => fn ($q) => $q->orderByDesc('vigencia_hasta')->orderByDesc('periodo_anio')->orderByDesc('periodo_mes')])
            ->withCount([
                'planillaArchivos',
                'planillaArchivos as planilla_archivos_anio_count' => fn ($q) => $q->where('periodo_anio', $anioFiltro),
            ])
            ->when($tipoFiltro === 'SIN_CLASIFICAR', fn ($q) => $q->whereNull('tipo_empresa'))
            ->when(in_array($tipoFiltro, EmpresaTipo::valores(), true), fn ($q) => $q->where('tipo_empresa', $tipoFiltro))
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $q->where(function ($query) use ($busqueda) {
                    $query->where('nombre', 'ilike', '%'.$busqueda.'%')
                        ->orWhere('nit', 'ilike', '%'.$busqueda.'%');
                });
            })
            ->orderBy('nombre')
            ->get();

        return view('planillas.index', compact('empresas', 'tipoFiltro', 'busqueda', 'anioFiltro'));
    }

    public function storeArchivo(StorePlanillaArchivoRequest $request, Empresa $empresa): RedirectResponse
    {
        if (! $empresa->llevaPlanillaSs()) {
            return redirect()
                ->route('planillas.index', $this->filtrosRedirect($request))
                ->with('error', 'Esta empresa no lleva planilla de seguridad social a nivel empresa. En empresas independientes, cada contratista gestiona su propia planilla.');
        }

        if ($empresa->limite === null) {
            return redirect()
                ->route('planillas.index', $this->filtrosRedirect($request))
                ->with('error', 'La empresa debe tener una fecha límite definida para registrar planillas mensuales.');
        }

        if ($empresa->estado_limite === 'VENCIDA') {
            return redirect()
                ->route('planillas.index', array_merge($this->filtrosRedirect($request), ['abrir' => $empresa->id]))
                ->with('error', 'La fecha límite venció el '.$empresa->limite->format('d/m/Y').'. Renueve la vigencia en Empresas y luego adjunte la nueva planilla de seguridad social.');
        }

        $vigenciaHasta = $empresa->limite->copy()->startOfDay();
        $anio = (int) $vigenciaHasta->year;
        $mes = (int) $vigenciaHasta->month;

        $archivo = $request->file('archivo');
        $ruta = PlanillaEmpresaStorage::guardar($empresa->id, $archivo);

        $datosArchivo = [
            'periodo_anio' => $anio,
            'periodo_mes' => $mes,
            'archivo' => $ruta,
            'nombre_original' => $archivo->getClientOriginalName(),
            'mime' => $archivo->getClientMimeType(),
            'tamano_bytes' => $archivo->getSize(),
            'user_id' => $request->user()?->id,
            'vigencia_hasta' => $vigenciaHasta,
        ];

        $registro = EmpresaPlanillaArchivo::query()
            ->where('empresa_id', $empresa->id)
            ->whereDate('vigencia_hasta', $vigenciaHasta)
            ->first();

        if ($registro !== null) {
            PlanillaEmpresaStorage::eliminar($registro->archivo);
            $registro->update($datosArchivo);

            $mensaje = "Planilla de «{$empresa->nombre}» actualizada para vigencia hasta ".$vigenciaHasta->format('d/m/Y').'.';
        } else {
            EmpresaPlanillaArchivo::query()->create([
                'empresa_id' => $empresa->id,
                ...$datosArchivo,
            ]);

            $mensaje = "Planilla de «{$empresa->nombre}» registrada para vigencia hasta ".$vigenciaHasta->format('d/m/Y').'.';
        }

        $this->marcarMesVigenciaDependientes($empresa, $anio, $mes);

        return redirect()
            ->route('planillas.index', array_merge($this->filtrosRedirect($request), ['abrir' => $empresa->id]))
            ->with('success', $mensaje);
    }

    public function updateTipo(UpdateEmpresaTipoRequest $request, Empresa $empresa): RedirectResponse
    {
        $empresa->update([
            'tipo_empresa' => $request->validated('tipo_empresa'),
        ]);

        return redirect()
            ->route('planillas.index', $this->filtrosRedirect($request))
            ->with('success', "Clasificación de «{$empresa->nombre}» actualizada.");
    }

    public function destroyArchivo(Request $request, EmpresaPlanillaArchivo $archivo): RedirectResponse
    {
        abort_unless($request->user()?->puedeEditar(), 403);

        $empresa = $archivo->empresa;
        $nombreEmpresa = $empresa?->nombre ?? 'Empresa';
        $periodo = $archivo->periodoEtiqueta();

        PlanillaEmpresaStorage::eliminar($archivo->archivo);
        $archivo->delete();

        $redirect = array_merge($this->filtrosRedirect($request), $empresa ? ['abrir' => $empresa->id] : []);

        return redirect()
            ->route('planillas.index', $redirect)
            ->with('success', "Registro de {$periodo} de «{$nombreEmpresa}» eliminado.");
    }

    public function descargar(EmpresaPlanillaArchivo $archivo): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($archivo->archivo), 404);

        $nombre = $archivo->nombre_original ?: basename($archivo->archivo);

        return Storage::disk('public')->download($archivo->archivo, $nombre);
    }

    /** @return array<string, string|int> */
    private function filtrosRedirect(Request $request): array
    {
        return array_filter([
            'tipo_empresa' => $request->input('_filtro_tipo'),
            'q' => $request->input('_filtro_q'),
            'anio' => $request->input('_filtro_anio'),
        ], fn ($valor) => $valor !== null && $valor !== '');
    }

    private function marcarMesVigenciaDependientes(Empresa $empresa, int $anio, int $mes): void
    {
        $empresa->loadMissing(['contratistasInternos']);

        foreach ($empresa->contratistasInternos as $contratista) {
            if ($contratista->tipo_planilla === PlanillaTipo::INDEPENDIENTE) {
                continue;
            }

            $contratista->marcarMes($anio, $mes, 'ok');
            $contratista->save();
        }
    }
}
