<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entrada extends Model
{
    protected $fillable = [
        'departamento_id',
        'categoria_id',
        'producto_id',
        'bodega_id',
        'cantidad',
        'recibido_por',
        'entregado_por',
        'observacion',
        'estado',
        'codigo_operacion',
        'numero_factura',
        'numero_orden_compra',
        'factura_pdf'
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }
}
