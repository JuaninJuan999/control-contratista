<?php

namespace App\Support;

final class PeriodoPlanilla
{
    /** @var array<int, string> */
    public const MESES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    /** @var array<int, string> */
    public const MESES_CORTOS = [
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
    ];

    public static function etiqueta(?int $anio, ?int $mes, bool $corto = false): string
    {
        if ($anio === null || $mes === null) {
            return '—';
        }

        $meses = $corto ? self::MESES_CORTOS : self::MESES;

        return ($meses[$mes] ?? (string) $mes).' '.$anio;
    }
}
