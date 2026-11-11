<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CodigoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\EntradaController;
use App\Http\Controllers\SalidaController;
use App\Http\Controllers\PrestamosController;
use App\Http\Controllers\BajaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\BodegaController;
use App\Http\Controllers\TipoProductoController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\SolicitudController;
use App\Http\Controllers\SolicitanteController;
use App\Http\Controllers\TrasladoController;

use App\Exports\ProductosExport;
use Maatwebsite\Excel\Facades\Excel;

/* ============================================================
   ROOT
============================================================ */
Route::get('/', function () {
    return Auth::check() ? redirect()->route('panel') : redirect()->route('login');
});

/* ============================================================
   LOGIN ADMIN (AUTH)
============================================================ */
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/sesion/actividad', function (Illuminate\Http\Request $request) {
    abort_unless(Auth::check() || $request->session()->has('solicitante_id'), 401);
    return response()->noContent();
})->name('sesion.actividad');

// Opcional: si aún lo necesitas como GET (no recomendado, pero lo dejo)
Route::get('/logout', [LoginController::class, 'logout']);

/* ============================================================
   VALIDAR CÓDIGO / USUARIOS (FUERA AUTH)
============================================================ */
Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
Route::post('/validar-codigo', [CodigoController::class, 'validar'])->name('codigo.validar');

/* ============================================================
   ADMIN - PROTEGIDO
============================================================ */
Route::middleware('auth')->group(function () {

    Route::get('/panel', [DashboardController::class, 'index'])->name('panel');

    /* ===================== USUARIOS ===================== */
    Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios');
    Route::post('/store', [UserController::class, 'store'])->name('usuarios.store');
    Route::put('/update/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::put('/toggle/{id}', [UserController::class, 'toggleEstado'])->name('usuarios.toggle');
    Route::delete('/delete/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');

    /* ===================== PRODUCTOS ===================== */
    Route::get('/productos', [ProductoController::class, 'index'])->name('productos');
    Route::post('/productos/store', [ProductoController::class, 'store'])->name('productos.store');
    Route::put('/productos/{id}', [ProductoController::class, 'update'])->name('productos.update');
    Route::put('/productos/{id}/desactivar', [ProductoController::class, 'desactivar'])->name('productos.desactivar');
    Route::put('/productos/{id}/activar', [ProductoController::class, 'activar'])->name('productos.activar');

    Route::get('/productos/printVista', [ProductoController::class, 'printVista'])->name('productos.printVista');
    Route::get('/productos/todos', [ProductoController::class, 'todos'])->name('productos.todos');
    Route::get('/productos/otras-bodegas', [ProductoController::class, 'otrasBodegas']);
    Route::get('/productos/bodega/{id}', [ProductoController::class, 'porBodega'])->name('productos.porBodega');

    Route::get('/productos/export', function (Illuminate\Http\Request $request) {
        return Excel::download(
            new ProductosExport($request->bodega, $request->buscar),
            'productos.xlsx'
        );
    })->name('productos.export');

    /* ===================== ENTRADAS ===================== */
    Route::get('/entradas', [EntradaController::class, 'index'])->name('entradas');
    Route::post('/entradas', [EntradaController::class, 'store'])->name('entradas.store');
    Route::get('/entradas/historial', [EntradaController::class, 'historial'])->name('entradas.historial');
    Route::get('/entradas/historial/detalle/{codigo}', [EntradaController::class, 'detalleEntrada'])->name('entradas.detalle');
    Route::get('/entradas/pdf/{codigo}', [EntradaController::class, 'exportarPDF'])->name('entradas.pdf');
    Route::delete('/entradas/{codigo}', [EntradaController::class, 'destroy'])->name('entradas.destroy');

    Route::get('/bodega/{bodegaId}/productos', [EntradaController::class, 'getProductosPorBodega'])->name('bodega.productos');
    Route::post('/entradas/crear-producto', [EntradaController::class, 'crearEntrada'])->name('productos.crearEntrada');

    Route::get('/facturas/ver/{archivo}', [EntradaController::class, 'verFactura'])
    ->name('facturas.ver');

    /* ===================== SALIDAS ===================== */
    Route::get('/salidas', [SalidaController::class, 'index'])->name('salidas');
    Route::post('/salidas', [SalidaController::class, 'store'])->name('salidas.store');
    Route::get('/salidas/historial', [SalidaController::class, 'historial'])->name('salidas.historial');
    Route::get('/salidas/detalle/{codigo}', [SalidaController::class, 'detalle'])->name('salidas.detalle');
    Route::get('/salidas/pdf/{codigo}', [SalidaController::class, 'exportarPDF'])->name('salidas.pdf');
    Route::delete('/salidas/{codigo}', [SalidaController::class, 'cancelar'])->name('salidas.cancelar');

    /* ===================== PRESTAMOS ===================== */
    Route::get('/prestamos', [PrestamosController::class, 'index'])->name('prestamos');
    Route::post('/prestamos', [PrestamosController::class, 'store'])->name('prestamos.store');
    Route::get('/prestamos/productos/bodega/{bodegaId}', [PrestamosController::class, 'productosPorBodega'])->name('prestamos.productos.bodega');
    Route::get('/prestamos/historial', [PrestamosController::class, 'historial'])->name('prestamos.historial');
    Route::get('/prestamos/historial/detalle/{codigo}', [PrestamosController::class, 'detalleOperacion'])->name('prestamos.detalle');
    Route::post('/prestamos/devolver-todo/{codigo}', [PrestamosController::class, 'devolverTodo'])->name('prestamos.devolver.todo');
    Route::post('/prestamos/devolver-parcial/{codigo}', [PrestamosController::class, 'devolverParcial'])->name('prestamos.devolver.parcial');
    Route::delete('/prestamos/{codigo}', [PrestamosController::class, 'cancelarOperacion'])->name('prestamos.cancelar');
    Route::get('/prestamos/pdf/{codigo}', [PrestamosController::class, 'exportarPDF'])->name('prestamos.pdf');

    /* ===================== BAJAS ===================== */
    Route::get('/bajas', [BajaController::class, 'index'])->name('bajas');
    Route::post('/bajas', [BajaController::class, 'store'])->name('bajas.store');
    Route::get('/bajas/historial', [BajaController::class, 'historial'])->name('bajas.historial');
    Route::get('/bajas/historial/detalle/{codigo}', [BajaController::class, 'detalleOperacion']);
    Route::get('/bajas/pdf/{codigo}', [BajaController::class, 'exportarPDF'])->name('bajas.pdf');
    Route::delete('/bajas/{codigo}', [BajaController::class, 'destroy'])->name('bajas.cancelar');
    Route::get('/bajas/bodega/{bodegaId}/productos', [BajaController::class, 'productosPorBodega'])->name('bajas.productos.bodega');

    /* ===================== CATEGORIAS ===================== */
    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
    Route::put('/categoria/{id}/desactivar', [CategoriaController::class, 'desactivar'])->name('categoria.desactivar');
    Route::put('/categoria/{id}/activar', [CategoriaController::class, 'activar'])->name('categoria.activar');

    /* ===================== DEPARTAMENTOS ===================== */
    Route::get('/departamentos', [DepartamentoController::class, 'index'])->name('departamentos');
    Route::post('/departamentos', [DepartamentoController::class, 'store'])->name('departamentos.store');
    Route::put('/departamentos/{id}', [DepartamentoController::class, 'update'])->name('departamentos.update');
    Route::delete('/departamentos/{id}', [DepartamentoController::class, 'destroy'])->name('departamentos.destroy');

    /* ===================== BODEGAS ===================== */
    Route::get('/bodegas', [BodegaController::class, 'index'])->name('bodegas.index');
    Route::post('/bodegas', [BodegaController::class, 'store'])->name('bodegas.store');
    Route::put('/bodegas/{id}', [BodegaController::class, 'update'])->name('bodegas.update');
    Route::put('/bodegas/{id}/activar', [BodegaController::class, 'activar'])->name('bodegas.activar');
    Route::put('/bodegas/{id}/desactivar', [BodegaController::class, 'desactivar'])->name('bodegas.desactivar');

    /* ===================== TIPO PRODUCTO ===================== */
    Route::get('/tipoProducto', [TipoProductoController::class, 'index'])->name('tipoProducto');
    Route::post('/tipoProducto', [TipoProductoController::class, 'store'])->name('tipoProducto.store');
    Route::put('/tipoProducto/{id}', [TipoProductoController::class, 'update'])->name('tipoProducto.update');
    Route::put('/tipoProducto/{id}/activar', [TipoProductoController::class, 'activar'])->name('tipoProducto.activar');
    Route::put('/tipoProducto/{id}/desactivar', [TipoProductoController::class, 'desactivar'])->name('tipoProducto.desactivar');

    /* ===================== UNIDADES ===================== */
    Route::get('/unidades', [UnidadMedidaController::class, 'index'])->name('unidades');
    Route::post('/unidades', [UnidadMedidaController::class, 'store'])->name('unidades.store');
    Route::put('/unidades/{unidad}', [UnidadMedidaController::class, 'update'])->name('unidades.update');
    Route::delete('/unidades/{unidad}', [UnidadMedidaController::class, 'destroy'])->name('unidades.destroy');
    Route::put('/unidades/{id}/activar', [UnidadMedidaController::class, 'activar'])->name('unidades.activar');
    Route::put('/unidades/{id}/desactivar', [UnidadMedidaController::class, 'desactivar'])->name('unidades.desactivar');

    /* ===================== SOLICITUDES ADMIN ===================== */
    Route::get('/admin/solicitudes', [SolicitudController::class, 'index'])->name('solicitud.index');
    Route::get('/admin/solicitudes/{id}', [SolicitudController::class, 'show'])->name('solicitud.ver');
    Route::post('/admin/solicitudes/{id}/aprobar', [SolicitudController::class, 'aprobar'])->name('solicitud.aprobar');
    Route::post('/admin/solicitudes/{id}/rechazar', [SolicitudController::class, 'rechazar'])->name('solicitud.rechazar');

    /* ===================== SOLICITANTES ===================== */
    Route::resource('solicitantes', SolicitanteController::class)->except(['create', 'edit', 'show']);
    Route::get('solicitantes-exportar-pdf', [SolicitanteController::class, 'exportPdf'])->name('solicitantes.export.pdf');
    Route::get('solicitantes-exportar-excel', [SolicitanteController::class, 'exportExcel'])->name('solicitantes.export.excel');
    

    // TRASLADOS
    Route::get('/traslados/historial', [TrasladoController::class, 'historial'])->name('traslados.historial');
    Route::get('/traslados/detalle/{codigo}', [TrasladoController::class, 'detalle'])->name('traslados.detalle');
    Route::get('/traslados/pdf/{codigo}', [TrasladoController::class, 'exportarPDF'])->name('traslados.pdf');
});

/* ============================================================
   SOLICITUD - PÚBLICO (SOLICITANTE)
   Flujo:
   /solicitud -> login
   post /solicitud/login -> autentica
   /solicitud/tipo -> elige PRESTAMO/SALIDA/TRASLADO
   post /solicitud/tipo -> guarda tipo
   /solicitud/ingreso -> formulario
============================================================ */

Route::get('/solicitud', [SolicitudController::class, 'login'])->name('solicitud.login');

Route::post('/solicitud/login', [SolicitudController::class, 'autenticar'])->name('solicitud.autenticar');

Route::get('/solicitud/logout', [SolicitudController::class, 'logout'])->name('solicitud.logout');

/* -------- NUEVAS RUTAS: SELECCIÓN DE TIPO -------- */
Route::get('/solicitud/tipo', [SolicitudController::class, 'tipoMovimiento'])->name('solicitud.tipo');
Route::post('/solicitud/tipo', [SolicitudController::class, 'guardarTipoMovimiento'])->name('solicitud.tipo.guardar');

/* -------- FORMULARIO Y ENVÍO -------- */
Route::get('/solicitud/ingreso', [SolicitudController::class, 'formularioPublicoProtegido'])->name('solicitud.form');

Route::post('/solicitud/enviar', [SolicitudController::class, 'registrarPublico'])->name('solicitud.enviar');
Route::get('/solicitud/pdf/{id}', [SolicitudController::class, 'descargarPdf'])
    ->middleware('signed')->name('solicitud.pdf');

/* -------- CARGA DINÁMICA -------- */
Route::get('/solicitud/bodega/{bodegaId}/departamentos', [SolicitudController::class, 'departamentosPorBodega']);
Route::get('/solicitud/bodega/{bodegaId}/departamento/{deptoId}/productos', [SolicitudController::class, 'productosPorDepto']);

/* -------- CONSULTA PÚBLICA -------- */
Route::get('/solicitud/consultar', [SolicitudController::class, 'mostrarConsultaPublica'])->name('solicitud.consultar');
Route::post('/solicitud/consultar', [SolicitudController::class, 'buscarPorCodigo'])->name('solicitud.buscar');

/* -------- MARCAR ENTREGADO (ADMIN BODEGA) -------- */
Route::post('/solicitud/{id}/entregado', [SolicitudController::class, 'entregado'])->middleware('auth')->name('solicitud.entregado');

Route::get('/solicitud/bodegas-destino/{bodegaOrigenId}', [SolicitudController::class, 'bodegasDestino']);

