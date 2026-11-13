<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Departamento;
use App\Models\Bodega;
use App\Models\TipoProducto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Imports\ProductosImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProductoController extends Controller
{
    /**
     * ================================================================
     * LISTADO DE PRODUCTOS
     * ================================================================
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Producto::with([
            'categoria',
            'tipoProducto',
            'unidades',
            'departamento',
            'bodega'
        ]);

        /**
         * Si el usuario tiene bodega asignada,
         * solamente puede ver los productos de esa bodega.
         */
        if ($user->bodega_id) {
            $query->where('bodega_id', $user->bodega_id);
            $bodega = $user->bodega;
        } else {
            $bodega = null;
        }

        /**
         * ============================================================
         * BUSCADOR
         * Buscar por:
         * - Código
         * - Nombre
         * - Descripción
         * ============================================================
         */
        if ($request->filled('buscar')) {

            $buscar = trim($request->buscar);

            $query->where(function ($q) use ($buscar) {

                $q->where('codigo', 'like', '%' . $buscar . '%')
                    ->orWhere('nombre', 'like', '%' . $buscar . '%')
                    ->orWhere('descripcion', 'like', '%' . $buscar . '%');
            });
        }

        /**
         * Mantener parámetros como:
         *
         * ?buscar=...
         *
         * cuando se cambia de página.
         */
        $productos = $query
            ->orderBy('id', 'desc')
            ->paginate(4)
            ->withQueryString();

        $categorias = Categoria::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $tipoProductos = TipoProducto::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $unidades = UnidadMedida::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $departamentos = Departamento::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        $bodegas = Bodega::where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return view('pages.productos', compact(
            'productos',
            'categorias',
            'tipoProductos',
            'unidades',
            'departamentos',
            'bodega',
            'bodegas'
        ));
    }

    /**
     * ================================================================
     * REGISTRAR PRODUCTO
     * ================================================================
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        /**
         * Si tiene bodega asignada se utiliza esa bodega.
         * Si es administrador se toma la bodega del formulario.
         */
        $bodegaId = $user->bodega_id ?: $request->bodega_id;

        if (!$bodegaId) {
            throw ValidationException::withMessages([
                'bodega_id' => [
                    'Debe seleccionar una bodega para el producto.'
                ]
            ]);
        }

        $request->validate([
            'codigo' => [
                'required',
                'string',
                'max:255',

                function ($attribute, $value, $fail) use ($bodegaId) {

                    $codigo = strtoupper(trim($value));

                    $exists = Producto::whereRaw(
                        'UPPER(codigo) = ?',
                        [$codigo]
                    )
                    ->where('bodega_id', $bodegaId)
                    ->exists();

                    if ($exists) {
                        $fail('El código ya existe en esta bodega.');
                    }
                },
            ],

            'nombre' => [
                'required',
                'string',
                'max:255'
            ],

            'descripcion' => [
                'nullable',
                'string'
            ],

            'stock' => [
                'required',
                'integer',
                'min:0'
            ],

            'categoria_id' => [
                'required',
                'exists:categorias,id'
            ],

            'tipo_producto_id' => [
                'required',
                'exists:tipos_productos,id'
            ],

            'unidades_id' => [
                'required',
                'exists:unidades,id'
            ],

            'departamento_id' => [
                'required',
                'exists:departamentos,id'
            ],

            'bodega_id' => [
                'nullable',
                'exists:bodegas,id'
            ],
        ], [
            'codigo.required' => 'Debe ingresar el código del producto.',
            'nombre.required' => 'Debe ingresar el nombre del producto.',
            'stock.required' => 'Debe ingresar el stock.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock no puede ser negativo.',
            'categoria_id.required' => 'Debe seleccionar una categoría.',
            'tipo_producto_id.required' => 'Debe seleccionar un tipo de producto.',
            'unidades_id.required' => 'Debe seleccionar una unidad de medida.',
            'departamento_id.required' => 'Debe seleccionar un departamento.',
        ]);

        Producto::create([
            'codigo' => strtoupper(trim($request->codigo)),
            'nombre' => strtoupper(trim($request->nombre)),
            'descripcion' => strtoupper(trim($request->descripcion ?? '')),
            'stock' => $request->stock,
            'categoria_id' => $request->categoria_id,
            'tipo_producto_id' => $request->tipo_producto_id,
            'unidades_id' => $request->unidades_id,
            'departamento_id' => $request->departamento_id,
            'bodega_id' => $bodegaId,
            'estado' => 1,
        ]);

        return redirect()
            ->route('productos')
            ->with(
                'success',
                'Producto registrado con éxito.'
            );
    }

    /**
     * ================================================================
     * ACTUALIZAR PRODUCTO
     * ================================================================
     */
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $user = Auth::user();

        /**
         * ============================================================
         * SEGURIDAD POR BODEGA
         * ============================================================
         *
         * Si el usuario tiene una bodega asignada, solamente podrá
         * modificar productos pertenecientes a esa bodega.
         */
        if (
            $user->bodega_id &&
            (int) $producto->bodega_id !== (int) $user->bodega_id
        ) {
            throw ValidationException::withMessages([
                'bodega_id' => [
                    'No puedes modificar productos de otra bodega.'
                ]
            ]);
        }

        /**
         * Determinar la bodega final.
         *
         * Usuario con bodega:
         * utiliza obligatoriamente su propia bodega.
         *
         * Administrador:
         * utiliza la bodega seleccionada en el formulario.
         */
        $bodegaId = $user->bodega_id
            ? $user->bodega_id
            : $request->bodega_id;

        /**
         * Un producto debe tener una bodega.
         */
        if (!$bodegaId) {
            throw ValidationException::withMessages([
                'bodega_id' => [
                    'Debe seleccionar una bodega para el producto.'
                ]
            ]);
        }

        /**
         * ============================================================
         * VALIDACIÓN
         * ============================================================
         */
        $request->validate([

            /**
             * CÓDIGO
             *
             * El código debe ser único dentro de la bodega.
             *
             * Se excluye el ID del producto actual para permitir:
             *
             * ABC-001 -> ABC-001
             *
             * sin que Laravel lo considere duplicado consigo mismo.
             */
            'codigo' => [
                'required',
                'string',
                'max:255',

                function ($attribute, $value, $fail) use (
                    $bodegaId,
                    $producto
                ) {

                    $codigo = strtoupper(trim($value));

                    $exists = Producto::whereRaw(
                        'UPPER(codigo) = ?',
                        [$codigo]
                    )
                    ->where(
                        'bodega_id',
                        $bodegaId
                    )
                    ->where(
                        'id',
                        '!=',
                        $producto->id
                    )
                    ->exists();

                    if ($exists) {

                        $fail(
                            'Ya existe otro producto con este código en la bodega seleccionada.'
                        );
                    }
                },
            ],

            'nombre' => [
                'required',
                'string',
                'max:255'
            ],

            'descripcion' => [
                'nullable',
                'string'
            ],

            'stock' => [
                'required',
                'integer',
                'min:0'
            ],

            'categoria_id' => [
                'required',
                'exists:categorias,id'
            ],

            'tipo_producto_id' => [
                'required',
                'exists:tipos_productos,id'
            ],

            'unidades_id' => [
                'required',
                'exists:unidades,id'
            ],

            'departamento_id' => [
                'required',
                'exists:departamentos,id'
            ],

            'bodega_id' => [
                'nullable',
                'exists:bodegas,id'
            ],

        ], [

            'codigo.required' =>
                'Debe ingresar el código del producto.',

            'codigo.max' =>
                'El código no puede tener más de 255 caracteres.',

            'nombre.required' =>
                'Debe ingresar el nombre del producto.',

            'nombre.max' =>
                'El nombre no puede tener más de 255 caracteres.',

            'stock.required' =>
                'Debe ingresar el stock.',

            'stock.integer' =>
                'El stock debe ser un número entero.',

            'stock.min' =>
                'El stock no puede ser negativo.',

            'categoria_id.required' =>
                'Debe seleccionar una categoría.',

            'categoria_id.exists' =>
                'La categoría seleccionada no existe.',

            'tipo_producto_id.required' =>
                'Debe seleccionar un tipo de producto.',

            'tipo_producto_id.exists' =>
                'El tipo de producto seleccionado no existe.',

            'unidades_id.required' =>
                'Debe seleccionar una unidad de medida.',

            'unidades_id.exists' =>
                'La unidad de medida seleccionada no existe.',

            'departamento_id.required' =>
                'Debe seleccionar un departamento.',

            'departamento_id.exists' =>
                'El departamento seleccionado no existe.',

            'bodega_id.exists' =>
                'La bodega seleccionada no existe.',
        ]);

        /**
         * ============================================================
         * ACTUALIZAR
         * ============================================================
         */
        $producto->update([

            'codigo' =>
                strtoupper(trim($request->codigo)),

            'nombre' =>
                strtoupper(trim($request->nombre)),

            'descripcion' =>
                strtoupper(trim($request->descripcion ?? '')),

            'stock' =>
                $request->stock,

            'categoria_id' =>
                $request->categoria_id,

            'tipo_producto_id' =>
                $request->tipo_producto_id,

            'unidades_id' =>
                $request->unidades_id,

            'departamento_id' =>
                $request->departamento_id,

            'bodega_id' =>
                $bodegaId,
        ]);

        /**
         * ============================================================
         * CONSERVAR PÁGINA Y BÚSQUEDA
         * ============================================================
         */
        $page = $request->input('page', 1);

        $buscar = trim(
            $request->input('buscar', '')
        );

        $parametros = [
            'page' => $page
        ];

        if ($buscar !== '') {
            $parametros['buscar'] = $buscar;
        }

        return redirect()
            ->route(
                'productos',
                $parametros
            )
            ->with(
                'success',
                'Producto actualizado con éxito.'
            );
    }

    /**
     * ================================================================
     * CAMBIAR ESTADO
     * ================================================================
     */
    public function toggleEstado(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        // 🛡️ PROTECCIÓN: Validar que el producto pertenezca a la bodega del usuario
        if (Auth::user()->bodega_id && $producto->bodega_id !== Auth::user()->bodega_id) {
            return back()->with('error', 'No puedes modificar productos de otra bodega.');
        }

        $producto->estado = !$producto->estado;

        $producto->save();

        if (
            $request->ajax() ||
            $request->expectsJson()
        ) {

            return response()->json([

                'success' => true,

                'estado' =>
                    $producto->estado
                        ? 'activo'
                        : 'inactivo',

                'message' =>
                    'Estado del producto actualizado correctamente.'
            ]);
        }

        return redirect()
            ->back()
            ->with(
                'success',
                'Estado del producto actualizado correctamente.'
            );
    }

    /**
     * ================================================================
     * ACTIVAR PRODUCTO
     * ================================================================
     */
    public function activar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        // 🛡️ PROTECCIÓN: Validar que el producto pertenezca a la bodega del usuario
        if (Auth::user()->bodega_id && $producto->bodega_id !== Auth::user()->bodega_id) {
            return back()->with('error', 'No puedes modificar productos de otra bodega.');
        }

        $producto->estado = 1;

        $producto->save();

        if (
            $request->ajax() ||
            $request->expectsJson()
        ) {

            return response()->json([
                'success' => true,
                'estado' => 'activo',
                'message' =>
                    'Producto activado correctamente.'
            ]);
        }

        return redirect()
            ->route('productos')
            ->with(
                'success',
                'Producto activado correctamente.'
            );
    }

    /**
     * ================================================================
     * DESACTIVAR PRODUCTO
     * ================================================================
     */
    public function desactivar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        // 🛡️ PROTECCIÓN: Validar que el producto pertenezca a la bodega del usuario
        if (Auth::user()->bodega_id && $producto->bodega_id !== Auth::user()->bodega_id) {
            return back()->with('error', 'No puedes modificar productos de otra bodega.');
        }

        $producto->estado = 0;

        $producto->save();

        if (
            $request->ajax() ||
            $request->expectsJson()
        ) {

            return response()->json([
                'success' => true,
                'estado' => 'inactivo',
                'message' =>
                    'Producto desactivado correctamente.'
            ]);
        }

        return redirect()
            ->route('productos')
            ->with(
                'success',
                'Producto desactivado correctamente.'
            );
    }

    /**
     * ================================================================
     * PRODUCTOS POR BODEGA
     * ================================================================
     */
    public function porBodega($id)
    {
        try {

            $productos = Producto::with([
                'categoria',
                'departamento',
                'bodega',
                'tipoProducto',
                'unidades'
            ])
            ->where('bodega_id', $id)
            ->orderBy('nombre', 'asc')
            ->paginate(5);

            return response()->json(
                $productos
            );

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,

                'message' =>
                    'Error al obtener productos por bodega.',

                'error' =>
                    $e->getMessage()

            ], 500);
        }
    }

        public function porDepartamento($id)
    {
        $user = Auth::user();

        $query = Producto::with([
            'categoria',
            'departamento',
            'bodega',
            'tipoProducto',
            'unidades'
        ])->where('departamento_id', $id);

        // Si el usuario está restringido a una bodega, solo ve esa bodega
        if ($user->bodega_id) {
            $query->where('bodega_id', $user->bodega_id);
        }

        return response()->json(
            $query->orderBy('nombre', 'asc')->paginate(5)
        );
    }

    /**
     * ================================================================
     * TODOS LOS PRODUCTOS
     * ================================================================
     */
    public function todos()
    {
        try {
            $user = Auth::user();

            $productos = Producto::with([
                'categoria',
                'departamento',
                'bodega',
                'tipoProducto',
                'unidades'
            ])
            // 🛡️ FILTRO: Si el usuario tiene bodega, solo le mostramos su bodega
            ->when($user->bodega_id, fn($q) => $q->where('bodega_id', $user->bodega_id))
            ->orderBy('nombre', 'asc')
            ->paginate(5);

            return response()->json(
                $productos
            );

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,

                'message' =>
                    'Error al obtener productos.',

                'error' =>
                    $e->getMessage()

            ], 500);
        }
    }

    /**
     * ================================================================
     * CONSULTAR PRODUCTOS DE OTRAS BODEGAS
     * ================================================================
     */
    public function otrasBodegas(Request $request)
    {
        $user = Auth::user();

        $bodegaAsignada =
            $user->bodega_id ?? null;

        $query = Producto::with([
            'bodega',
            'categoria',
            'departamento',
            'unidades'
        ])
        ->where('estado', 1)

        ->when(
            $bodegaAsignada,
            fn ($q) =>
                $q->where(
                    'bodega_id',
                    '!=',
                    $bodegaAsignada
                )
        );

        /**
         * Filtro por bodega.
         */
        if ($request->filled('bodega')) {

            $query->where(
                'bodega_id',
                $request->bodega
            );
        }

        /**
         * Buscador.
         */
        if ($request->filled('buscar')) {

            $buscar =
                trim($request->buscar);

            $query->where(
                function ($q) use ($buscar) {

                    $q->where(
                        'nombre',
                        'like',
                        "%{$buscar}%"
                    )
                    ->orWhere(
                        'codigo',
                        'like',
                        "%{$buscar}%"
                    );
                }
            );
        }

        $productos = $query
            ->orderBy('bodega_id')
            ->paginate(5)
            ->withQueryString();

        return response()->json(
            $productos
        );
    }

    /**
     * ================================================================
     * IMPORTAR PRODUCTOS DESDE EXCEL
     * ================================================================
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo_excel' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:5120', // 5 MB
            ],
        ], [
            'archivo_excel.required' => 'Debe seleccionar un archivo Excel.',
            'archivo_excel.file'     => 'El archivo subido no es válido.',
            'archivo_excel.mimes'    => 'El archivo debe ser de tipo Excel (.xlsx o .xls).',
            'archivo_excel.max'      => 'El archivo no puede pesar más de 5 MB.',
        ]);

        $user = Auth::user();

        // La bodega se fuerza si el usuario tiene una asignada
        $bodegaId = $user->bodega_id ?: null;

        $import = new ProductosImport($bodegaId);

        try {
            Excel::import($import, $request->file('archivo_excel'));
        } catch (\Exception $e) {
            return redirect()
                ->route('productos')
                ->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }

        // Los resultados están en la hoja interna
        $resultado = $import->sheet;

        // Construir mensaje de resultado
        $partes = [];
        $partes[] = "{$resultado->importados} artículo(s) importado(s) correctamente";

        if ($resultado->duplicados > 0) {
            $partes[] = "{$resultado->duplicados} duplicado(s) omitido(s)";
        }
        if ($resultado->fallidos > 0) {
            $partes[] = "{$resultado->fallidos} fila(s) con errores";
        }

        $mensaje = implode('. ', $partes) . '.';

        // Si hubo errores detallados los mostramos
        if (count($resultado->errores) > 0) {
            $detalles = array_slice($resultado->errores, 0, 15); // máximo 15
            $mensaje .= ' Detalle: ' . implode(' | ', $detalles);

            if (count($resultado->errores) > 15) {
                $mensaje .= ' | ... y ' . (count($resultado->errores) - 15) . ' error(es) más.';
            }
        }

        $tipo = $resultado->importados > 0 ? 'success' : 'error';

        return redirect()
            ->route('productos')
            ->with($tipo, $mensaje);
    }

    /**
     * ================================================================
     * DESCARGAR PLANTILLA EXCEL PARA IMPORTACIÓN
     * ================================================================
     */
    public function plantillaImportacion()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Artículos');

        // ── Encabezados ──
        $encabezados = [
            'A1' => 'codigo',
            'B1' => 'nombre',
            'C1' => 'descripcion',
            'D1' => 'stock',
            'E1' => 'categoria',
            'F1' => 'tipo_producto',
            'G1' => 'unidad_medida',
            'H1' => 'departamento',
            'I1' => 'bodega',
        ];

        foreach ($encabezados as $celda => $valor) {
            $sheet->setCellValue($celda, $valor);
        }

        // ── Estilos del encabezado ──
        $headerRange = 'A1:I1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => '000000'],
                ],
            ],
        ]);

        // ── Fila de ejemplo ──
        $sheet->setCellValue('A2', 'ART-001');
        $sheet->setCellValue('B2', 'ESCRITORIO DE MADERA');
        $sheet->setCellValue('C2', 'ESCRITORIO EJECUTIVO CON 3 CAJONES');
        $sheet->setCellValue('D2', 10);
        $sheet->setCellValue('E2', 'MOBILIARIO');
        $sheet->setCellValue('F2', 'CONSUMIBLE');
        $sheet->setCellValue('G2', 'UNIDAD');
        $sheet->setCellValue('H2', 'ADMINISTRACIÓN');
        $sheet->setCellValue('I2', 'BODEGA CENTRAL');

        $sheet->getStyle('A2:I2')->getFont()->setItalic(true)->getColor()->setRGB('6C757D');

        // ── Anchos de columna ──
        $anchos = ['A' => 15, 'B' => 30, 'C' => 40, 'D' => 10, 'E' => 20, 'F' => 20, 'G' => 20, 'H' => 25, 'I' => 25];
        foreach ($anchos as $col => $ancho) {
            $sheet->getColumnDimension($col)->setWidth($ancho);
        }

        // ── Hoja de instrucciones ──
        $instrucciones = $spreadsheet->createSheet();
        $instrucciones->setTitle('Instrucciones');

        $textos = [
            ['A1', 'INSTRUCCIONES PARA LA IMPORTACIÓN DE ARTÍCULOS'],
            ['A3', '1. Use la hoja "Plantilla Artículos" para llenar los datos.'],
            ['A4', '2. La fila 1 contiene los encabezados. NO los modifique.'],
            ['A5', '3. La fila 2 tiene datos de ejemplo. Elimínela o reemplácela con sus datos reales.'],
            ['A6', '4. Comience a llenar desde la fila 2 (o 3 si conserva el ejemplo).'],
            ['A8', 'COLUMNAS OBLIGATORIAS:'],
            ['A9', '   • codigo: Código único del artículo dentro de la bodega.'],
            ['A10', '   • nombre: Nombre del artículo.'],
            ['A11', '   • stock: Cantidad en existencia (número entero ≥ 0).'],
            ['A12', '   • categoria: Nombre exacto de la categoría (debe existir en el sistema).'],
            ['A13', '   • tipo_producto: Nombre exacto del tipo de producto (debe existir en el sistema).'],
            ['A14', '   • unidad_medida: Nombre exacto de la unidad de medida (debe existir en el sistema).'],
            ['A15', '   • departamento: Nombre exacto del departamento (debe existir en el sistema).'],
            ['A17', 'COLUMNAS OPCIONALES:'],
            ['A18', '   • descripcion: Descripción del artículo.'],
            ['A19', '   • bodega: Nombre de la bodega destino. Si su usuario tiene bodega asignada, se ignora esta columna.'],
            ['A21', 'NOTAS:'],
            ['A22', '   • Los nombres de categoría, tipo, unidad, departamento y bodega deben coincidir con los registrados en el sistema.'],
            ['A23', '   • No importa si usa mayúsculas o minúsculas; el sistema los convertirá automáticamente.'],
            ['A24', '   • Si un código ya existe en la misma bodega, esa fila será omitida como duplicado.'],
            ['A25', '   • Tamaño máximo del archivo: 5 MB.'],
            ['A26', '   • Formatos aceptados: .xlsx, .xls'],
        ];

        foreach ($textos as [$celda, $texto]) {
            $instrucciones->setCellValue($celda, $texto);
        }

        $instrucciones->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('198754');
        $instrucciones->getStyle('A8')->getFont()->setBold(true);
        $instrucciones->getStyle('A17')->getFont()->setBold(true);
        $instrucciones->getStyle('A21')->getFont()->setBold(true);
        $instrucciones->getColumnDimension('A')->setWidth(90);

        // Volver a la primera hoja
        $spreadsheet->setActiveSheetIndex(0);

        // ── Descargar ──
        $filename = 'plantilla_importacion_articulos.xlsx';
        $temp = tempnam(sys_get_temp_dir(), 'plantilla_');

        $writer = new Xlsx($spreadsheet);
        $writer->save($temp);

        return response()->download($temp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}