<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Baja extends Model
{
    protected $fillable = [
        'codigo_operacion',
        'bodega_id',
        'producto_id', 
        'categoria_id',
        'departamento_id',
        'cantidad',
        'motivo',
        'responsable',
        'estado'
    ];

    // 🔹 Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
}
