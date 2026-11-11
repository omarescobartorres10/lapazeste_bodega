<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo.
     */
    protected $table = 'productos';

    /**
     * Campos permitidos para asignación masiva.
     */
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'stock',
        'categoria_id',
        'departamento_id',
        'bodega_id',
        'tipo_producto_id',
        'unidades_id',
        'estado',
    ];

    /**
     * Categoría del producto.
     */
    public function categoria()
    {
        return $this->belongsTo(
            Categoria::class
        );
    }

    /**
     * Departamento del producto.
     */
    public function departamento()
    {
        return $this->belongsTo(
            Departamento::class
        );
    }

    /**
     * Bodega del producto.
     */
    public function bodega()
    {
        return $this->belongsTo(
            Bodega::class
        );
    }

    /**
     * Tipo del producto.
     */
    public function tipoProducto()
    {
        return $this->belongsTo(
            TipoProducto::class,
            'tipo_producto_id'
        );
    }

    /**
     * Unidad de medida.
     */
    public function unidades()
    {
        return $this->belongsTo(
            UnidadMedida::class,
            'unidades_id'
        );
    }

    /**
     * Entradas relacionadas.
     */
    public function entradas()
    {
        return $this->hasMany(
            \App\Models\Entrada::class,
            'producto_id'
        );
    }

    /**
     * Salidas relacionadas.
     */
    public function salidas()
    {
        return $this->hasMany(
            \App\Models\Salida::class,
            'producto_id'
        );
    }
}