<?php

namespace App\Support;

final class NumeroDocumento
{
    /** @var list<string> */
    private const SOLO_DIGITOS = ['CC', 'CE', 'TI', 'NIT'];

    /**
     * Normaliza el número de documento para almacenar y mostrar sin separadores.
     * CC/CE/TI/NIT: solo dígitos. Pasaporte y otros: sin espacios, puntos ni guiones.
     */
    public static function normalizar(?string $valor, ?string $tipoDocumento = null): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim($valor);

        if ($valor === '') {
            return '';
        }

        $tipo = $tipoDocumento !== null ? mb_strtoupper(trim($tipoDocumento), 'UTF-8') : null;

        if ($tipo !== null && in_array($tipo, self::SOLO_DIGITOS, true)) {
            return preg_replace('/\D+/', '', $valor) ?? $valor;
        }

        return preg_replace('/[\s.\-_\/,]+/', '', $valor) ?? $valor;
    }
}
