<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Departamento;
use App\Models\Salida;
use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class SalidaController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $bodega = $user->bodega ?? null;

        $categorias    = Categoria::where('estado', 1)->get();
        $departamentos = Departamento::where('estado', 1)->get();
        $bodegas       = Bodega::where('estado', 1)->get();

        $productos = $bodega
            ? Producto::where('bodega_id', $bodega->id)->where('estado', 1)->get()
            : [];

        return view('pages.salidas', compact(
            'productos',
            'categorias',
            'departamentos',
            'bodegas',
            'bodega'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bodega_id'   => 'nullable|exists:bodegas,id',
            'productos'   => 'required|array|min:1',

            'productos.*.producto_id'     => 'required|exists:productos,id',
            'productos.*.categoria_id'    => 'required|exists:categorias,id',
            'productos.*.departamento_id' => 'required|exists:departamentos,id',
            'productos.*.cantidad'        => 'required|integer|min:1',
            'productos.*.observacion'     => 'nullable|string|max:255',

            'destino'      => 'required|string|max:255',
            'recibido_por' => 'required|string|max:255',
            'entregado_por'=> 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $fecha = now()->format('dmY');

            $contador = Salida::whereDate('created_at', now()->toDateString())
                ->distinct('codigo_operacion')
                ->count();

            $correlativo = str_pad($contador + 1, 4, '0', STR_PAD_LEFT);

            $codigoOperacion = "SAL-{$fecha}-LPE-{$correlativo}";

            foreach ($validated['productos'] as $row) {

                $producto = Producto::lockForUpdate()
                    ->findOrFail($row['producto_id']);

                if ($producto->stock < $row['cantidad']) {
                    DB::rollBack();
                    return back()->with('error', "No hay suficiente stock para el producto: {$producto->nombre}");
                }

                Salida::create([
                    'producto_id'     => $row['producto_id'],
                    'categoria_id'    => $row['categoria_id'],
                    'departamento_id' => $row['departamento_id'],
                    'cantidad'        => $row['cantidad'],
                    'destino'         => strtoupper($validated['destino']),
                    'recibido_por'    => strtoupper($validated['recibido_por']),
                    'entregado_por'   => strtoupper($validated['entregado_por'] ?? Auth::user()->nombre),
                    'observacion'     => strtoupper($row['observacion'] ?? ''),
                    'bodega_id'       => $validated['bodega_id'] ?? $producto->bodega_id,
                    'estado'          => 1,
                    'codigo_operacion'=> $codigoOperacion,
                ]);

                $producto->decrement('stock', $row['cantidad']);
            }

            DB::commit();
                    return redirect()->route('salidas')
            ->with('success', 'Salidas registradas correctamente.')
            ->with('pdf_codigo', $codigoOperacion);

        } catch (\Throwable $e) {

            DB::rollBack();
            return back()->with('error', 'Error al registrar salidas: ' . $e->getMessage());
        }
    }

    public function historial(Request $request)
    {
        $user       = Auth::user();
        $bodegaUser = $user->bodega_id;
        $bodega     = $user->bodega ?? null;

        $desde = $request->desde;
        $hasta = $request->hasta;

        $salidas = Salida::select(
                'codigo_operacion',
                'destino',
                'recibido_por',
                'entregado_por',
                DB::raw('MIN(created_at) as fecha'),
                DB::raw('COUNT(*) as total_productos'),
                DB::raw('MAX(bodega_id) as bodega_id'),
                DB::raw('MAX(departamento_id) as departamento_id'),
                DB::raw('MIN(estado) as estado')  // 0 = cancelada si algún item lo está
            )
            ->when($bodegaUser, fn($q) => $q->where('bodega_id', $bodegaUser))
            ->when($desde, fn($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('created_at', '<=', $hasta))
            ->groupBy('codigo_operacion', 'destino', 'recibido_por', 'entregado_por')
            ->orderByDesc('fecha')
            ->paginate(15);

        $bodegas = Bodega::pluck('nombre', 'id');
        $departamentos = Departamento::pluck('nombre', 'id');

        return view('pages.historial-salidas', compact(
            'salidas', 'bodegas', 'departamentos', 'desde', 'hasta', 'bodega'
        ));
    }

    public function detalle($codigo)
    {
        $items = Salida::with(['producto', 'categoria', 'departamento'])
            ->where('codigo_operacion', $codigo)
            ->get();

        return response()->json($items);
    }

    public function exportarPDF($codigo)
    {
        $items = Salida::with(['producto', 'categoria', 'departamento', 'bodega'])
            ->where('codigo_operacion', $codigo)
            ->get();

        if ($items->isEmpty()) {
            abort(404, "La salida no existe.");
        }

        $salida = $items->first();

        $estado = $items->min('estado');

        $data = [
            'codigo'       => $codigo,
            'fecha'        => $salida->created_at,
            'bodega'       => $salida->bodega?->nombre,
            'departamento' => $salida->departamento?->nombre,
            'destino'      => $salida->destino,
            'entregado'    => $salida->entregado_por,
            'recibido'     => $salida->recibido_por,
            'items'        => $items,
            'estado'       => $estado,
        ];

        return Pdf::loadView('pdf.salida-pdf', $data)
            ->setPaper('letter')
            ->stream("Salida-{$codigo}.pdf");
    }

    public function cancelar($codigo)
    {
        $items = Salida::where('codigo_operacion', $codigo)->get();

        if ($items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la salida.'
            ], 404);
        }

        // 🛡️ Evitar que se cancele dos veces
        if ($items->first()->estado == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Esta salida ya fue cancelada previamente. El stock ya fue devuelto.'
            ], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($items as $item) {
                Producto::where('id', $item->producto_id)
                    ->increment('stock', $item->cantidad);

                $item->estado = 0;
                $item->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Salida cancelada correctamente.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar: ' . $e->getMessage()
            ], 500);
        }
    }
}
