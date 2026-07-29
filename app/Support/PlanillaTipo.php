<?php

namespace App\Support;

final class PlanillaTipo
{
    public const INDEPENDIENTE = 'INDEPENDIENTE';

    public const DEPENDIENTE = 'DEPENDIENTE';

    /** @var array<string, string> */
    public const OPCIONES = [
        self::INDEPENDIENTE => 'Independiente',
        self::DEPENDIENTE => 'Dependiente',
    ];

    /** @return list<string> */
    public static function valores(): array
    {
        return array_keys(self::OPCIONES);
    }
}
