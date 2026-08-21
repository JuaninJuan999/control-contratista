<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardaContratistaConDocumentos;
use App\Http\Controllers\Concerns\GuardaPlanillaSsInterno;
use App\Http\Requests\StoreContratistaInternoRequest;
use App\Http\Requests\UpdateContratistaInternoRequest;
use App\Models\ContratistaInterno;
use App\Models\ContratistaInternoPlanillaArchivo;
use App\Models\Empresa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContratistaInternoController extends Controller
{
    use GuardaContratistaConDocumentos;
    use GuardaPlanillaSsInterno;

    public function index(Request $request): View
    {
        $anio = (int) $request->query('anio', now()->year);
        if ($anio < 2000 || $anio > 2100) {
            $anio = now()->year;
        }

        $contratistasInternos = ContratistaInterno::query()
            ->with([
                'empresa:id,nombre,nit,limite,planilla',
                'empresa.planillaArchivos',
                'planillaArchivos',
            ])
            ->orderByDesc('activo')
            ->orderBy('nombres_apellidos')
            ->get();

        $empresasFiltro = Empresa::query()
            ->whereHas('contratistasInternos')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('contratistas_internos.index', compact('contratistasInternos', 'anio', 'empresasFiltro'));
    }

    public function create(): View
    {
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre', 'planilla']);

        return view('contratistas_internos.create', compact('empresas'));
    }

    public function store(StoreContratistaInternoRequest $request): RedirectResponse
    {
        $datos = collect($request->validated())->except(['planilla_ss_archivo'])->all();

        /** @var ContratistaInterno $contratista */
        $contratista = $this->crearContratistaConDocumentos(
            ContratistaInterno::class,
            'internos',
            $datos,
            $request
        );

        $this->guardarPlanillaSsIndependiente($contratista->fresh(['empresa']), $request);

        return redirect()
            ->route('contratistas-internos.index')
            ->with('success', 'Contratista interno registrado correctamente.');
    }

    public function edit(ContratistaInterno $contratistasInterno): View
    {
        $contratistaInterno = $contratistasInterno->load(['planillaArchivos', 'empresa']);
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre', 'planilla']);

        return view('contratistas_internos.edit', compact('contratistaInterno', 'empresas'));
    }

    public function update(UpdateContratistaInternoRequest $request, ContratistaInterno $contratistasInterno): RedirectResponse
    {
        $datos = collect($request->validated())->except(['planilla_ss_archivo'])->all();

        $this->actualizarContratistaConDocumentos(
            $contratistasInterno,
            'internos',
            $datos,
            $request
        );

        $this->guardarPlanillaSsIndependiente($contratistasInterno->fresh(['empresa', 'planillaArchivos']), $request);

        return redirect()
            ->route('contratistas-internos.index', [
                'anio' => (int) $request->input('anio', now()->year),
                'abrir' => 'interno-'.$contratistasInterno->id,
            ])
            ->with('success', 'Contratista interno actualizado correctamente.');
    }

    public function toggleActivo(Request $request, ContratistaInterno $contratistasInterno): RedirectResponse
    {
        $contratistasInterno->update(['activo' => ! $contratistasInterno->activo]);

        $mensaje = $contratistasInterno->activo
            ? 'Contratista interno reactivado correctamente.'
            : 'Contratista interno inactivado correctamente.';

        $anio = (int) $request->input('anio', now()->year);

        return redirect()
            ->route('contratistas-internos.index', ['anio' => $anio])
            ->with('success', $mensaje);
    }

    public function toggleMes(Request $request, ContratistaInterno $contratistaInterno): RedirectResponse
    {
        $anio = (int) $request->input('anio', now()->year);
        $mes = (int) $request->input('mes');

        if ($anio < 2000 || $anio > 2100 || $mes < 1 || $mes > 12) {
            return redirect()
                ->route('contratistas-internos.index', ['anio' => $anio])
                ->with('error', 'Mes o año no válido.');
        }

        $contratistaInterno->load(['empresa.planillaArchivos', 'planillaArchivos']);
        $ui = $contratistaInterno->controlMesSsUi($anio, $mes, false);
        if (! $ui['editable']) {
            return redirect()
                ->route('contratistas-internos.index', ['anio' => $anio])
                ->with('error', 'La vigencia SS del mes activo se calcula automáticamente desde la fecha límite.');
        }

        $contratistaInterno->toggleMes($anio, $mes);

        return redirect()
            ->route('contratistas-internos.index', ['anio' => $anio])
            ->with('success', 'Registro mensual actualizado.');
    }

    public function destroy(Request $request, ContratistaInterno $contratistasInterno): RedirectResponse
    {
        $anio = (int) $request->input('anio', $request->query('anio', now()->year));

        if (! Auth::user()?->puedeEliminarContratistas()) {
            return redirect()
                ->route('contratistas-internos.index', ['anio' => $anio])
                ->with('error', 'No tiene permiso para eliminar contratistas.');
        }

        $nombre = $contratistasInterno->nombres_apellidos;

        $this->eliminarContratistaConDocumentos($contratistasInterno, 'internos');

        return redirect()
            ->route('contratistas-internos.index', ['anio' => $anio])
            ->with('success', "Contratista interno «{$nombre}» eliminado correctamente.");
    }

    public function descargarPlanilla(ContratistaInternoPlanillaArchivo $archivo): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($archivo->archivo), 404);

        $nombre = $archivo->nombre_original ?: basename($archivo->archivo);

        return Storage::disk('public')->download($archivo->archivo, $nombre);
    }
}
