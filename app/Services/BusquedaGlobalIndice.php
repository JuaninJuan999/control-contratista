<?php

namespace App\Services;

use App\Models\ContratistaExterno;
use App\Models\ContratistaInterno;
use App\Models\Empresa;
use App\Models\Vehiculo;

class BusquedaGlobalIndice
{
    private const EMPRESAS_POR_PAGINA = 15;

    private const LIMITE_SUGERENCIAS = 15;

    /** @var list<array{tipo: string, label: string, sublabel: string, url: string, buscar: string}>|null */
    private ?array $cache = null;

    /**
     * @return list<array{tipo: string, label: string, sublabel: string, url: string, buscar: string}>
     */
    public function items(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $items = [];

        Empresa::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'nit'])
            ->each(function (Empresa $empresa) use (&$items): void {
                $items[] = [
                    'tipo' => 'empresa',
                    'label' => $empresa->nombre,
                    'sublabel' => $empresa->nit ? 'NIT: '.$empresa->nit : 'Empresa',
                    'url' => $this->url('/empresas', [
                        'page' => $this->paginaEmpresa($empresa),
                        'abrir' => 'empresa-'.$empresa->id,
                    ]),
                    'buscar' => $this->textoBusqueda($empresa->nombre, $empresa->nit),
                ];
            });

        ContratistaExterno::query()
            ->with('empresa:id,nombre')
            ->orderBy('nombres_apellidos')
            ->get(['id', 'nombres_apellidos', 'tipo_documento', 'numero_documento', 'empresa_id'])
            ->each(function (ContratistaExterno $c) use (&$items): void {
                $items[] = [
                    'tipo' => 'contratista_externo',
                    'label' => $c->nombres_apellidos,
                    'sublabel' => $c->tipo_documento.' '.$c->numero_documento.($c->empresa ? ' · '.$c->empresa->nombre : ''),
                    'url' => $this->url('/contratistas-externos', ['abrir' => 'externo-'.$c->id]),
                    'buscar' => $this->textoBusqueda(
                        $c->nombres_apellidos,
                        $c->numero_documento,
                        $c->empresa?->nombre
                    ),
                ];
            });

        ContratistaInterno::query()
            ->with('empresa:id,nombre')
            ->orderBy('nombres_apellidos')
            ->get(['id', 'nombres_apellidos', 'tipo_documento', 'numero_documento', 'empresa_id'])
            ->each(function (ContratistaInterno $c) use (&$items): void {
                $items[] = [
                    'tipo' => 'contratista_interno',
                    'label' => $c->nombres_apellidos,
                    'sublabel' => $c->tipo_documento.' '.$c->numero_documento.($c->empresa ? ' · '.$c->empresa->nombre : ''),
                    'url' => $this->url('/contratistas-internos', ['abrir' => 'interno-'.$c->id]),
                    'buscar' => $this->textoBusqueda(
                        $c->nombres_apellidos,
                        $c->numero_documento,
                        $c->empresa?->nombre
                    ),
                ];
            });

        Vehiculo::query()
            ->with('empresa:id,nombre')
            ->orderBy('placa')
            ->get(['id', 'placa', 'empresa_id'])
            ->each(function (Vehiculo $v) use (&$items): void {
                $items[] = [
                    'tipo' => 'vehiculo',
                    'label' => $v->placa,
                    'sublabel' => $v->empresa ? 'Empresa: '.$v->empresa->nombre : 'Vehículo',
                    'url' => $this->url('/vehiculos', ['abrir' => 'vehiculo-'.$v->id]),
                    'buscar' => $this->textoBusqueda(
                        $v->placa,
                        str_replace(' ', '', $v->placa),
                        $v->empresa?->nombre
                    ),
                ];
            });

        return $this->cache = $items;
    }

    /**
     * @return list<array{tipo: string, label: string, sublabel: string, url: string}>
     */
    public function filtrar(string $termino, ?int $limite = null): array
    {
        $termino = trim($termino);
        if (mb_strlen($termino) < 2) {
            return [];
        }

        $limite ??= self::LIMITE_SUGERENCIAS;
        $needle = $this->normalizar($termino);
        $needlePlaca = $this->normalizar(str_replace(' ', '', $termino));
        $resultados = [];

        foreach ($this->items() as $item) {
            $coincide = str_contains($item['buscar'], $needle)
                || ($needlePlaca !== '' && str_contains($item['buscar'], $needlePlaca));

            if (! $coincide) {
                continue;
            }

            unset($item['buscar']);
            $resultados[] = $item;

            if (count($resultados) >= $limite) {
                break;
            }
        }

        return $resultados;
    }

    private function textoBusqueda(string ...$partes): string
    {
        return $this->normalizar(implode(' ', array_filter($partes, fn ($p) => is_string($p) && trim($p) !== '')));
    }

    private function normalizar(string $texto): string
    {
        return mb_strtolower(trim($texto), 'UTF-8');
    }

    private function paginaEmpresa(Empresa $empresa): int
    {
        $posicion = Empresa::query()
            ->where(function ($query) use ($empresa): void {
                $query->where('nombre', '<', $empresa->nombre)
                    ->orWhere(function ($q) use ($empresa): void {
                        $q->where('nombre', $empresa->nombre)
                            ->where('id', '<', $empresa->id);
                    });
            })
            ->count();

        return (int) floor($posicion / self::EMPRESAS_POR_PAGINA) + 1;
    }

    /**
     * @param  array<string, int|string>  $query
     */
    private function url(string $path, array $query = []): string
    {
        if ($query === []) {
            return $path;
        }

        return $path.'?'.http_build_query($query);
    }
}
