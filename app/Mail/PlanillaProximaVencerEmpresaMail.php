<?php

namespace App\Mail;

use App\Models\Empresa;
use App\Support\AlertaPlanillaHito;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanillaProximaVencerEmpresaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Empresa $empresa,
        public int $diasRestantes,
        public string $hito = AlertaPlanillaHito::PROXIMA_10,
    ) {}

    public function envelope(): Envelope
    {
        $esVencida = AlertaPlanillaHito::esVencida($this->hito);
        $prefijo = $esVencida ? 'Planilla SS vencida' : 'Recordatorio planilla SS';

        return new Envelope(
            subject: $prefijo.' — '.$this->empresa->nombre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.planilla-proxima-vencer-empresa',
        );
    }
}
