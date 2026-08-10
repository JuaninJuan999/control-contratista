<?php

namespace App\Mail;

use App\Models\Empresa;
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
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio: planilla de seguridad social próxima a vencer — '.$this->empresa->nombre,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mails.planilla-proxima-vencer-empresa',
        );
    }
}
