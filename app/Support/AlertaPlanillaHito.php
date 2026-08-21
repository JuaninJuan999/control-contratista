<?php

namespace App\Support;

final class AlertaPlanillaHito
{
    public const PROXIMA_10 = 'proxima_10';

    public const PROXIMA_5 = 'proxima_5';

    public const VENCIDA_10 = 'vencida_10';

    /**
     * Determina si hoy corresponde enviar alerta según los días al límite.
     * null = hoy no se envía nada para esta vigencia.
     */
    public static function paraDias(?int $dias): ?string
    {
        if ($dias === null) {
            return null;
        }

        $diasProxima = config('alertas_planilla.dias_alertas_proxima', [10, 5]);

        if (in_array($dias, $diasProxima, true)) {
            return 'proxima_'.$dias;
        }

        $diasVencida = (int) config('alertas_planilla.dias_alerta_vencida', 10);

        if ($dias === -$diasVencida) {
            return self::VENCIDA_10;
        }

        return null;
    }

    public static function esVencida(string $hito): bool
    {
        return str_starts_with($hito, 'vencida_');
    }

    public static function etiqueta(string $hito): string
    {
        return match ($hito) {
            self::PROXIMA_10 => 'Próxima a vencer (10 días)',
            self::PROXIMA_5 => 'Próxima a vencer (5 días)',
            self::VENCIDA_10 => 'Vencida (10 días sin renovar)',
            default => $hito,
        };
    }
}
