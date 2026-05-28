<?php

namespace App\Support;

final class TiposDocumento
{
    /** @var array<string, string> */
    public const OPCIONES = [
        'CC' => 'CC',
        'CE' => 'CE',
        'PAS' => 'Pasaporte',
        'TI' => 'TI',
        'NIT' => 'NIT',
        'PPT' => 'PPT',
    ];

    /** @return list<string> */
    public static function valores(): array
    {
        return array_keys(self::OPCIONES);
    }
}
