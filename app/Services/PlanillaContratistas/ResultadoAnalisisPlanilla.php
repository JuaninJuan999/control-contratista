<?php

namespace App\Services\PlanillaContratistas;

class ResultadoAnalisisPlanilla
{
    /** @param list<array<string, mixed>> $actualizados */
    /** @param list<array<string, mixed>> $inactivados */
    /** @param list<array<string, mixed>> $nuevos */
    /** @param list<array<string, mixed>> $pendientes */
    /** @param list<array<string, mixed>> $errores */
    /** @param list<array<string, mixed>> $advertencias */
    public function __construct(
        public array $actualizados = [],
        public array $inactivados = [],
        public array $nuevos = [],
        public array $pendientes = [],
        public array $errores = [],
        public array $advertencias = [],
    ) {}

    public function tieneErroresBloqueantes(): bool
    {
        return $this->errores !== [];
    }

    public function totalFilasExcel(): int
    {
        return count($this->actualizados)
            + count($this->nuevos)
            + count($this->pendientes)
            + count($this->errores);
    }
}
