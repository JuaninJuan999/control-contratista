<?php

namespace App\Support;

final class UserRol
{
    public const SUPERADMIN = 'superadministrador';

    public const ADMIN = 'administrador';

    public const OPERATIVO = 'operativo';

    public const CONSULTA = 'consulta';

    /** @var array<string, string> */
    public const ETIQUETAS = [
        self::SUPERADMIN => 'Superadministrador',
        self::ADMIN => 'Administrador',
        self::OPERATIVO => 'Operativo',
        self::CONSULTA => 'Consulta',
    ];

    /** @return list<string> */
    public static function todos(): array
    {
        return array_keys(self::ETIQUETAS);
    }

    /** @return array<string, string> */
    public static function asignablesPara(?object $usuario): array
    {
        $rol = is_object($usuario) && isset($usuario->rol) ? $usuario->rol : null;

        if ($rol === self::SUPERADMIN) {
            return self::ETIQUETAS;
        }

        if ($rol === self::ADMIN) {
            return array_filter(
                self::ETIQUETAS,
                fn (string $clave) => $clave !== self::SUPERADMIN,
                ARRAY_FILTER_USE_KEY
            );
        }

        return [];
    }

    public static function etiqueta(?string $rol): string
    {
        return self::ETIQUETAS[$rol] ?? (string) $rol;
    }
}
