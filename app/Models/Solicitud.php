<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';

    protected $fillable = [
        'codigo',
        'solicitante',
        'telefono',
        'correo',
        'fecha_requerida',
        'fecha_devolucion',
        'departamento_solicitante',
        'comentario',
        'estado',
        'bodega_id',
        'motivo_rechazo',
        'resolucion',

        'tipo_movimiento',
        'bodega_destino_id'
    ];

    public function detalles()
    {
        return $this->hasMany(SolicitudDetalle::class);
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_solicitante');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

        public function bodegaDestino()
    {
        return $this->belongsTo(Bodega::class, 'bodega_destino_id');
    }
}
