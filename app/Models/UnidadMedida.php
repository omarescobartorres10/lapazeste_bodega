<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{

    use HasFactory;

    // Definir el nombre de la tabla si no es plural automáticamente
    protected $table = 'unidades';

    // Atributos que se pueden asignar de manera masiva
    protected $fillable = ['nombre'];

    /**
     * Relación uno a muchos con productos
     * Un tipo de producto puede estar asociado a muchos productos
     */
    public function productos()
    {
        return $this->hasMany(Producto::class, 'unidades_id');
    }
}
