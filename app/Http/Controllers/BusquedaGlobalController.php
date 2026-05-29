<?php

namespace App\Http\Controllers;

use App\Services\BusquedaGlobalIndice;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusquedaGlobalController extends Controller
{
    private const ETIQUETAS = [
        'empresa' => 'Empresa',
        'contratista_externo' => 'Externo',
        'contratista_interno' => 'Interno',
        'vehiculo' => 'Vehículo',
    ];

    public function __construct(
        private readonly BusquedaGlobalIndice $indice
    ) {}

    public function index(Request $request): View
    {
        $termino = trim((string) $request->query('q', ''));
        $resultados = array_map(function (array $item): array {
            $item['tipo_etiqueta'] = self::ETIQUETAS[$item['tipo']] ?? $item['tipo'];

            return $item;
        }, $this->indice->filtrar($termino, 100));

        return view('busqueda.index', [
            'termino' => $termino,
            'resultados' => $resultados,
        ]);
    }

    public function sugerencias(Request $request): JsonResponse
    {
        $termino = trim((string) $request->query('q', ''));

        return response()->json([
            'results' => $this->indice->filtrar($termino),
        ]);
    }
}
