<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Solicitante extends Model
{
    use HasFactory;

    protected $fillable = [
        'dui',
        'codigo_marcador',
        'nombre',
        'cargo',
        'departamento_id',
        'telefono',
        'correo',
        'estado'
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    // 🔥 NUEVO: contar solicitudes
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class, 'solicitante', 'nombre');
    }
}
