<?php

namespace App\Http\Controllers;

use App\Models\Solicitante;
use App\Models\Departamento;
use Illuminate\Http\Request;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SolicitantesExport;

class SolicitanteController extends Controller
{
    // =====================================================
    // LISTADO + BÚSQUEDA + FILTRO + CONTADOR SOLICITUDES
    // =====================================================
    public function index(Request $request)
    {
        $search      = $request->search;
        $filtroDepto = $request->departamento_id;

        $query = Solicitante::with('departamento')
            ->withCount('solicitudes');

        // 🔍 BÚSQUEDA AVANZADA (DUI, nombre, departamento)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('dui', 'LIKE', "%$search%")
                  ->orWhere('nombre', 'LIKE', "%$search%")
                  ->orWhereHas('departamento', function ($d) use ($search) {
                      $d->where('nombre', 'LIKE', "%$search%");
                  });
            });
        }

        // 🔍 FILTRO POR DEPARTAMENTO
        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $filtroDepto);
        }

        $solicitantes  = $query->orderBy('nombre')->paginate(5);
        $departamentos = Departamento::orderBy('nombre')->get();

        return view('admin.solicitantes.index', compact(
            'solicitantes',
            'departamentos',
            'search',
            'filtroDepto'
        ));
    }

    // =====================================================
    // GUARDAR NUEVO SOLICITANTE (USADO POR MODAL "NUEVO")
    // =====================================================
    public function store(Request $request)
    {
        $request->validate([
            'dui'             => 'required|unique:solicitantes,dui',
            'codigo_marcador' => 'required',
            'nombre'          => 'required',
            'cargo'           => 'required',
            'departamento_id' => 'required',
            'telefono'        => 'required',
            'correo'          => 'required|email',
            'estado'          => 'required'
        ]);

        Solicitante::create($request->all());

        return redirect()->route('solicitantes.index')
            ->with('success', 'Solicitante creado correctamente.');
    }

    // =====================================================
    // ACTUALIZAR SOLICITANTE (USADO POR MODAL "EDITAR")
    // =====================================================
    public function update(Request $request, $id)
    {
        $solicitante = Solicitante::findOrFail($id);

        $request->validate([
            'dui'             => "required|unique:solicitantes,dui,$id",
            'codigo_marcador' => 'required',
            'nombre'          => 'required',
            'cargo'           => 'required',
            'departamento_id' => 'required',
            'telefono'        => 'required',
            'correo'          => 'required|email',
            'estado'          => 'required'
        ]);

        $solicitante->update($request->all());

        return redirect()->route('solicitantes.index')
            ->with('success', 'Solicitante actualizado correctamente.');
    }

    // =====================================================
    // ELIMINAR SOLICITANTE
    // =====================================================
    public function destroy($id)
    {
        Solicitante::findOrFail($id)->delete();

        return redirect()->route('solicitantes.index')
            ->with('success', 'Solicitante eliminado correctamente.');
    }

    // =====================================================
    // EXPORTAR EXCEL
    // =====================================================
    public function exportExcel()
    {
        return Excel::download(new SolicitantesExport, 'solicitantes.xlsx');
    }

    // =====================================================
    // EXPORTAR PDF
    // =====================================================
    public function exportPdf()
    {
        $solicitantes = Solicitante::with('departamento')
            ->withCount('solicitudes')
            ->orderBy('nombre')
            ->get();

        $pdf = PDF::loadView('admin.solicitantes.pdf', compact('solicitantes'));
        return $pdf->download('solicitantes.pdf');
    }
}
