<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoProducto extends Model
{
    use HasFactory;

    // Definir el nombre de la tabla si no es plural automáticamente
    protected $table = 'tipos_productos';

    // Atributos que se pueden asignar de manera masiva
    protected $fillable = ['nombre'];

    /**
     * Relación uno a muchos con productos
     * Un tipo de producto puede estar asociado a muchos productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'tipo_producto_id');
    }
}
