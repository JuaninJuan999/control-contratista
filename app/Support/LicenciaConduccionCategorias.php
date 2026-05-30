<?php

namespace App\Support;

use Illuminate\Support\Carbon;

final class LicenciaConduccionCategorias
{
    /** @var array<string, string> */
    public const OPCIONES = [
        'A1' => 'A1 — Motocicletas',
        'A2' => 'A2 — Motocarros',
        'B1' => 'B1 — Automóviles',
        'B2' => 'B2 — Camionetas y camperos',
        'B3' => 'B3 — Carga liviana',
        'C1' => 'C1 — Carga pesada',
        'C2' => 'C2 — Articulados',
        'C3' => 'C3 — Trenes rodados',
    ];

    /**
     * @param  list<string>|null  $categorias
     * @param  array<string, mixed>|null  $vencimientos
     * @return array<string, string>|null
     */
    public static function normalizarVencimientos(?array $categorias, ?array $vencimientos): ?array
    {
        $categorias = array_values(array_filter($categorias ?? [], fn ($c) => is_string($c) && $c !== ''));
        $vencimientos = is_array($vencimientos) ? $vencimientos : [];
        $normalizado = [];

        foreach ($categorias as $categoria) {
            if (! array_key_exists($categoria, self::OPCIONES)) {
                continue;
            }

            $fecha = $vencimientos[$categoria] ?? null;
            if (! is_string($fecha) || trim($fecha) === '') {
                continue;
            }

            $normalizado[$categoria] = trim($fecha);
        }

        return $normalizado === [] ? null : $normalizado;
    }

    public static function esVigente(?string $fecha): ?bool
    {
        if ($fecha === null || trim($fecha) === '') {
            return null;
        }

        try {
            return Carbon::parse($fecha)->startOfDay()->greaterThanOrEqualTo(now()->startOfDay());
        } catch (\Throwable) {
            return null;
        }
    }

    public static function etiquetaEstado(?string $fecha): ?string
    {
        $vigente = self::esVigente($fecha);

        if ($vigente === null) {
            return null;
        }

        return $vigente ? 'VIGENTE' : 'VENCIDA';
    }
}
