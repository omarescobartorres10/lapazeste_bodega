<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use Illuminate\Http\Request;

class BodegaController extends Controller
{
    /**
     * Listado de bodegas con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $bodegas = Bodega::when($buscar, function ($query, $buscar) {
            return $query->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('responsable', 'like', "%{$buscar}%")
                         ->orWhere('direccion', 'like', "%{$buscar}%")
                         ->orWhere('correo', 'like', "%{$buscar}%");
        })
        ->orderBy('id', 'desc')
        ->paginate(10);

        return view('pages.bodega', compact('bodegas'));
    }

    /**
     * Guardar una nueva bodega.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|in:Activo,Inactivo',
            'correo' => 'required_if:estado,Activo|nullable|email|max:255',
        ], [
            'correo.required_if' => 'El correo es obligatorio cuando la bodega está Activa.',
        ]);

        Bodega::create([
            'nombre' => strtoupper($request->nombre),
            'responsable' => strtoupper($request->responsable),
            'direccion' => strtoupper($request->direccion),
            'estado' => $request->estado,
            'correo' => $request->correo ? strtolower($request->correo) : null,
        ]);

        return redirect()->route('bodegas.index')
                         ->with('success', 'Bodega creada correctamente.');
    }

    /**
     * Actualizar datos de la bodega.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'estado' => 'required|in:Activo,Inactivo',
            'correo' => 'required_if:estado,Activo|nullable|email|max:255',
        ], [
            'correo.required_if' => 'El correo es obligatorio cuando la bodega está Activa.',
        ]);

        $bodega = Bodega::findOrFail($id);

        $bodega->update([
            'nombre' => strtoupper($request->nombre),
            'responsable' => strtoupper($request->responsable),
            'direccion' => strtoupper($request->direccion),
            'estado' => $request->estado,
            'correo' => $request->correo ? strtolower($request->correo) : null,
        ]);

        return redirect()->route('bodegas.index')
                         ->with('success', 'Bodega actualizada correctamente.');
    }

    /**
     * Eliminar una bodega.
     */
    public function destroy($id)
    {
        $bodega = Bodega::findOrFail($id);
        $bodega->delete();

        return redirect()->route('bodegas.index')
                         ->with('success', 'Bodega eliminada correctamente.');
    }

    /**
     * Desactivar bodega (estado = Inactivo)
     */
    public function desactivar($id)
    {
        $bodega = Bodega::findOrFail($id);
        $bodega->estado = 'Inactivo';
        $bodega->save();

        return redirect()->route('bodegas.index')
                         ->with('success', 'Bodega desactivada correctamente.');
    }

    /**
     * Activar bodega (estado = Activo)
     */
    public function activar($id)
    {
        $bodega = Bodega::findOrFail($id);

        if (!$bodega->correo) {
            return back()->with('error', 'Debe agregar un correo antes de activar la bodega.');
        }

        $bodega->estado = 'Activo';
        $bodega->save();

        return redirect()->route('bodegas.index')
                         ->with('success', 'Bodega activada correctamente.');
    }
}
