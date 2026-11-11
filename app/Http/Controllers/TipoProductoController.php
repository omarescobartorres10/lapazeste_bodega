<?php

namespace App\Http\Controllers;

use App\Models\TipoProducto;
use Illuminate\Http\Request;

class TipoProductoController extends Controller
{
    // Mostrar todos los tipos de productos y aplicar filtro si se proporciona
    public function index(Request $request)
    {
        $tipoProducto = TipoProducto::when($request->buscar, function($query) use ($request) {
            return $query->where('nombre', 'like', '%' . $request->buscar . '%');
        })->paginate(5);

        return view('pages.TipoProducto', compact('tipoProducto'));
    }

    // Crear un nuevo tipo de producto
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        TipoProducto::create([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('tipoProducto')->with('success', 'Tipo de producto creado correctamente.');
    }

    // Activar un tipo de producto
    public function activar($id)
    {
        $tipo = TipoProducto::findOrFail($id);
        $tipo->estado = 1;
        $tipo->save();

        return redirect()->route('tipoProducto')->with('success', 'Tipo de producto activado correctamente.');
    }

    // Desactivar un tipo de producto
    public function desactivar($id)
    {
        $tipo = TipoProducto::findOrFail($id);
        $tipo->estado = 0;
        $tipo->save();

        return redirect()->route('tipoProducto')->with('success', 'Tipo de producto desactivado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([            
            'nombre' => 'required|string|max:255',
        ]);

        $tipoProducto = TipoProducto::findOrFail($id);

        $tipoProducto->nombre = $validatedData['nombre'];

        $tipoProducto->save();

        return redirect()->route('tipoProducto')->with('success', 'Tipo Producto actualizado con éxito');
    }
}
