<?php

namespace App\Services\PlanillaContratistas;

readonly class PlanillaFilaContratista
{
    public function __construct(
        public int $numeroFila,
        public string $numeroDocumento,
        public string $tipoDocumento,
        public ?string $nombresApellidos,
        public string $tipoContratista,
        public ?string $arl,
    ) {}

    public function claveDocumento(): string
    {
        return mb_strtoupper($this->tipoDocumento, 'UTF-8').'|'.$this->numeroDocumento;
    }
}
