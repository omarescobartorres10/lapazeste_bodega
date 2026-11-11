<?php

namespace App\Mail;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitudAprobada extends Mailable
{
    use Queueable, SerializesModels;

    public $solicitud;
    public $pdfPath;

    public function __construct($solicitud, $pdfPath)
    {
        $this->solicitud = $solicitud;
        $this->pdfPath   = $pdfPath;
    }

    public function build()
    {
        return $this->subject("Solicitud {$this->solicitud->codigo} APROBADA")
                    ->view('emails.solicitud-aprobada')
                    ->attach($this->pdfPath, [
                        'as'   => "Solicitud-{$this->solicitud->codigo}.pdf",
                        'mime' => 'application/pdf',
                    ]);
    }
}
