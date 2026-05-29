<?php

namespace App\Http\Controllers;

use App\Models\ContratistaExterno;
use App\Models\ContratistaInterno;
use App\Models\Empresa;
use App\Models\Vehiculo;
use Carbon\CarbonInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private const DIAS_PROXIMO = 10;

    /** @var array<string, string> */
    public const TIPOS = [
        'empresas' => 'Empresas',
        'ind_rnd' => 'Ind/Rnd',
        'licencia' => 'Licencia de conducción',
        'manipulador' => 'Manipulador de Alimentos',
        'soat' => 'SOAT',
        'tecnomecanica' => 'Tecnomecánica',
        'inspeccion' => 'Inspección Sanitaria',
    ];

    public function index(): View
    {
        $items = $this->recopilarItems();

        $vencidas = $items
            ->filter(fn (array $item) => $item['dias'] < 0)
            ->sortBy('dias')
            ->groupBy('tipo');

        $proximas = $items
            ->filter(fn (array $item) => $item['dias'] >= 0 && $item['dias'] <= self::DIAS_PROXIMO)
            ->sortBy('dias')
            ->groupBy('tipo');

        $tipos = self::TIPOS;
        $estadisticas = $this->estadisticasPorTipo($items);

        $totales = [
            'empresas' => Empresa::query()->count(),
            'contratistas_externos' => ContratistaExterno::query()->count(),
            'contratistas_internos' => ContratistaInterno::query()->count(),
            'vehiculos' => Vehiculo::query()->count(),
        ];

        return view('dashboard', compact('vencidas', 'proximas', 'tipos', 'estadisticas', 'totales'));
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<string, array<string, int|string>>
     */
    private function estadisticasPorTipo(Collection $items): array
    {
        $estadisticas = [];

        foreach (self::TIPOS as $tipoKey => $tipoLabel) {
            $delTipo = $items->where('tipo', $tipoKey);

            $vencido = $delTipo->filter(fn (array $i) => $i['dias'] < 0)->count();
            $proximo = $delTipo->filter(fn (array $i) => $i['dias'] >= 0 && $i['dias'] <= self::DIAS_PROXIMO)->count();
            $vigente = $delTipo->filter(fn (array $i) => $i['dias'] > self::DIAS_PROXIMO)->count();

            $estadisticas[$tipoKey] = [
                'label' => $tipoLabel,
                'vencido' => $vencido,
                'proximo' => $proximo,
                'vigente' => $vigente,
                'total' => $vencido + $proximo + $vigente,
            ];
        }

        return $estadisticas;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function recopilarItems(): Collection
    {
        $items = collect();

        Empresa::query()
            ->whereNotNull('limite')
            ->get()
            ->each(function (Empresa $empresa) use ($items): void {
                $items->push($this->item(
                    'empresas',
                    $empresa->nombre,
                    'Fecha límite',
                    $empresa->limite,
                    route('empresas.index'),
                    route('empresas.edit', $empresa),
                ));
            });

        $this->agregarIndRnd($items, ContratistaExterno::class, route('contratistas-externos.index'), 'externo');
        $this->agregarIndRnd($items, ContratistaInterno::class, route('contratistas-internos.index'), 'interno');

        $this->agregarDocumentosContratistas($items, ContratistaExterno::class, route('contratistas-externos.index'));
        $this->agregarDocumentosContratistas($items, ContratistaInterno::class, route('contratistas-internos.index'));

        Vehiculo::query()
            ->with('empresa:id,nombre')
            ->get()
            ->each(function (Vehiculo $v) use ($items): void {
                $empresaNombre = $v->empresa ? ' · '.$v->empresa->nombre : '';

                $editarVehiculo = route('vehiculos.edit', $v);

                if ($v->soat_fin) {
                    $items->push($this->item('soat', $v->placa, 'SOAT'.$empresaNombre, $v->soat_fin, route('vehiculos.index'), $editarVehiculo));
                }

                if ($v->tecnomecanica_fin) {
                    $items->push($this->item('tecnomecanica', $v->placa, 'Tecnomecánica'.$empresaNombre, $v->tecnomecanica_fin, route('vehiculos.index'), $editarVehiculo));
                }

                if ($v->inspeccion_sanitaria && $v->inspeccion_sanitaria_fin) {
                    $items->push($this->item('inspeccion', $v->placa, 'Inspección sanitaria'.$empresaNombre, $v->inspeccion_sanitaria_fin, route('vehiculos.index'), $editarVehiculo));
                }
            });

        return $items;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  class-string<ContratistaExterno|ContratistaInterno>  $modelClass
     */
    private function agregarIndRnd(Collection $items, string $modelClass, string $url, string $tipo): void
    {
        $sufijo = ' ('.$tipo.')';
        $rutaEdit = 'contratistas-'.$tipo.'s.edit';

        $modelClass::query()
            ->where('activo', true)
            ->whereNotNull('fecha_vencimiento')
            ->with('empresa:id,nombre')
            ->get()
            ->each(function ($c) use ($items, $url, $sufijo, $rutaEdit): void {
                $ultimaIr = $c->fecha_ultima_ir ? 'Última I/R: '.$c->fecha_ultima_ir->format('d/m/Y') : 'Inducción / reinducción';

                $items->push($this->item(
                    'ind_rnd',
                    $c->nombres_apellidos,
                    $ultimaIr.$sufijo.($c->empresa ? ' · '.$c->empresa->nombre : ''),
                    $c->fecha_vencimiento,
                    $url,
                    route($rutaEdit, $c),
                ));
            });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  class-string<ContratistaExterno|ContratistaInterno>  $modelClass
     */
    private function agregarDocumentosContratistas(Collection $items, string $modelClass, string $url): void
    {
        $esExterno = $modelClass === ContratistaExterno::class;
        $sufijo = $esExterno ? ' (externo)' : ' (interno)';
        $rutaEdit = $esExterno ? 'contratistas-externos.edit' : 'contratistas-internos.edit';

        $modelClass::query()
            ->where('activo', true)
            ->where(function ($query): void {
                $query->whereNotNull('licencia_vencimiento')
                    ->orWhereNotNull('manipulador_vigencia');
            })
            ->get()
            ->each(function ($c) use ($items, $url, $sufijo, $rutaEdit): void {
                if ($c->licencia_conduccion && $c->licencia_vencimiento) {
                    $items->push($this->item('licencia', $c->nombres_apellidos, 'Licencia de conducción'.$sufijo, $c->licencia_vencimiento, $url, route($rutaEdit, $c)));
                }

                if ($c->manipulador_alimentos && $c->manipulador_vigencia) {
                    $items->push($this->item('manipulador', $c->nombres_apellidos, 'Manipulador de alimentos'.$sufijo, $c->manipulador_vigencia, $url, route($rutaEdit, $c)));
                }
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function item(string $tipo, string $titulo, string $detalle, CarbonInterface $fecha, string $url, ?string $editarUrl = null): array
    {
        $hoy = Carbon::now()->startOfDay();
        $fecha = $fecha->copy()->startOfDay();

        return [
            'tipo' => $tipo,
            'titulo' => $titulo,
            'detalle' => $detalle,
            'fecha' => $fecha,
            'dias' => (int) $hoy->diffInDays($fecha, false),
            'url' => $url,
            'editar_url' => $editarUrl ?? $url,
        ];
    }
}
