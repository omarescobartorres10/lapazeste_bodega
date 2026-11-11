<?php

// app/Mail/SolicitudCreadaAdmin.php
namespace App\Mail;

use App\Models\Solicitud;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitudCreadaAdmin extends Mailable
{
    use Queueable, SerializesModels;

    public Solicitud $solicitud;

    public function __construct(Solicitud $solicitud)
    {
        $this->solicitud = $solicitud->load('detalles.producto', 'bodega');
    }

    public function build()
    {
        return $this->subject('Nueva solicitud de productos')
            ->view('emails.solicitud_admin');
    }
}
