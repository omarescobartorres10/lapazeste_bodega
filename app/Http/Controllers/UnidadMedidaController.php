<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{

    // Mostrar todas las categorías y aplicar filtro de búsqueda si se proporciona
    public function index(Request $request)
    {
        $unidades = UnidadMedida::when($request->buscar, function($query) use ($request) {
            return $query->where('nombre', 'like', '%' . $request->buscar . '%');
        })->paginate(5);       

        return view('pages.UnidadesMedida', compact('unidades'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([            
            'nombre' => 'required|string|max:255',
        ]);

        $unidades = UnidadMedida::findOrFail($id);

        $unidades->nombre = $validatedData['nombre'];

        $unidades->save();

        return redirect()->route('unidades')->with('success', 'Unidad de Medida actualizada con éxito');
    }

    // Crear nueva categoría
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        UnidadMedida::create([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('unidades')->with('success', 'Unidad de Medida creada con éxito.');
    }

    // Activar un tipo de producto
    public function activar($id)
    {
        $unidades = UnidadMedida::findOrFail($id);
        $unidades->estado = 1;
        $unidades->save();

        return redirect()->route('unidades')->with('success', 'Unidad de Medadia activada correctamente.');
    }

    // Desactivar un tipo de producto
    public function desactivar($id)
    {
        $unidades = UnidadMedida::findOrFail($id);
        $unidades->estado = 0;
        $unidades->save();

        return redirect()->route('unidades')->with('success', 'Unidad de Medadia desactivada correctamente.');
    }


    public function destroy(UnidadMedida $unidad)
    {
        $unidad->delete();
        return redirect()->route('unidades')->with('success', 'Unidad de medida eliminada.');
    }
}
