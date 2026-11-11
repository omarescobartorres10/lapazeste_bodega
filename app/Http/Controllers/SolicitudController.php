<?php

namespace App\Http\Controllers;

use App\Models\Bodega;
use App\Models\Solicitud;
use App\Models\SolicitudDetalle;
use App\Models\Producto;
use App\Models\Solicitante;

use App\Models\Prestamo; // ✅
use App\Models\Salida;   // ✅

use App\Models\Traslado;
use App\Models\Entrada; // si no lo habías importado

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\SolicitudEnviada;
use App\Mail\SolicitudRechazada;
use Illuminate\Support\Facades\Mail;

class SolicitudController extends Controller
{
    public function login()
    {
        if (session()->has('solicitante_id')) {
            if (session()->has('tipo_movimiento')) {
                return redirect()->route('solicitud.form');
            }
            return redirect()->route('solicitud.tipo');
        }

        return view('public.login-solicitante');
    }

    public function autenticar(Request $request)
    {
        $request->validate([
            'dui' => 'required',
            'codigo' => 'required'
        ]);

        $solicitante = Solicitante::where('dui', $request->dui)
            ->where('codigo_marcador', $request->codigo)
            ->where('estado', 'activo')
            ->first();

        if (!$solicitante) {
            return back()->with('error', 'DUI o código de marcador incorrecto.');
        }

        session(['solicitante_id' => $solicitante->id]);
        $request->session()->regenerate();
        session(['last_activity' => time()]);
        session()->forget('tipo_movimiento');

        return redirect()->route('solicitud.tipo');
    }

    public function logout()
    {
        \Illuminate\Support\Facades\Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()
            ->route('solicitud.login')
            ->with('success', 'Sesión cerrada. Debe validar nuevamente para continuar.');
    }

    public function tipoMovimiento()
    {
        if (!session()->has('solicitante_id')) {
            return redirect()->route('solicitud.login')
                ->with('error', 'Debe identificarse para continuar.');
        }

        return view('public.tipo-movimiento');
    }

    public function guardarTipoMovimiento(Request $request)
    {
        if (!session()->has('solicitante_id')) {
            return redirect()->route('solicitud.login')
                ->with('error', 'Debe identificarse para continuar.');
        }

        $request->validate([
            'tipo_movimiento' => 'required|in:PRESTAMO,SALIDA,TRASLADO'
        ]);

        session(['tipo_movimiento' => $request->tipo_movimiento]);

        return redirect()->route('solicitud.form');
    }

    public function bodegasDestino($bodegaOrigenId)
    {
        $bodegas = Bodega::where('estado', 'Activo')
            ->where('id', '!=', $bodegaOrigenId)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return response()->json($bodegas);
    }

    public function formularioPublicoProtegido()
    {
        if (!session()->has('solicitante_id')) {
            return redirect()->route('solicitud.login')
                ->with('error', 'Debe identificarse para continuar.');
        }

        if (!session()->has('tipo_movimiento')) {
            return redirect()->route('solicitud.tipo');
        }

        $tipoMovimiento = session('tipo_movimiento');
        $solicitante = Solicitante::findOrFail(session('solicitante_id'));

        $bodegaIds = Producto::where('departamento_id', $solicitante->departamento_id)
            ->where('estado', 1)
            ->pluck('bodega_id')
            ->unique();

        $bodegas = Bodega::whereIn('id', $bodegaIds)
            ->where('estado', 'Activo')
            ->get();

        return view('public.solicitud-productos', compact('bodegas', 'solicitante', 'tipoMovimiento'));
    }

    public function departamentosPorBodega($bodegaId)
    {
        $query = DB::table('productos')
            ->join('departamentos', 'departamentos.id', '=', 'productos.departamento_id')
            ->where('productos.bodega_id', $bodegaId)
            ->where('productos.estado', 1)
            ->where('departamentos.estado', 'Activo');

        if (session()->has('solicitante_id')) {
            $solicitante = Solicitante::find(session('solicitante_id'));
            if ($solicitante) {
                $query->where('departamentos.id', $solicitante->departamento_id);
            }
        }

        $departamentos = $query
            ->select('departamentos.id', 'departamentos.nombre')
            ->groupBy('departamentos.id', 'departamentos.nombre')
            ->get();

        return response()->json($departamentos);
    }

    public function productosPorDepto($bodegaId, $deptoId)
    {
        $productos = Producto::where('bodega_id', $bodegaId)
            ->where('departamento_id', $deptoId)
            ->where('estado', 1)
            ->select('id', 'nombre', 'stock')
            ->orderBy('nombre')
            ->get();

        return response()->json($productos);
    }

    private function generarCodigo()
    {
        $ultimo = Solicitud::orderBy('id', 'desc')->first();
        $num = $ultimo ? $ultimo->id + 1 : 1;

        return "SOL-" . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    public function registrarPublico(Request $request)
    {
        if (!session()->has('solicitante_id')) {
            return response()->json(['ok' => false, 'error' => 'Su sesión expiró. Ingrese nuevamente.'], 401);
        }
        $request->validate([
            'solicitante' => 'required',
            'telefono' => 'required',
            'correo' => 'required|email',
            'bodega_id' => 'required|exists:bodegas,id',
            'departamento_id' => 'required|exists:departamentos,id',
            'tipo_movimiento' => 'required|in:PRESTAMO,SALIDA,TRASLADO',

            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.observacion' => 'nullable|string',

            'bodega_destino_id' => 'nullable|exists:bodegas,id',
            'fecha_requerida' => 'nullable|date|after_or_equal:today',
            'fecha_devolucion' => 'nullable|date|after_or_equal:today',
        ]);

        if ($request->tipo_movimiento === 'PRESTAMO') {

            $request->validate([
                'fecha_requerida' => 'required|date|after_or_equal:today',
                'fecha_devolucion' => 'required|date|after_or_equal:fecha_requerida',
            ]);

            $request->merge(['bodega_destino_id' => null]);

        } else {

            $request->validate([
                'fecha_requerida' => 'required|date|after_or_equal:today',
            ]);

            $request->merge(['fecha_devolucion' => null]);

            if ($request->tipo_movimiento === 'TRASLADO') {
                $request->validate([
                    'bodega_destino_id' => 'required|different:bodega_id',
                ]);
            } else {
                $request->merge(['bodega_destino_id' => null]);
            }
        }

        DB::beginTransaction();

        try {
            $codigo = $this->generarCodigo();

            $solicitud = Solicitud::create([
                'codigo' => $codigo,
                'solicitante' => $request->solicitante,
                'telefono' => $request->telefono,
                'correo' => $request->correo,
                'fecha_requerida' => $request->fecha_requerida,
                'fecha_devolucion' => $request->fecha_devolucion,
                'departamento_solicitante' => $request->departamento_id,
                'bodega_id' => $request->bodega_id,
                'tipo_movimiento' => $request->tipo_movimiento,
                'bodega_destino_id' => $request->bodega_destino_id,
                'estado' => 'pendiente'
            ]);

            foreach ($request->productos as $prod) {
                SolicitudDetalle::create([
                    'solicitud_id' => $solicitud->id,
                    'producto_id' => $prod['producto_id'],
                    'cantidad' => $prod['cantidad'],
                    'observacion' => $prod['observacion'] ?? null,
                ]);
            }

            $solicitud->load(['bodega', 'bodegaDestino', 'departamento', 'detalles.producto']);

            $pdf = Pdf::loadView('pdf.solicitud', [
                'solicitud' => $solicitud,
            ])->setPaper('letter', 'portrait');

            $fileName = "solicitud-$solicitud->id.pdf";
            if (!Storage::disk('local')->put("solicitudes/$fileName", $pdf->output())) {
                throw new \RuntimeException('No se pudo guardar el PDF de la solicitud.');
            }
            DB::commit();

            try {
                Mail::to(['Gilbertovela2015@gmail.com','sidig.lapazeste@gmail.com'])
                    ->send(new SolicitudEnviada($solicitud, Storage::disk('local')->path("solicitudes/$fileName")));
            } catch (\Throwable $e) {
                \Log::error('Error enviando correo admin: ' . $e->getMessage());
            }

            return response()->json([
                'ok' => true,
                'codigo' => $codigo,
                'pdf_url' => \Illuminate\Support\Facades\URL::temporarySignedRoute('solicitud.pdf', now()->addMinutes(30), ['id' => $solicitud->id])
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function descargarPdf($id)
    {
        abort_unless(ctype_digit((string) $id), 404);
        $path = "solicitudes/solicitud-{$id}.pdf";
        $disk = Storage::disk('local');
        if (!$disk->exists($path)) {
            $disk = Storage::disk('public');
        }
        abort_unless($disk->exists($path), 404, 'PDF no encontrado.');
        return response()->file($disk->path($path), ['Content-Type' => 'application/pdf']);
    }

    /** =========================
     * ADMIN views
     * ========================= */
    public function index()
    {
        $query = Solicitud::with(['bodega', 'departamento', 'bodegaDestino']);

        if (auth()->user()->bodega_id) {
            $query->where('bodega_id', auth()->user()->bodega_id);
        }

        $solicitudes = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.solicitudes.index', compact('solicitudes'));
    }

    public function show($id)
    {
        $solicitud = Solicitud::with([
            'bodega',
            'bodegaDestino',
            'departamento',
            'detalles.producto'
        ])->findOrFail($id);

        return view('admin.solicitudes.show', compact('solicitud'));
    }

    public function aprobar($id)
    {
        $solicitud = Solicitud::findOrFail($id);

        if ($solicitud->estado !== 'pendiente') {
            return back()->with('error', 'Esta solicitud ya fue procesada.');
        }

        $solicitud->estado = 'aprobado';
        $solicitud->resolucion = 'A_LA_ESPERA';
        $solicitud->save();

        return redirect()->route('solicitud.ver', $id)
            ->with('success', 'Solicitud aprobada.');
    }

    public function rechazar(Request $request, $id)
    {
        $request->validate([
            'motivo_rechazo' => 'required|min:5'
        ]);

        try {
            $solicitud = Solicitud::findOrFail($id);

            $solicitud->estado = 'rechazado';
            $solicitud->motivo_rechazo = $request->motivo_rechazo;
            $solicitud->save();

            if ($solicitud->correo) {
                Mail::to($solicitud->correo)->send(new SolicitudRechazada($solicitud));
            }

            return back()->with('success', 'Solicitud rechazada y correo enviado.');

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** =========================
     * ENTREGADO (BODEGA)
     * ========================= */
    public function entregado($id)
    {
        $solicitud = Solicitud::with(['detalles.producto', 'departamento', 'bodegaDestino'])
            ->findOrFail($id);

        if (auth()->user()->bodega_id != $solicitud->bodega_id) {
            return back()->with('error', 'No tienes permiso.');
        }

        if ($solicitud->estado !== 'aprobado') {
            return back()->with('error', 'Solo solicitudes aprobadas pueden ser entregadas.');
        }

        // Si ya fue entregada, redirigir según tipo
        if ($solicitud->resolucion === 'ENTREGADO') {
            if ($solicitud->tipo_movimiento === 'PRESTAMO') {
                return redirect()->route('prestamos.historial')
                    ->with('success', 'Ya estaba entregada. Mostrando historial de préstamos.');
            }
            if ($solicitud->tipo_movimiento === 'SALIDA') {
                return redirect()->route('salidas.historial')
                    ->with('success', 'Ya estaba entregada. Mostrando historial de salidas.');
            }
            return back()->with('success', 'Ya estaba entregada.');
        }

        DB::beginTransaction();

        try {
            // 1) Inventario
            $this->aplicarMovimientoInventario($solicitud);

            // 2) PRESTAMO -> insertar en prestamos
            if ($solicitud->tipo_movimiento === 'PRESTAMO') {

                $codigoOperacion = "SOLPRE-{$solicitud->codigo}";

                if (!Prestamo::where('codigo_operacion', $codigoOperacion)->exists()) {

                    foreach ($solicitud->detalles as $d) {

                        $categoriaId = $d->producto?->categoria_id;
                        if (!$categoriaId) {
                            throw new \Exception("El producto {$d->producto?->nombre} no tiene categoría asignada.");
                        }

                        Prestamo::create([
                            'codigo_operacion'  => $codigoOperacion,
                            'bodega_id'         => $solicitud->bodega_id,
                            'categoria_id'      => $categoriaId,
                            'producto_id'       => $d->producto_id,
                            'departamento_id'   => $solicitud->departamento_solicitante,
                            'cantidad'          => (int)$d->cantidad,
                            'cantidad_devuelta' => 0,
                            'destino'           => strtoupper($solicitud->departamento->nombre ?? 'SOLICITUD'),
                            'entregado_por'     => strtoupper(auth()->user()->name ?? 'ENCARGADO'),
                            'recibido_por'      => strtoupper($solicitud->solicitante),
                            'observacion'       => strtoupper($d->observacion ?? ''),
                            'estado'            => 'prestado',
                        ]);
                    }
                }
            }

            // 3) SALIDA -> insertar en salidas (✅ con categoria_id)
            if ($solicitud->tipo_movimiento === 'SALIDA') {

                $codigoOperacion = "SOLSAL-{$solicitud->codigo}";

                if (!Salida::where('codigo_operacion', $codigoOperacion)->exists()) {

                    foreach ($solicitud->detalles as $d) {

                        $producto = Producto::lockForUpdate()->findOrFail($d->producto_id);

                        $categoriaId = $producto->categoria_id;
                        if (!$categoriaId) {
                            throw new \Exception("El producto {$producto->nombre} no tiene categoría asignada.");
                        }

                        Salida::create([
                            'codigo_operacion' => $codigoOperacion,
                            'bodega_id'        => $solicitud->bodega_id,

                            'producto_id'      => $producto->id,
                            'categoria_id'     => $categoriaId,
                            'departamento_id'  => $solicitud->departamento_solicitante,

                            'cantidad'         => (int)$d->cantidad,
                            'destino'          => strtoupper($solicitud->departamento->nombre ?? 'SOLICITUD'),
                            'entregado_por'    => strtoupper(auth()->user()->name ?? 'ENCARGADO'),
                            'recibido_por'     => strtoupper($solicitud->solicitante),
                            'observacion'      => strtoupper($d->observacion ?? ''),

                            'estado'           => 1, // ✅ IMPORTANTE: numérico
                        ]);
                    }
                }
            }

            // 4) TRASLADO: solo inventario (si quieres guardarlo en tabla traslados, dímelo)
            // ya se aplica en aplicarMovimientoInventario()

            // 5) marcar ENTREGADO
            $solicitud->resolucion = 'ENTREGADO';
            $solicitud->save();

            DB::commit();

            // 6) redirect según tipo
            if ($solicitud->tipo_movimiento === 'PRESTAMO') {
                return redirect()->route('prestamos.historial')
                    ->with('success', 'Solicitud entregada y registrada en préstamos.');
            }

            if ($solicitud->tipo_movimiento === 'SALIDA') {
                return redirect()->route('salidas.historial')
                    ->with('success', 'Solicitud entregada y registrada en salidas.');
            }

            if ($solicitud->tipo_movimiento === 'TRASLADO') {
                return redirect()->route('traslados.historial')
                    ->with('success', 'Traslado entregado y registrado correctamente.');
            }

            return back()->with('success', 'Solicitud marcada como ENTREGADA y stock actualizado.');

        } catch (\Throwable $e) {

            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Inventario:
     * - SALIDA/PRESTAMO: baja stock origen
     * - TRASLADO: baja origen y suma destino
     */
    private function aplicarMovimientoInventario(Solicitud $solicitud): void
    {
        foreach ($solicitud->detalles as $detalle) {

            $productoOrigen = Producto::where('id', $detalle->producto_id)
                ->lockForUpdate()
                ->first();

            if (!$productoOrigen) {
                throw new \Exception("Producto origen no encontrado (ID: {$detalle->producto_id}).");
            }

            $cantidad = (int)$detalle->cantidad;

            if ($cantidad <= 0) {
                throw new \Exception("Cantidad inválida para {$productoOrigen->nombre}.");
            }

            if ((int)$productoOrigen->stock < $cantidad) {
                throw new \Exception("Stock insuficiente para {$productoOrigen->nombre}. Disponible: {$productoOrigen->stock}");
            }

            // SALIDA / PRESTAMO => bajar stock
            if (in_array($solicitud->tipo_movimiento, ['SALIDA', 'PRESTAMO'], true)) {
                $productoOrigen->decrement('stock', $cantidad);
                continue;
            }

            // TRASLADO => bajar origen + subir destino
            if ($solicitud->tipo_movimiento === 'TRASLADO') {

                if (!$solicitud->bodega_destino_id) {
                    throw new \Exception("Falta bodega destino para el traslado.");
                }

                // 1) bajar stock en origen
                $productoOrigen->decrement('stock', $cantidad);

                // 2) buscar producto equivalente en destino (por nombre + dpto + categoria)
                $productoDestino = Producto::where('bodega_id', $solicitud->bodega_destino_id)
                    ->where('nombre', $productoOrigen->nombre)
                    ->where('departamento_id', $productoOrigen->departamento_id)
                    ->where('categoria_id', $productoOrigen->categoria_id)
                    ->lockForUpdate()
                    ->first();

                // 3) si NO existe -> CREARLO copiando datos del origen
                if (!$productoDestino) {

                    $nuevo = $productoOrigen->replicate(); // copia columnas
                    $nuevo->bodega_id = $solicitud->bodega_destino_id;
                    $nuevo->stock = 0;

                    // ⚠️ Si tu "codigo" es UNIQUE global, descomenta esto:
                    // $nuevo->codigo = $productoOrigen->codigo . '-T' . $solicitud->bodega_destino_id;

                    $nuevo->save();

                    $productoDestino = $nuevo;
                }

                // 4) sumar stock en destino
                $productoDestino->increment('stock', $cantidad);

                // 5) registrar en tabla TRASLADOS (historial)
                $codigoOperacion = "TRA-{$solicitud->codigo}";

                Traslado::create([
                    'codigo_operacion'    => $codigoOperacion,
                    'solicitud_id'        => $solicitud->id, // ✅ CLAVE

                    'bodega_origen_id'    => $solicitud->bodega_id,
                    'bodega_destino_id'   => $solicitud->bodega_destino_id,

                    'producto_id_origen'  => $productoOrigen->id,
                    'producto_id_destino' => $productoDestino->id,

                    'categoria_id'        => $productoOrigen->categoria_id,
                    'departamento_id'     => $productoOrigen->departamento_id,

                    'cantidad'            => $cantidad,
                    'entregado_por'       => strtoupper(auth()->user()->name ?? 'ENCARGADO'),
                    'recibido_por'        => strtoupper($solicitud->solicitante),
                    'observacion'         => strtoupper($detalle->observacion ?? ''),
                    'estado'              => 1,
                ]);

                // 6) registrar ENTRADA en bodega destino (para auditoría)
                Entrada::create([
                    'departamento_id'     => $productoDestino->departamento_id,
                    'categoria_id'        => $productoDestino->categoria_id,
                    'producto_id'         => $productoDestino->id,
                    'bodega_id'           => $solicitud->bodega_destino_id,
                    'cantidad'            => $cantidad,
                    'recibido_por'        => strtoupper($solicitud->solicitante),
                    'entregado_por'       => strtoupper(auth()->user()->name ?? 'ENCARGADO'),
                    'observacion'         => 'TRASLADO DESDE BODEGA ID: '.$solicitud->bodega_id.' | '.$detalle->observacion,
                    'estado'              => 1,
                    'codigo_operacion'    => $codigoOperacion,
                    'numero_factura'      => null,
                    'numero_orden_compra' => null,
                    'factura_pdf'         => null,
                ]);

                continue;
            }
        }
    }

    public function mostrarConsultaPublica()
    {
        return view('public.consultar-solicitud');
    }

    public function buscarPorCodigo(Request $request)
    {
        $request->validate([
            'codigo' => ['required', 'regex:/^SOL-\d{5}$/']
        ], [
            'codigo.regex' => 'El formato del código no es válido. Ejemplo correcto: SOL-00001'
        ]);

        $solicitud = Solicitud::with(['departamento', 'bodega', 'bodegaDestino', 'detalles.producto'])
            ->where('codigo', $request->codigo)
            ->first();

        if (!$solicitud) {
            return back()->with('error', 'No se encontró la solicitud.');
        }

        return view('public.resultado-consulta', compact('solicitud'));
    }
}
