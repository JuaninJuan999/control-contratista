<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GuardaContratistaConDocumentos;
use App\Http\Requests\StoreContratistaExternoRequest;
use App\Http\Requests\UpdateContratistaExternoRequest;
use App\Models\ContratistaExterno;
use App\Models\Empresa;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContratistaExternoController extends Controller
{
    use GuardaContratistaConDocumentos;

    public function index(Request $request): View
    {
        $anio = (int) $request->query('anio', now()->year);
        if ($anio < 2000 || $anio > 2100) {
            $anio = now()->year;
        }

        $contratistasExternos = ContratistaExterno::query()
            ->with('empresa:id,nombre')
            ->orderByDesc('activo')
            ->orderByDesc('fecha_ultima_ir')
            ->orderBy('nombres_apellidos')
            ->get();

        return view('contratistas_externos.index', compact('contratistasExternos', 'anio'));
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

    public function toggleActivo(Request $request, ContratistaExterno $contratistasExterno): RedirectResponse
    {
        $contratistasExterno->update(['activo' => ! $contratistasExterno->activo]);

        $mensaje = $contratistasExterno->activo
            ? 'Contratista externo reactivado correctamente.'
            : 'Contratista externo inactivado correctamente.';

        $anio = (int) $request->input('anio', now()->year);

        return redirect()
            ->route('contratistas-externos.index', ['anio' => $anio])
            ->with('success', $mensaje);
    }

    public function toggleMes(Request $request, ContratistaExterno $contratistaExterno): RedirectResponse
    {
        $anio = (int) $request->input('anio', now()->year);
        $mes = (int) $request->input('mes');

        if ($anio < 2000 || $anio > 2100 || $mes < 1 || $mes > 12) {
            return redirect()
                ->route('contratistas-externos.index', ['anio' => $anio])
                ->with('error', 'Mes o año no válido.');
        }

        $contratistaExterno->toggleMes($anio, $mes);

        return redirect()
            ->route('contratistas-externos.index', ['anio' => $anio])
            ->with('success', 'Registro mensual actualizado.');
    }
}
