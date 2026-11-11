<?php

namespace App\Mail;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitudEnviada extends Mailable
{
    use Queueable, SerializesModels;

    public $solicitud;
    public $archivo;

    public function __construct(Solicitud $solicitud, $archivoPdf)
    {
        $this->solicitud = $solicitud;
        $this->archivo = $archivoPdf;
    }

    public function build()
    {
        return $this->subject("Solicitud registrada - {$this->solicitud->codigo}")
                    ->view('emails.solicitud')
                    ->attach($this->archivo, [
                        'as' => "Solicitud-{$this->solicitud->codigo}.pdf",
                        'mime' => 'application/pdf'
                    ]);
    }
}
