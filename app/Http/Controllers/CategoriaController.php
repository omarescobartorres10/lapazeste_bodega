<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // Mostrar todas las categorías y aplicar filtro de búsqueda si se proporciona
    public function index(Request $request)
    {
        $categorias = Categoria::when($request->buscar, function($query) use ($request) {
            return $query->where('nombre', 'like', '%' . $request->buscar . '%');
        })->paginate(2);       

        return view('pages.categorias', compact('categorias'));
    }

    // Crear nueva categoría
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        Categoria::create([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('categorias')->with('success', 'Categoría creada con éxito.');
    }

    public function desactivar($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->estado = 0; // Desactivar la categoría
        $categoria->save();

        return redirect()->route('categorias')->with('success', 'Categoría desactivada con éxito.');
    }

    public function activar($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->estado = 1; // Activar la categoría
        $categoria->save();

        return redirect()->route('categorias')->with('success', 'Categoría activada con éxito.');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([            
            'nombre' => 'required|string|max:255',
        ]);

        $categoria = Categoria::findOrFail($id);

        $categoria->nombre = $validatedData['nombre'];

        $categoria->save();

        return redirect()->route('categorias')->with('success', 'Categoria actualizada con éxito');
    }


}
