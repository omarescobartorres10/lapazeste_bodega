<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudDetalle extends Model
{
    use HasFactory;

    protected $table = 'solicitud_detalles'; // 👈 AGRÉGALO SI TU TABLA TIENE ESTE NOMBRE

    protected $fillable = [
        'solicitud_id',
        'producto_id',
        'cantidad',
        'observacion',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
