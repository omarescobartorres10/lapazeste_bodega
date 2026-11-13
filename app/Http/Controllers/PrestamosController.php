<?php

namespace App\Http\Controllers;

use App\Models\Prestamo;
use App\Models\Producto;
use App\Models\Bodega;
use App\Models\Departamento;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PrestamosController extends Controller
{
    public function index()
    {
        $user   = Auth::user();
        $bodega = $user->bodega ?? null;

        $departamentos = Departamento::where('estado', 1)->get();
        $categorias    = Categoria::where('estado', 1)->get();
        $bodegas       = Bodega::where('estado', 1)->get();

        return view('pages.prestamos', compact(
            'bodega',
            'bodegas',
            'departamentos',
            'categorias'
        ));
    }

    public function store(Request $request)
    {
        $user       = Auth::user();
        $bodegaUser = $user->bodega_id;
        $bodegaForm = $request->bodega_id;

        $request->validate([
            'items'                     => 'required|array|min:1',
            'items.*.producto_id'       => 'required|exists:productos,id',
            'items.*.categoria_id'      => 'required|exists:categorias,id',
            'items.*.cantidad'          => 'required|integer|min:1',
            'items.*.departamento_id'   => 'nullable|exists:departamentos,id',
            'items.*.observacion'       => 'nullable|string|max:255',
            'destino'                   => 'required|string|max:255',
            'recibido_por'              => 'required|string|max:255',
            'entregado_por'             => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $fecha = now()->format('dmY');

            $contador = Prestamo::whereDate('created_at', now()->toDateString())
                ->distinct('codigo_operacion')
                ->count();

            $correlativo     = str_pad($contador + 1, 4, '0', STR_PAD_LEFT);
            $codigoOperacion = "PRE-{$fecha}-LPE-{$correlativo}";

            foreach ($request->items as $item) {

                $producto = Producto::lockForUpdate()->findOrFail($item['producto_id']);

                // 🛡️ PROTECCIÓN: Validar que el producto pertenezca a la bodega del usuario
                if (Auth::user()->bodega_id && $producto->bodega_id !== Auth::user()->bodega_id) {
                    throw new \Exception("No tienes permiso para realizar préstamos de productos de otra bodega.");
                }

                if ($producto->stock < $item['cantidad']) {
                    throw new \Exception("No hay suficiente stock para el producto: {$producto->nombre}");
                }

                $bodegaId = $bodegaUser ?: $bodegaForm ?: $producto->bodega_id;
                if (!$bodegaId) {
                    throw new \Exception('Debe seleccionar una bodega válida.');
                }

                Prestamo::create([
                    'codigo_operacion'  => $codigoOperacion,
                    'bodega_id'         => $bodegaId,
                    'categoria_id'      => $item['categoria_id'],
                    'producto_id'       => $item['producto_id'],
                    'departamento_id'   => $item['departamento_id'] ?? null,
                    'cantidad'          => $item['cantidad'],
                    'cantidad_devuelta' => 0,
                    'destino'           => strtoupper($request->destino),
                    'entregado_por'     => strtoupper($request->entregado_por ?? $user->nombre),
                    'recibido_por'      => strtoupper($request->recibido_por),
                    'observacion'       => strtoupper($item['observacion'] ?? ''),
                    'estado'            => 'prestado',
                ]);

                $producto->decrement('stock', $item['cantidad']);
            }

            DB::commit();

            return back()->with('success', 'Préstamo registrado correctamente.');

        } catch (\Throwable $e) {

            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function historial(Request $request)
    {
        $user       = Auth::user();
        $bodegaUser = $user->bodega_id;

        $desde = $request->desde;
        $hasta = $request->hasta;

        $prestamos = Prestamo::select(
                'codigo_operacion',
                'destino',
                'recibido_por',
                'entregado_por',
                DB::raw('MIN(created_at)        as fecha'),
                DB::raw('COUNT(*)               as total_items'),
                DB::raw('SUM(cantidad)          as total_prestado'),
                DB::raw('SUM(cantidad_devuelta) as total_devuelto'),
                DB::raw('MAX(bodega_id)         as bodega_id'),
                DB::raw('MAX(departamento_id)   as departamento_id'),
                DB::raw('MAX(estado)            as estado')
            )
            ->when($bodegaUser, fn($q) => $q->where('bodega_id', $bodegaUser))
            ->when($desde, fn($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('created_at', '<=', $hasta))
            ->groupBy('codigo_operacion', 'destino', 'recibido_por', 'entregado_por')
            ->orderByDesc('fecha')
            ->paginate(15);

        $bodegas       = Bodega::pluck('nombre', 'id');
        $departamentos = Departamento::pluck('nombre', 'id');

        return view('pages.historial-prestamos', compact(
            'prestamos',
            'bodegas',
            'departamentos',
            'desde',
            'hasta'
        ));
    }

    public function detalleOperacion($codigo)
    {
        $items = Prestamo::with(['producto', 'categoria', 'departamento'])
            ->where('codigo_operacion', $codigo)
            ->get()
            ->map(function ($p) {
                $pendiente = $p->cantidad - $p->cantidad_devuelta;
                return [
                    'id'                 => $p->id,
                    'producto'           => $p->producto->nombre ?? '',
                    'categoria'          => $p->categoria->nombre ?? '',
                    'departamento'       => $p->departamento->nombre ?? '',
                    'cantidad'           => $p->cantidad,
                    'cantidad_devuelta'  => $p->cantidad_devuelta,
                    'pendiente'          => $pendiente,
                    'observacion'        => $p->observacion ?? '',
                ];
            });

        return response()->json($items);
    }

    public function devolverTodo($codigo)
    {
        DB::beginTransaction();

        try {
            $items = Prestamo::with('producto')
                ->where('codigo_operacion', $codigo)
                ->get();

            if ($items->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No se encontró el préstamo.'], 404);
            }

            if ($items->first()->estado === 'cancelado') {
                return response()->json(['success' => false, 'message' => 'Este préstamo está cancelado.'], 400);
            }

            foreach ($items as $item) {
                $pendiente = $item->cantidad - $item->cantidad_devuelta;
                if ($pendiente > 0) {
                    $item->producto->increment('stock', $pendiente);
                    $item->cantidad_devuelta = $item->cantidad;
                    $item->save();
                }
            }

            Prestamo::where('codigo_operacion', $codigo)
                ->update(['estado' => 'devuelto', 'fecha_devolucion' => now()]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Todos los productos fueron devueltos correctamente.']);

        } catch (\Throwable $e) {

            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al devolver: ' . $e->getMessage()], 500);
        }
    }

    public function devolverParcial(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:prestamos,id',
            'items.*.devolver' => 'nullable|integer|min:0',
        ]);

        $items = collect($request->items)->map(function ($item) {
            $item['devolver'] = intval($item['devolver'] ?? 0);
            return $item;
        });

        DB::beginTransaction();

        try {
            foreach ($items as $item) {
                $prestamo = Prestamo::with('producto')->lockForUpdate()->findOrFail($item['id']);
                
                // 🛡️ PROTECCIÓN 1: Evitar devolver préstamos ya finalizados
                if ($prestamo->estado === 'devuelto' || $prestamo->estado === 'cancelado') {
                    throw new \Exception("El préstamo del producto {$prestamo->producto->nombre} ya fue devuelto o cancelado previamente.");
                }

                $pendiente = $prestamo->cantidad - $prestamo->cantidad_devuelta;

                // 🛡️ PROTECCIÓN 2: Validar que no devuelvan más de lo pendiente
                if ($item['devolver'] > $pendiente) {
                    throw new \Exception("La cantidad a devolver supera lo pendiente del producto {$prestamo->producto->nombre}");
                }

                if ($item['devolver'] > 0) {
                    $prestamo->increment('cantidad_devuelta', $item['devolver']);
                    $prestamo->producto->increment('stock', $item['devolver']);
                }

                // Actualizar estado
                if ($prestamo->cantidad_devuelta == $prestamo->cantidad) {
                    $prestamo->estado = 'devuelto';
                    $prestamo->fecha_devolucion = now();
                    $prestamo->save();
                } else {
                    $prestamo->estado = 'parcial';
                    $prestamo->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Throwable $e) {

            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancelarOperacion($codigo)
    {
        DB::beginTransaction();

        try {
            $items = Prestamo::with('producto')
                ->where('codigo_operacion', $codigo)
                ->get();

            if ($items->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No se encontró la operación.'], 404);
            }

            if ($items->first()->estado === 'cancelado') {
                return response()->json(['success' => false, 'message' => 'Esta operación ya está cancelada.'], 400);
            }

            foreach ($items as $item) {
                $pendiente = $item->cantidad - $item->cantidad_devuelta;
                if ($pendiente > 0) {
                    $item->producto->increment('stock', $pendiente);
                }
                $item->estado = 'cancelado';
                $item->save();
            }

            Prestamo::where('codigo_operacion', $codigo)
                ->update(['estado' => 'cancelado', 'fecha_devolucion' => now()]);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Préstamo cancelado y stock restaurado.']);

        } catch (\Throwable $e) {

            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error al cancelar: ' . $e->getMessage()], 500);
        }
    }

    public function productosPorBodega($bodegaId)
    {
        try {
            $productos = Producto::with(['categoria', 'departamento'])
                ->where('bodega_id', $bodegaId)
                ->where('estado', 1)
                ->get()
                ->map(fn($p) => [
                    'id'                 => $p->id,
                    'codigo'             => $p->codigo,
                    'nombre'             => $p->nombre,
                    'stock'              => $p->stock,
                    'categoria_id'       => $p->categoria_id,
                    'departamento_id'    => $p->departamento_id,
                    'categoria_nombre'   => $p->categoria->nombre ?? '',
                    'departamento_nombre'=> $p->departamento->nombre ?? '',
                ]);

            return response()->json($productos);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function exportarPDF($codigo)
    {
        $items = Prestamo::with(['producto', 'categoria', 'departamento', 'bodega'])
            ->where('codigo_operacion', $codigo)
            ->get();

        if ($items->isEmpty()) {
            abort(404, "El préstamo no existe.");
        }

        $primero = $items->first();

        $totalPrestado = $items->sum('cantidad');
        $totalDevuelto = $items->sum('cantidad_devuelta');
        $pendiente     = $totalPrestado - $totalDevuelto;

        $data = [
            'codigo'        => $codigo,
            'fecha'         => $primero->created_at,
            'bodega'        => $primero->bodega?->nombre,
            'departamento'  => $primero->departamento?->nombre,
            'destino'       => $primero->destino,
            'entregado'     => $primero->entregado_por,
            'recibido'      => $primero->recibido_por,
            'items'         => $items,
            'estado'        => $primero->estado,
            'totalPrestado' => $totalPrestado,
            'totalDevuelto' => $totalDevuelto,
            'pendiente'     => $pendiente,
        ];

        return Pdf::loadView('pdf.prestamo-pdf', $data)
            ->setPaper('letter')
            ->stream("Prestamo-{$codigo}.pdf");
    }
}