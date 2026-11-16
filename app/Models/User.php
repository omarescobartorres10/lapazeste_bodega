<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nombre',
        'usuario',
        'password',
        'rol',
        'bodega_id',
        'estado',
    ];

    protected $hidden = [
        'password',
    ];

    public function bodega()
    {
        return $this->belongsTo(Bodega::class);
    }

    // 🔹 Saber si el usuario es administrador
    public function esAdminGlobal(): bool
    {
        return $this->rol === 'admin';
    }
}
