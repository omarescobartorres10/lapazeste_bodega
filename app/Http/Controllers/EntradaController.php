<?php

namespace App\Http\Controllers;

use App\Models\Entrada;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Departamento;
use App\Models\Bodega;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class EntradaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $bodega = $user->bodega ?? null;

        $departamentos = Departamento::where('estado', 1)->get();
        $categorias = Categoria::where('estado', 1)->get();
        $bodegas = Bodega::where('estado', 1)->get();

        $productos = Producto::where('estado', 1)
            ->with(['categoria', 'departamento'])
            ->when($bodega, fn($q) => $q->where('bodega_id', $bodega->id))
            ->get();

        $entradas = Entrada::with(['producto', 'categoria'])
            ->when($bodega, fn($q) => $q->where('bodega_id', $bodega->id))
            ->orderByDesc('id')
            ->paginate(10);

        return view('pages.entradas', compact(
            'bodega',
            'bodegas',
            'departamentos',
            'categorias',
            'productos',
            'entradas'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $bodegaUser = $user->bodega_id;
        $bodegaForm = $request->bodega_id;

        $validated = $request->validate([
            'recibido_por' => 'required|string|max:255',
            'entregado_por' => 'required|string|max:255',
            'productos' => 'required|array|min:1',
            'productos.*.departamento_id' => 'required|exists:departamentos,id',
            'productos.*.categoria_id' => 'required|exists:categorias,id',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.observacion' => 'nullable|string|max:255',
            'numero_factura' => 'required|string|max:255',
            'numero_orden_compra' => 'required|string|max:255',
            'factura_pdf' => 'nullable|file|mimes:pdf|max:10240',
        ], [
            'factura_pdf.max' => 'El PDF no debe pesar más de 10 MB.',
            'factura_pdf.required' => 'Debe adjuntar la factura en PDF.',
            'factura_pdf.mimes' => 'La factura debe ser un archivo PDF.',
        ]);

        DB::beginTransaction();

        try {
            $facturaPdf = $request->file('factura_pdf');
            $pdfPath = $facturaPdf?->store('facturas', 'public');

            $fecha = now()->format('dmY');

            $contador = Entrada::whereDate('created_at', now()->toDateString())
                ->distinct('codigo_operacion')
                ->count();

            $correlativo = str_pad($contador + 1, 4, '0', STR_PAD_LEFT);
            $codigoOperacion = "ENT-{$fecha}-LPE-{$correlativo}";

            foreach ($validated['productos'] as $item) {
                $bodegaId = $bodegaUser ?: $bodegaForm;

                if (!$bodegaId) {
                    throw new \Exception('Debe seleccionar una bodega válida.');
                }

                Entrada::create([
                    'departamento_id' => $item['departamento_id'],
                    'categoria_id' => $item['categoria_id'],
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'recibido_por' => strtoupper($validated['recibido_por']),
                    'entregado_por' => strtoupper($validated['entregado_por']),
                    'observacion' => strtoupper($item['observacion'] ?? ''),
                    'estado' => 1,
                    'bodega_id' => $bodegaId,
                    'codigo_operacion' => $codigoOperacion,
                    'numero_factura' => $validated['numero_factura'],
                    'numero_orden_compra' => $validated['numero_orden_compra'],
                    'factura_pdf' => $pdfPath,
                ]);

                Producto::findOrFail($item['producto_id'])
                    ->increment('stock', $item['cantidad']);
            }

            DB::commit();

            return redirect()->route('entradas')
                ->with('success', '✅ Entradas registradas correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', '⚠️ Error al registrar entrada: ' . $e->getMessage());
        }
    }

    public function getProductosPorBodega($bodegaId)
    {
        $productos = Producto::where('bodega_id', $bodegaId)
            ->where('estado', 1)
            ->with(['categoria:id,nombre', 'departamento:id,nombre'])
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'codigo' => $p->codigo,
                'nombre' => $p->nombre,
                'stock' => $p->stock,
                'categoria_id' => $p->categoria_id,
                'departamento_id' => $p->departamento_id,
                'categoria_nombre' => $p->categoria->nombre ?? '',
                'departamento_nombre' => $p->departamento->nombre ?? '',
            ]);

        return response()->json($productos);
    }

    public function crearEntrada(Request $request)
    {
        try {
            $validated = $request->validate([
                'codigo' => 'required|string|max:50',
                'nombre' => 'required|string|max:255',
                'categoria_id' => 'required|exists:categorias,id',
                'departamento_id' => 'required|exists:departamentos,id',
                'bodega_id' => 'required|exists:bodegas,id',
                'tipo_producto_id' => 'required|exists:tipos_productos,id',
                'unidades_id' => 'required|exists:unidades,id',
                'descripcion' => 'nullable|string|max:255',
            ]);

            $existe = Producto::where('codigo', strtoupper($validated['codigo']))
                ->where('bodega_id', $validated['bodega_id'])
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un producto con ese código en esta bodega.'
                ]);
            }

            $producto = Producto::create([
                'codigo' => strtoupper($validated['codigo']),
                'nombre' => strtoupper($validated['nombre']),
                'categoria_id' => $validated['categoria_id'],
                'departamento_id' => $validated['departamento_id'],
                'bodega_id' => $validated['bodega_id'],
                'tipo_producto_id' => $validated['tipo_producto_id'],
                'unidades_id' => $validated['unidades_id'],
                'descripcion' => strtoupper($validated['descripcion'] ?? ''),
                'stock' => 0,
                'estado' => 1,
            ]);

            $producto->load(['categoria', 'departamento']);

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado correctamente (Stock = 0).',
                'producto' => [
                    'id' => $producto->id,
                    'codigo' => $producto->codigo,
                    'nombre' => $producto->nombre,
                    'stock' => $producto->stock,
                    'categoria_id' => $producto->categoria_id,
                    'departamento_id' => $producto->departamento_id,
                    'categoria_nombre' => $producto->categoria->nombre ?? '',
                    'departamento_nombre' => $producto->departamento->nombre ?? '',
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el producto: ' . $e->getMessage(),
            ]);
        }
    }

    public function historial(Request $request)
    {
        $user = Auth::user();
        $bodegaUser = $user->bodega_id;

        $desde = $request->desde;
        $hasta = $request->hasta;

        $entradas = Entrada::select(
                'codigo_operacion',
                'recibido_por',
                'entregado_por',
                DB::raw('MIN(created_at) as fecha'),
                DB::raw('COUNT(*) as total_productos'),
                DB::raw('MAX(bodega_id) as bodega_id'),
                DB::raw('MAX(departamento_id) as departamento_id'),
                DB::raw('MAX(estado) as estado'),
                DB::raw('MAX(factura_pdf) as factura_pdf')
            )
            ->when($bodegaUser, fn($q) => $q->where('bodega_id', $bodegaUser))
            ->when($desde, fn($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('created_at', '<=', $hasta))
            ->groupBy('codigo_operacion', 'recibido_por', 'entregado_por')
            ->orderByDesc('fecha')
            ->paginate(15);

        $bodegas = Bodega::pluck('nombre', 'id');
        $departamentos = Departamento::pluck('nombre', 'id');

        return view('pages.historial-entradas', compact(
            'entradas',
            'bodegas',
            'departamentos',
            'desde',
            'hasta'
        ));
    }

    public function verFactura($archivo)
    {
        $path = 'facturas/' . $archivo;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Factura no encontrada.');
        }

        return response()->file(storage_path('app/public/' . $path));
    }

    public function detalleEntrada($codigo)
    {
        $items = Entrada::with(['producto', 'categoria', 'departamento'])
            ->where('codigo_operacion', $codigo)
            ->get();

        return response()->json($items);
    }

    public function exportarPDF($codigo)
    {
        $items = Entrada::with(['producto', 'categoria', 'departamento', 'bodega'])
            ->where('codigo_operacion', $codigo)
            ->get();

        if ($items->isEmpty()) {
            abort(404, "La entrada no existe.");
        }

        $entrada = $items->first();
        $estado = $items->min('estado');

        $data = [
            'codigo'       => $codigo,
            'fecha'        => $entrada->created_at,
            'bodega'       => $entrada->bodega?->nombre,
            'departamento' => $entrada->departamento?->nombre,
            'entregado'    => $entrada->entregado_por,
            'recibido'     => $entrada->recibido_por,
            'items'        => $items,
            'estado'       => $estado
        ];

        return Pdf::loadView('pdf.entrada-pdf', $data)
            ->setPaper('letter')
            ->stream("Entrada-{$codigo}.pdf");
    }

    public function destroy($codigo)
    {
        DB::beginTransaction();

        try {
            $items = Entrada::where('codigo_operacion', $codigo)->lockForUpdate()->get();

            if ($items->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la entrada.'
                ]);
            }

            foreach ($items as $item) {
                if (!$item->estado) {
                    continue;
                }
                $producto = Producto::whereKey($item->producto_id)->lockForUpdate()->firstOrFail();
                if ($producto->stock < $item->cantidad) {
                    throw new \RuntimeException('No se puede cancelar: parte de las existencias ya fue utilizada.');
                }
                $producto->decrement('stock', $item->cantidad);

                $item->estado = 0;
                $item->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Entrada cancelada correctamente.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar: ' . $e->getMessage()
            ]);
        }
    }
}
