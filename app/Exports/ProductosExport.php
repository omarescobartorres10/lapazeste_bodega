<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\Auth;

class ProductosExport implements FromView
{
    public function __construct($bodega = null, $buscar = null)
    {
        $this->bodega = $bodega;
        $this->buscar = $buscar;
    }

    public function view(): View
    {
        $user = Auth::user();
        $bodegaAsignada = $user->bodega_id ?? null;

        $query = Producto::with(['bodega', 'categoria', 'departamento', 'unidades', 'tipoProducto'])
            ->when($bodegaAsignada, fn($q) => $q->where('bodega_id', $bodegaAsignada)) // Si tiene bodega, se filtra solo la suya
            ->when(!$bodegaAsignada && $this->bodega, fn($q) => $q->where('bodega_id', $this->bodega))
            ->when($this->buscar, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nombre', 'like', "%{$this->buscar}%")
                        ->orWhere('codigo', 'like', "%{$this->buscar}%");
                });
            })
            ->orderBy('bodega_id')
            ->orderBy('nombre');

        $productos = $query->get();

        return view('exports.productos', [
            'productos' => $productos,
            'user' => $user
        ]);
    }
}
