<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salida extends Model
{
    protected $fillable = [
        'producto_id',
        'categoria_id',
        'departamento_id',
        'cantidad',
        'destino',
        'recibido_por',
        'entregado_por',
        'observacion',
        'bodega_id',
        'estado',
        'codigo_operacion',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }
}
