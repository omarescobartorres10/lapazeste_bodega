<?php

namespace App\Http\Controllers;

use App\Models\Baja;
use App\Models\Producto;
use App\Models\Entrada;
use App\Models\Salida;
use App\Models\Prestamo;
use App\Models\Bodega;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $mesActual = strtoupper(Carbon::now()->locale('es')->isoFormat('MMMM'));

        $bodegas = Bodega::where('estado', 1)->get();
        $bodegaSeleccionada = $request->get('bodega_id');

        /* ============================================================
           1. CONTADORES REALES (basados en código_operacion)
        ============================================================= */

        // Entradas del mes (contar operaciones únicas)
        $entradasMes = Entrada::query()
            ->select('codigo_operacion')
            ->whereNotNull('codigo_operacion')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when($bodegaSeleccionada, fn($q) => $q->where('bodega_id', $bodegaSeleccionada))
            ->distinct('codigo_operacion')
            ->count('codigo_operacion');

        // Salidas del mes
        $salidasMes = Salida::query()
            ->select('codigo_operacion')
            ->whereNotNull('codigo_operacion')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when($bodegaSeleccionada, fn($q) => $q->where('bodega_id', $bodegaSeleccionada))
            ->distinct('codigo_operacion')
            ->count('codigo_operacion');

        // Bajas del mes
        $bajasMes = Baja::query()
            ->select('codigo_operacion')
            ->whereNotNull('codigo_operacion')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when($bodegaSeleccionada, fn($q) => $q->where('bodega_id', $bodegaSeleccionada))
            ->distinct('codigo_operacion')
            ->count('codigo_operacion');

        // Préstamos del mes (solo creación)
        $prestamosMes = Prestamo::query()
            ->select('codigo_operacion')
            ->whereNotNull('codigo_operacion')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->when($bodegaSeleccionada, fn($q) => $q->where('bodega_id', $bodegaSeleccionada))
            ->distinct('codigo_operacion')
            ->count('codigo_operacion');

        // Préstamos activos (prestado)
        $prestamosActivos = Prestamo::query()
            ->where('estado', 'prestado')
            ->when($bodegaSeleccionada, fn($q) => $q->where('bodega_id', $bodegaSeleccionada))
            ->distinct('codigo_operacion')
            ->count('codigo_operacion');

        /* ============================================================
           2. PRODUCTOS (stock, bajo stock, cantidad total)
        ============================================================= */

        $productoQuery = Producto::query()->where('estado', 1);

        if ($bodegaSeleccionada) {
            $productoQuery->where('bodega_id', $bodegaSeleccionada);
        }

        $totalProductos = $productoQuery->count();

        $productosBajoStock = $productoQuery
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        $hayProductos = $productosBajoStock->count();

        /* ============================================================
           3. PRODUCTO + solicitado / + entrada
        ============================================================= */

        $productoMasSalidas = Salida::query()
            ->select('productos.nombre', DB::raw('SUM(salidas.cantidad) as total_salidas'))
            ->join('productos', 'salidas.producto_id', '=', 'productos.id')
            ->where('salidas.estado', 1)
            ->when($bodegaSeleccionada, fn($q) => $q->where('salidas.bodega_id', $bodegaSeleccionada))
            ->groupBy('productos.nombre')
            ->orderByDesc('total_salidas')
            ->first();

        $productoMasEntradas = Entrada::query()
            ->select('productos.nombre', DB::raw('SUM(entradas.cantidad) as total_entradas'))
            ->join('productos', 'entradas.producto_id', '=', 'productos.id')
            ->where('entradas.estado', 1)
            ->when($bodegaSeleccionada, fn($q) => $q->where('entradas.bodega_id', $bodegaSeleccionada))
            ->groupBy('productos.nombre')
            ->orderByDesc('total_entradas')
            ->first();

        /* ============================================================
           4. Top 5 productos entrada/salida
        ============================================================= */

        $topProductosSalidas = Salida::query()
            ->join('productos', 'salidas.producto_id', '=', 'productos.id')
            ->select('productos.nombre', DB::raw('SUM(salidas.cantidad) as total'))
            ->where('salidas.estado', 1)
            ->when($bodegaSeleccionada, fn($q) => $q->where('salidas.bodega_id', $bodegaSeleccionada))
            ->groupBy('productos.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topProductosEntradas = Entrada::query()
            ->join('productos', 'entradas.producto_id', '=', 'productos.id')
            ->select('productos.nombre', DB::raw('SUM(entradas.cantidad) as total'))
            ->where('entradas.estado', 1)
            ->when($bodegaSeleccionada, fn($q) => $q->where('entradas.bodega_id', $bodegaSeleccionada))
            ->groupBy('productos.nombre')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        /* ============================================================
           5. ACTIVIDAD DIARIA (ENTRADAS / SALIDAS / PRESTAMOS)
        ============================================================= */

        $inicioMes = now()->startOfMonth();
        $finMes = now()->endOfMonth();

        $diasDelMes = [];
        $valoresEntradas = [];
        $valoresSalidas = [];
        $valoresPrestamos = [];

        // Entradas por día (contando operaciones únicas)
        $entradasPorDia = Entrada::query()
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('count(distinct codigo_operacion) as total'))
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->when($bodegaSeleccionada, fn($q) => $q->where('bodega_id', $bodegaSeleccionada))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'fecha');

        // Salidas por día
        $salidasPorDia = Salida::query()
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('count(distinct codigo_operacion) as total'))
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->when($bodegaSeleccionada, fn($q) => $q->where('bodega_id', $bodegaSeleccionada))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'fecha');

        // Préstamos por día
        $prestamosPorDia = Prestamo::query()
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('count(distinct codigo_operacion) as total'))
            ->whereBetween('created_at', [$inicioMes, $finMes])
            ->when($bodegaSeleccionada, fn($q) => $q->where('bodega_id', $bodegaSeleccionada))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('total', 'fecha');

        // Rellenar día por día
        for ($fecha = $inicioMes->copy(); $fecha <= $finMes; $fecha->addDay()) {
            $dia = $fecha->format('Y-m-d');

            $diasDelMes[] = $dia;
            $valoresEntradas[] = $entradasPorDia[$dia] ?? 0;
            $valoresSalidas[] = $salidasPorDia[$dia] ?? 0;
            $valoresPrestamos[] = $prestamosPorDia[$dia] ?? 0;
        }

        return view('panel.index', compact(
            'bodegaSeleccionada',
            'bodegas',
            'totalProductos',
            'entradasMes',
            'salidasMes',
            'bajasMes',
            'prestamosMes',
            'prestamosActivos',
            'productosBajoStock',
            'hayProductos',
            'productoMasSalidas',
            'productoMasEntradas',
            'topProductosSalidas',
            'topProductosEntradas',
            'diasDelMes',
            'valoresEntradas',
            'valoresSalidas',
            'valoresPrestamos',
            'mesActual',
        ));
    }
}
