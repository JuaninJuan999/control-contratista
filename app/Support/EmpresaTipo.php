<?php

namespace App\Support;

final class EmpresaTipo
{
    public const INTERNA = 'INTERNA';

    public const EXTERNA = 'EXTERNA';

    /** @var array<string, string> */
    public const OPCIONES = [
        self::INTERNA => 'Interna',
        self::EXTERNA => 'Externa',
    ];

    /** @return list<string> */
    public static function valores(): array
    {
        return array_keys(self::OPCIONES);
    }

    public static function etiqueta(?string $valor): string
    {
        if ($valor === null || $valor === '') {
            return 'Sin clasificar';
        }

        return self::OPCIONES[$valor] ?? $valor;
    }
}
