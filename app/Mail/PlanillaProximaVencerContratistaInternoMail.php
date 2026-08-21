<?php

namespace App\Mail;

use App\Models\ContratistaInterno;
use App\Support\AlertaPlanillaHito;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanillaProximaVencerContratistaInternoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContratistaInterno $contratista,
        public int $diasRestantes,
        public string $hito = AlertaPlanillaHito::PROXIMA_10,
    ) {}

    public function envelope(): Envelope
    {
        $empresa = $this->contratista->empresa;
        $esVencida = AlertaPlanillaHito::esVencida($this->hito);
        $prefijo = $esVencida
            ? 'Alerta interna SST: planilla SS independiente vencida'
            : 'Alerta interna SST: planilla SS independiente próxima a vencer';

        return new Envelope(
            subject: $prefijo.' — '.$this->contratista->nombres_apellidos
                .($empresa ? ' · '.$empresa->nombre : ''),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.planilla-proxima-vencer-contratista-interno',
        );
    }
}
