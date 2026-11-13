<?php

namespace App\Http\Controllers;

use App\Models\Baja;
use App\Models\Producto;
use App\Models\Departamento;
use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BajaController extends Controller
{
    /* =============================
     * 📌 FORMULARIO PRINCIPAL
     * ============================= */
    public function index()
    {
        $user   = Auth::user();
        $bodega = $user->bodega ?? null;

        $bodegas = Bodega::where('estado', 1)->get();

        return view('pages.bajas', compact('bodega', 'bodegas'));
    }

    /* =============================
     * 📌 GUARDAR BAJA
     * ============================= */
    public function store(Request $request)
    {
        $user       = Auth::user();
        $bodegaUser = $user->bodega_id;

        $validated = $request->validate([
            'bodega_id' => 'nullable|exists:bodegas,id',
            'productos' => 'required|array|min:1',

            'productos.*.producto_id'     => 'required|exists:productos,id',
            'productos.*.categoria_id'    => 'required|exists:categorias,id',
            'productos.*.departamento_id' => 'required|exists:departamentos,id',
            'productos.*.cantidad'        => 'required|integer|min:1',

            'motivo'        => 'required|string|max:255',
            'responsable'   => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {

            /** 🔥 GENERAR CÓDIGO OPERACIÓN */
            $fecha = now()->format('dmY');

            $contador = Baja::whereDate('created_at', now()->toDateString())
                ->distinct('codigo_operacion')
                ->count();

            $correlativo     = str_pad($contador + 1, 4, '0', STR_PAD_LEFT);
            $codigoOperacion = "BAJ-{$fecha}-LPE-{$correlativo}";

            /** 🔥 Registrar cada producto */
            foreach ($validated['productos'] as $row) {

                // Bodega asignada o seleccionada
                $bodegaId = $bodegaUser ?: $validated['bodega_id'];

                if (!$bodegaId) {
                    throw new \Exception("Debe seleccionar una bodega válida.");
                }

                $producto = Producto::lockForUpdate()->findOrFail($row['producto_id']);

                // 🛡️ PROTECCIÓN: Validar que el producto pertenezca a la bodega del usuario
                if (Auth::user()->bodega_id && $producto->bodega_id !== Auth::user()->bodega_id) {
                    throw new \Exception("No tienes permiso para modificar el stock de esta bodega.");
                }

                if ($producto->stock < $row['cantidad']) {
                    throw new \Exception("Stock insuficiente para: {$producto->nombre}");
                }

                Baja::create([
                    'codigo_operacion' => $codigoOperacion,
                    'bodega_id'        => $bodegaId,
                    'producto_id'      => $row['producto_id'],
                    'categoria_id'     => $row['categoria_id'],
                    'departamento_id'  => $row['departamento_id'],
                    'cantidad'         => $row['cantidad'],
                    'motivo'           => strtoupper($validated['motivo']),
                    'responsable'      => strtoupper($validated['responsable']),
                    'estado'           => 1, // Activa
                ]);

                // Descontar stock
                $producto->decrement('stock', $row['cantidad']);
            }

            DB::commit();
            return redirect()->route('bajas')->with('success', 'Bajas registradas correctamente.');

        } catch (\Throwable $e) {

            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /* =============================
     * 📌 HISTORIAL
     * ============================= */
    public function historial(Request $request)
    {
        $user       = Auth::user();
        $bodegaUser = $user->bodega_id;

        $desde = $request->desde;
        $hasta = $request->hasta;

        $bajas = Baja::select(
                'codigo_operacion',
                'motivo',
                'responsable',
                DB::raw('MIN(created_at) as fecha'),
                DB::raw('COUNT(*) as total_items'),
                DB::raw('SUM(cantidad) as total_bajas'),
                DB::raw('MAX(bodega_id) as bodega_id'),
                DB::raw('MAX(departamento_id) as departamento_id'),
                DB::raw('MIN(estado) as estado')
            )
            ->when($bodegaUser, fn($q) => $q->where('bodega_id', $bodegaUser))
            ->when($desde, fn($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('created_at', '<=', $hasta))
            ->groupBy('codigo_operacion', 'motivo', 'responsable')
            ->orderByDesc('fecha')
            ->paginate(15);

        $bodegas       = Bodega::pluck('nombre', 'id');
        $departamentos = Departamento::pluck('nombre', 'id');
        $bodega        = $user->bodega ?? null;
        $esAdmin       = $user->rol === 'admin';

        return view('pages.historial-bajas', compact(
            'bajas', 'bodegas', 'departamentos', 'bodega',
            'desde', 'hasta', 'esAdmin'
        ));
    }

    /* =============================
     * 📌 DETALLE AJAX
     * ============================= */
    public function detalleOperacion($codigo)
    {
        $items = Baja::with(['producto', 'categoria', 'departamento'])
            ->where('codigo_operacion', $codigo)
            ->get()
            ->map(fn($b) => [
                'producto'     => $b->producto?->nombre ?? 'Producto eliminado',
                'categoria'    => $b->categoria?->nombre ?? '—',
                'departamento' => $b->departamento?->nombre ?? '—',
                'cantidad'     => $b->cantidad,
            ]);

        return response()->json($items);
    }

    /* =============================
    * 📌 CANCELAR (destroy)
    * ============================= */
    public function destroy($codigo)
    {
        $user = Auth::user();

        // ✔ Si NO tiene bodega => es admin
        $esAdmin = !$user->bodega_id;

        if (!$esAdmin) {
            return response()->json([
                'success' => false,
                'message' => 'Solo un usuario sin bodega asignada puede cancelar bajas.'
            ], 403);
        }

        DB::beginTransaction();

        try {

            $items = Baja::with('producto')
                ->where('codigo_operacion', $codigo)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la operación.'
                ], 404);
            }

            if ($items->min('estado') == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta operación ya está cancelada.'
                ], 400);
            }

            foreach ($items as $item) {
                // Restaurar stock
                $item->producto->increment('stock', $item->cantidad);

                // Marcar cancelado
                $item->update(['estado' => 0]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Baja cancelada correctamente. Stock restaurado.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar: ' . $e->getMessage()
            ], 500);
        }
    }


    /* =============================
     * 📌 PDF
     * ============================= */
    public function exportarPDF($codigo)
    {
        $items = Baja::with(['producto', 'categoria', 'departamento', 'bodega'])
            ->where('codigo_operacion', $codigo)
            ->get();

        if ($items->isEmpty()) abort(404);

        $primero = $items->first();

        $data = [
            'codigo'       => $codigo,
            'fecha'        => $primero->created_at,
            'bodega'       => $primero->bodega?->nombre,
            'departamento' => $primero->departamento?->nombre,
            'motivo'       => $primero->motivo,
            'responsable'  => $primero->responsable,
            'items'        => $items,
            'totalBajas'   => $items->sum('cantidad'),
            'estado'       => $items->min('estado'),
        ];

        return Pdf::loadView('pdf.baja-pdf', $data)
            ->setPaper('letter')
            ->stream("Baja-{$codigo}.pdf");
    }
}
