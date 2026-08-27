<?php

namespace App\Support;

final class TerminoBusqueda
{
    /**
     * Construye el patrón para un ILIKE tolerante: escapa los comodines que escriba
     * el usuario y convierte cada espacio en un comodín, de modo que
     * "TECNOLOGICA INFINITY" encuentre "TECNOLOGICA  INFINITY SAS".
     */
    public static function patron(string $termino): string
    {
        $termino = trim($termino);

        if ($termino === '') {
            return '%';
        }

        $escapado = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $termino);

        return '%'.preg_replace('/\s+/', '%', $escapado).'%';
    }

    /**
     * Deja solo los dígitos, para comparar NIT y documentos escritos con puntos,
     * guiones o espacios contra los valores guardados sin separadores.
     */
    public static function digitos(string $termino): string
    {
        return preg_replace('/\D+/', '', $termino) ?? '';
    }
}
