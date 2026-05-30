<?php

namespace App\Support;

final class DuracionFormateada
{
    public static function desdeSegundos(int $segundos): string
    {
        if ($segundos <= 0) {
            return '0s';
        }

        $horas = intdiv($segundos, 3600);
        $minutos = intdiv($segundos % 3600, 60);
        $resto = $segundos % 60;

        $partes = [];

        if ($horas > 0) {
            $partes[] = $horas.'h';
        }

        if ($minutos > 0) {
            $partes[] = $minutos.'m';
        }

        if ($resto > 0 && $horas === 0) {
            $partes[] = $resto.'s';
        }

        return implode(' ', $partes);
    }
}
