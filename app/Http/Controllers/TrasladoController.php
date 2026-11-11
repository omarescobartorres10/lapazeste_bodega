<?php

namespace App\Http\Controllers;

use App\Models\Traslado;
use App\Models\Bodega;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TrasladoController extends Controller
{
    public function historial(Request $request)
    {
        $user = Auth::user();
        $bodegaUser = $user->bodega_id;

        $desde = $request->desde;
        $hasta = $request->hasta;

        $traslados = Traslado::select(
                'codigo_operacion',
                DB::raw('MAX(solicitud_id) as solicitud_id'),
                DB::raw('MIN(created_at) as fecha'),
                DB::raw('COUNT(*) as total_items'),
                DB::raw('SUM(cantidad) as total_cantidad'),
                DB::raw('MAX(bodega_origen_id) as bodega_origen_id'),
                DB::raw('MAX(bodega_destino_id) as bodega_destino_id'),
                DB::raw('MIN(estado) as estado')
            )
            ->when($bodegaUser, function ($q) use ($bodegaUser) {
                $q->where(function($qq) use ($bodegaUser){
                    $qq->where('bodega_origen_id', $bodegaUser)
                       ->orWhere('bodega_destino_id', $bodegaUser);
                });
            })
            ->when($desde, fn($q) => $q->whereDate('created_at', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('created_at', '<=', $hasta))
            ->groupBy('codigo_operacion')
            ->orderByDesc('fecha')
            ->paginate(15);

        $bodegas = Bodega::pluck('nombre', 'id');

        return view('pages.historial-traslados', compact('traslados', 'bodegas', 'desde', 'hasta'));
    }

    public function detalle($codigo)
    {
        $items = Traslado::with(['productoOrigen','productoDestino','bodegaOrigen','bodegaDestino'])
            ->where('codigo_operacion', $codigo)
            ->get()
            ->map(function($t){
                return [
                    'producto_origen'  => $t->productoOrigen?->nombre,
                    'producto_destino' => $t->productoDestino?->nombre,
                    'cantidad'         => $t->cantidad,
                    'bodega_origen'    => $t->bodegaOrigen?->nombre,
                    'bodega_destino'   => $t->bodegaDestino?->nombre,
                    'observacion'      => $t->observacion,
                    'fecha'            => $t->created_at?->format('d/m/Y H:i'),
                ];
            });

        return response()->json($items);
    }

    // ✅ PDF igual que salidas, pero basado en SOLICITUD guardada
    public function exportarPDF($codigo)
    {
        $traslado = Traslado::where('codigo_operacion', $codigo)
            ->orderByDesc('id')
            ->first();

        if (!$traslado) {
            abort(404, 'Traslado no encontrado.');
        }

        if (!$traslado->solicitud_id) {
            abort(404, 'Este traslado no tiene solicitud asociada (sin PDF).');
        }

        $solicitud = Solicitud::findOrFail($traslado->solicitud_id);

        $fileName = "solicitud-{$solicitud->id}.pdf";
        $relativePath = "solicitudes/{$fileName}";

        if (!Storage::disk('public')->exists($relativePath)) {
            abort(404, 'PDF no encontrado en storage.');
        }

        $fullPath = storage_path("app/public/{$relativePath}");

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}