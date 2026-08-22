<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Services\PlanillaContratistas\GeneradorPlantillaExcel;
use App\Services\PlanillaContratistas\ImportadorPlanillaContratistas;
use App\Services\PlanillaContratistas\LectorPlanillaExcel;
use App\Services\PlanillaContratistas\PlanillaFilaContratista;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanillaContratistaController extends Controller
{
    public function plantilla()
    {
        $this->autorizarImportacion();

        return GeneradorPlantillaExcel::descargar();
    }

    public function create(Empresa $empresa): View
    {
        $this->autorizarImportacion();
        $this->autorizarEmpresaPlanillaDependiente($empresa);

        return view('empresas.planilla_importar', compact('empresa'));
    }

    public function preview(Request $request, Empresa $empresa): View|RedirectResponse
    {
        $this->autorizarImportacion();
        $this->autorizarEmpresaPlanillaDependiente($empresa);

        $request->validate([
            'planilla' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'planilla.required' => 'Debe seleccionar un archivo Excel.',
            'planilla.mimes' => 'El archivo debe ser Excel (.xlsx o .xls).',
        ]);

        try {
            $filas = app(LectorPlanillaExcel::class)->leer($request->file('planilla'));
            $analisis = app(ImportadorPlanillaContratistas::class)->analizar($empresa, $filas);
        } catch (\InvalidArgumentException $exception) {
            return redirect()
                ->route('empresas.planilla.create', $empresa)
                ->with('error', $exception->getMessage());
        }

        $token = Str::random(40);
        session([
            'planilla_import_confirm' => [
                'empresa_id' => $empresa->id,
                'token' => $token,
                'filas' => $this->serializarFilas($filas),
            ],
        ]);

        return view('empresas.planilla_preview', compact('empresa', 'analisis', 'token'));
    }

    public function importar(Request $request, Empresa $empresa): RedirectResponse
    {
        $this->autorizarImportacion();
        $this->autorizarEmpresaPlanillaDependiente($empresa);

        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $sesion = session('planilla_import_confirm');

        if (
            ! is_array($sesion)
            || ($sesion['empresa_id'] ?? null) !== $empresa->id
            || ! hash_equals((string) ($sesion['token'] ?? ''), $request->input('token'))
        ) {
            return redirect()
                ->route('empresas.planilla.create', $empresa)
                ->with('error', 'La vista previa expiró. Vuelva a cargar el archivo Excel.');
        }

        $filas = $this->deserializarFilas($sesion['filas'] ?? []);
        $analisis = app(ImportadorPlanillaContratistas::class)->aplicar($empresa, $filas);

        session()->forget('planilla_import_confirm');

        if ($analisis->tieneErroresBloqueantes()) {
            $mensaje = collect($analisis->errores)->pluck('mensaje')->first() ?? 'No se pudo importar la planilla.';

            return redirect()
                ->route('empresas.planilla.create', $empresa)
                ->with('error', $mensaje);
        }

        $partes = [];
        $partes[] = count($analisis->actualizados).' contratista(s) actualizado(s)';
        $partes[] = count($analisis->inactivados).' contratista(s) inactivado(s)';
        $partes[] = count($analisis->nuevos).' contratista(s) nuevo(s) registrado(s)';

        $mensaje = 'Importación completada: '.implode(', ', $partes).'.';

        return redirect()
            ->route('empresas.edit', $empresa)
            ->with('success', $mensaje);
    }

    private function autorizarImportacion(): void
    {
        abort_unless(auth()->user()?->puedeImportarPlanilla(), 403);
    }

    private function autorizarEmpresaPlanillaDependiente(Empresa $empresa): void
    {
        abort_unless($empresa->llevaPlanillaSs(), 404);
    }

    /**
     * @param  list<PlanillaFilaContratista>  $filas
     * @return list<array<string, mixed>>
     */
    private function serializarFilas(array $filas): array
    {
        return array_map(fn (PlanillaFilaContratista $fila) => [
            'numero_fila' => $fila->numeroFila,
            'numero_documento' => $fila->numeroDocumento,
            'tipo_documento' => $fila->tipoDocumento,
            'nombres_apellidos' => $fila->nombresApellidos,
            'tipo_contratista' => $fila->tipoContratista,
            'arl' => $fila->arl,
        ], $filas);
    }

    /**
     * @param  mixed  $datos
     * @return list<PlanillaFilaContratista>
     */
    private function deserializarFilas(mixed $datos): array
    {
        if (! is_array($datos)) {
            return [];
        }

        $filas = [];

        foreach ($datos as $fila) {
            if (! is_array($fila)) {
                continue;
            }

            $filas[] = new PlanillaFilaContratista(
                numeroFila: (int) ($fila['numero_fila'] ?? 0),
                numeroDocumento: (string) ($fila['numero_documento'] ?? ''),
                tipoDocumento: (string) ($fila['tipo_documento'] ?? 'CC'),
                nombresApellidos: isset($fila['nombres_apellidos']) ? (string) $fila['nombres_apellidos'] : null,
                tipoContratista: (string) ($fila['tipo_contratista'] ?? 'externo'),
                arl: isset($fila['arl']) ? (string) $fila['arl'] : null,
            );
        }

        return $filas;
    }
}
