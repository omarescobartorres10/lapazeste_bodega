<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Traslado extends Model
{
    protected $fillable = [
        'codigo_operacion',
        'solicitud_id',
        'bodega_origen_id',
        'bodega_destino_id',
        'producto_id_origen',
        'producto_id_destino',
        'categoria_id',
        'departamento_id',
        'cantidad',
        'entregado_por',
        'recibido_por',
        'observacion',
        'estado',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function bodegaOrigen()
    {
        return $this->belongsTo(Bodega::class, 'bodega_origen_id');
    }

    public function bodegaDestino()
    {
        return $this->belongsTo(Bodega::class, 'bodega_destino_id');
    }

    public function productoOrigen()
    {
        return $this->belongsTo(Producto::class, 'producto_id_origen');
    }

    public function productoDestino()
    {
        return $this->belongsTo(Producto::class, 'producto_id_destino');
    }
}