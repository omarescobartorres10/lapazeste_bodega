<?php

// app/Models/Prestamo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prestamo extends Model
{
    protected $table = 'prestamos';

    protected $fillable = [
        'codigo_operacion',
        'bodega_id',
        'categoria_id',
        'producto_id',
        'departamento_id',
        'cantidad',
        'cantidad_devuelta',
        'destino',
        'entregado_por',
        'recibido_por',
        'observacion',
        'estado',
        'fecha_devolucion',
    ];

    protected $casts = [
        'fecha_devolucion' => 'datetime',
    ];

    // 🔗 Relaciones
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
