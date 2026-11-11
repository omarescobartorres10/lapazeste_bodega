<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    /**
     * Muestra el listado de departamentos.
     */
    public function index(Request $request)
    {
        // Opción de búsqueda
        $buscar = $request->get('buscar');

        $departamentos = Departamento::when($buscar, function ($query, $buscar) {
            return $query->where('nombre', 'like', "%{$buscar}%")
                         ->orWhere('encargado', 'like', "%{$buscar}%");
        })
        ->orderBy('id', 'desc')
        ->paginate(10);

        return view('pages.departamento', compact('departamentos'));
    }

    /**
     * Guarda un nuevo departamento.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'encargado' => 'nullable|string|max:255',
        ]);

        Departamento::create([
            'nombre' => $request->nombre,
            'encargado' => $request->encargado,
            'estado' => 'Activo', // por defecto activo
        ]);

        return redirect()->route('departamentos')
                         ->with('success', 'Departamento creado correctamente.');
    }

    /**
     * Actualiza los datos del departamento.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'encargado' => 'nullable|string|max:255',
        ]);

        $departamento = Departamento::findOrFail($id);
        $departamento->update([
            'nombre' => $request->nombre,
            'encargado' => $request->encargado,
        ]);

        return redirect()->route('departamentos')
                         ->with('success', 'Departamento actualizado correctamente.');
    }

    /**
     * Elimina un departamento.
     */
    public function destroy($id)
    {
        $departamento = Departamento::findOrFail($id);
        try {
            $departamento->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            if (in_array((string) $e->getCode(), ['23000', '23503'], true)) {
                return back()->with('error', 'No se puede eliminar este departamento porque tiene registros asociados.');
            }
            throw $e;
        }

        return redirect()->route('departamentos')
                        ->with('success', 'Departamento eliminado correctamente.');
    }

}
