<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardaContratistaConDocumentos;
use App\Http\Requests\StoreContratistaInternoRequest;
use App\Http\Requests\UpdateContratistaInternoRequest;
use App\Models\ContratistaInterno;
use App\Models\Empresa;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContratistaInternoController extends Controller
{
    use GuardaContratistaConDocumentos;

    public function index(Request $request): View
    {
        $anio = (int) $request->query('anio', now()->year);
        if ($anio < 2000 || $anio > 2100) {
            $anio = now()->year;
        }

        $contratistasInternos = ContratistaInterno::query()
            ->with('empresa:id,nombre')
            ->orderByDesc('activo')
            ->orderBy('nombres_apellidos')
            ->get();

        return view('contratistas_internos.index', compact('contratistasInternos', 'anio'));
    }

    public function create(): View
    {
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('contratistas_internos.create', compact('empresas'));
    }

    public function store(StoreContratistaInternoRequest $request): RedirectResponse
    {
        $this->crearContratistaConDocumentos(
            ContratistaInterno::class,
            'internos',
            $request->validated(),
            $request
        );

        return redirect()
            ->route('contratistas-internos.index')
            ->with('success', 'Contratista interno registrado correctamente.');
    }

    public function edit(ContratistaInterno $contratistasInterno): View
    {
        $contratistaInterno = $contratistasInterno;
        $empresas = Empresa::query()->orderBy('nombre')->get(['id', 'nombre']);

        return view('contratistas_internos.edit', compact('contratistaInterno', 'empresas'));
    }

    public function update(UpdateContratistaInternoRequest $request, ContratistaInterno $contratistasInterno): RedirectResponse
    {
        $this->actualizarContratistaConDocumentos(
            $contratistasInterno,
            'internos',
            $request->validated(),
            $request
        );

        return redirect()
            ->route('contratistas-internos.index')
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
}
