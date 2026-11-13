<?php

namespace App\Imports;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Departamento;
use App\Models\Bodega;
use App\Models\TipoProducto;
use App\Models\UnidadMedida;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class ProductosImport implements WithMultipleSheets, SkipsUnknownSheets
{
    /**
     * Bodega destino (obligatoria si el usuario tiene una asignada).
     */
    protected ?int $bodegaId;

    /**
     * Hoja interna que realmente procesa los datos.
     */
    public ProductosSheetImport $sheet;

    public function __construct(?int $bodegaId)
    {
        $this->bodegaId = $bodegaId;
        $this->sheet    = new ProductosSheetImport($bodegaId);
    }

    /**
     * Solo procesar la primera hoja (índice 0).
     */
    public function sheets(): array
    {
        return [
            0 => $this->sheet,
        ];
    }

    /**
     * Ignorar cualquier otra hoja sin lanzar error.
     */
    public function onUnknownSheet($sheetName)
    {
        // Silenciosamente ignorar hojas adicionales (ej: "Instrucciones")
    }

    // ── Accesos rápidos a los resultados ──
    public function __get($name)
    {
        if (in_array($name, ['importados', 'duplicados', 'fallidos', 'errores'])) {
            return $this->sheet->{$name};
        }
        return null;
    }
}

/**
 * ================================================================
 * Hoja individual de importación
 * ================================================================
 * Lee todas las filas como array crudo (sin WithHeadingRow) y
 * detecta automáticamente la fila de encabezados buscando
 * la primera fila que contenga "codigo" o "código".
 * ================================================================
 */
class ProductosSheetImport implements ToArray
{
    protected ?int $bodegaId;

    public int   $importados = 0;
    public int   $duplicados = 0;
    public int   $fallidos   = 0;
    public array $errores    = [];

    public function __construct(?int $bodegaId)
    {
        $this->bodegaId = $bodegaId;
    }

    /**
     * Mapa de sinónimos para reconocer diferentes nombres de columna.
     * Clave = nombre canónico, Valor = posibles variantes (slug).
     */
    private function aliasMap(): array
    {
        return [
            'codigo'       => ['codigo', 'código', 'cod', 'code'],
            'nombre'       => ['nombre', 'articulo', 'artículo', 'producto', 'name'],
            'descripcion'  => ['descripcion', 'descripción', 'desc', 'detalle'],
            'stock'        => ['stock', 'cantidad', 'existencia', 'qty'],
            'categoria'    => ['categoria', 'categoría', 'cat'],
            'tipo_producto'=> ['tipo_producto', 'tipo', 'tipoproducto', 'type'],
            'unidad_medida'=> ['unidad_medida', 'unidadmedida', 'unidad', 'um', 'u_medida'],
            'departamento' => ['departamento', 'depto', 'dpto', 'dept', 'department'],
            'bodega'       => ['bodega', 'almacen', 'almacén', 'warehouse', 'bod'],
        ];
    }

    /**
     * Normaliza un string para comparación de encabezados:
     * quita acentos, espacios extra, convierte a minúsculas, reemplaza espacios por _.
     */
    private function slugHeader(string $value): string
    {
        $value = mb_strtolower(trim($value));

        // Quitar acentos comunes
        $acentos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u',
        ];
        $value = strtr($value, $acentos);

        // Reemplazar espacios / guiones por _
        $value = preg_replace('/[\s\-]+/', '_', $value);

        // Solo alfanuméricos y _
        $value = preg_replace('/[^a-z0-9_]/', '', $value);

        return $value;
    }

    /**
     * Encuentra la fila de encabezados y devuelve su índice + el mapa columna→nombre canónico.
     */
    private function detectHeaders(array $rows): ?array
    {
        $aliases = $this->aliasMap();

        // Invertir: variante → canónico
        $variantToCanonical = [];
        foreach ($aliases as $canonical => $variants) {
            foreach ($variants as $v) {
                $variantToCanonical[$v] = $canonical;
            }
        }

        foreach ($rows as $rowIndex => $row) {
            $mapping = [];
            foreach ($row as $colIndex => $cell) {
                $slug = $this->slugHeader((string) ($cell ?? ''));
                if (isset($variantToCanonical[$slug])) {
                    $mapping[$variantToCanonical[$slug]] = $colIndex;
                }
            }

            // Se considera fila de encabezados si tiene al menos "codigo" y "nombre"
            if (isset($mapping['codigo']) && isset($mapping['nombre'])) {
                return [
                    'headerRow' => $rowIndex,
                    'mapping'   => $mapping,
                ];
            }
        }

        return null;
    }

    /**
     * Procesar todas las filas del archivo.
     */
    public function array(array $rows): void
    {
        if (empty($rows)) {
            $this->errores[] = 'El archivo está vacío o no contiene datos.';
            return;
        }

        // ── Detectar encabezados automáticamente ──
        $detected = $this->detectHeaders($rows);

        if (!$detected) {
            $this->errores[] = 'No se encontró una fila de encabezados válida. Asegúrese de que el archivo tenga las columnas "codigo" y "nombre".';
            return;
        }

        $headerRow = $detected['headerRow'];
        $mapping   = $detected['mapping'];

        // ── Cachear catálogos ──
        $categorias    = Categoria::where('estado', 1)->get()->keyBy(fn($c) => mb_strtoupper(trim($c->nombre)));
        $tipos         = TipoProducto::where('estado', 1)->get()->keyBy(fn($t) => mb_strtoupper(trim($t->nombre)));
        $unidades      = UnidadMedida::where('estado', 1)->get()->keyBy(fn($u) => mb_strtoupper(trim($u->nombre)));
        $departamentos = Departamento::where('estado', 1)->get()->keyBy(fn($d) => mb_strtoupper(trim($d->nombre)));
        $bodegas       = Bodega::where('estado', 1)->get()->keyBy(fn($b) => mb_strtoupper(trim($b->nombre)));

        // ── Función auxiliar para leer celda por nombre canónico ──
        $getVal = function (array $row, string $field) use ($mapping): string {
            if (!isset($mapping[$field])) {
                return '';
            }
            return trim((string) ($row[$mapping[$field]] ?? ''));
        };

        // ── Procesar filas de datos (después de la fila de encabezados) ──
        $dataRows = array_slice($rows, $headerRow + 1, null, true);

        foreach ($dataRows as $rowIndex => $row) {
            $filaExcel = $rowIndex + 1; // Número real de fila en Excel (1-based)

            // Saltar filas completamente vacías
            $celdas = array_filter($row, fn($c) => trim((string) ($c ?? '')) !== '');
            if (empty($celdas)) {
                continue;
            }

            // ── Leer valores ──
            $codigo       = mb_strtoupper($getVal($row, 'codigo'));
            $nombre       = mb_strtoupper($getVal($row, 'nombre'));
            $descripcion  = mb_strtoupper($getVal($row, 'descripcion'));
            $stockRaw     = $getVal($row, 'stock');
            $stock        = is_numeric($stockRaw) ? (int) $stockRaw : 0;
            $catNombre    = mb_strtoupper($getVal($row, 'categoria'));
            $tipoNombre   = mb_strtoupper($getVal($row, 'tipo_producto'));
            $unidadNombre = mb_strtoupper($getVal($row, 'unidad_medida'));
            $deptoNombre  = mb_strtoupper($getVal($row, 'departamento'));
            $bodegaNombre = mb_strtoupper($getVal($row, 'bodega'));

            // ── Validaciones básicas ──
            $erroresFila = [];

            if ($codigo === '') {
                $erroresFila[] = 'El código es obligatorio.';
            }
            if ($nombre === '') {
                $erroresFila[] = 'El nombre es obligatorio.';
            }
            if ($stock < 0) {
                $erroresFila[] = 'El stock no puede ser negativo.';
            }

            // ── Resolver relaciones por nombre ──
            $categoriaId    = $categorias[$catNombre]->id ?? null;
            $tipoId         = $tipos[$tipoNombre]->id ?? null;
            $unidadId       = $unidades[$unidadNombre]->id ?? null;
            $departamentoId = $departamentos[$deptoNombre]->id ?? null;

            // Bodega: la del usuario tiene prioridad, sino la del archivo
            $bodegaFinal = $this->bodegaId;
            if (!$bodegaFinal && $bodegaNombre !== '') {
                $bodegaFinal = $bodegas[$bodegaNombre]->id ?? null;
            }

            if ($catNombre !== '' && !$categoriaId) {
                $erroresFila[] = "Categoría «{$catNombre}» no encontrada.";
            } elseif ($catNombre === '') {
                $erroresFila[] = 'La categoría es obligatoria.';
            }

            if ($tipoNombre !== '' && !$tipoId) {
                $erroresFila[] = "Tipo producto «{$tipoNombre}» no encontrado.";
            } elseif ($tipoNombre === '') {
                $erroresFila[] = 'El tipo de producto es obligatorio.';
            }

            if ($unidadNombre !== '' && !$unidadId) {
                $erroresFila[] = "Unidad de medida «{$unidadNombre}» no encontrada.";
            } elseif ($unidadNombre === '') {
                $erroresFila[] = 'La unidad de medida es obligatoria.';
            }

            if ($deptoNombre !== '' && !$departamentoId) {
                $erroresFila[] = "Departamento «{$deptoNombre}» no encontrado.";
            } elseif ($deptoNombre === '') {
                $erroresFila[] = 'El departamento es obligatorio.';
            }

            if (!$bodegaFinal) {
                $erroresFila[] = 'No se pudo determinar la bodega.';
            }

            // ── Verificar duplicado de código en la misma bodega ──
            if ($codigo !== '' && $bodegaFinal && count($erroresFila) === 0) {
                $existe = Producto::whereRaw('UPPER(codigo) = ?', [$codigo])
                    ->where('bodega_id', $bodegaFinal)
                    ->exists();

                if ($existe) {
                    $this->duplicados++;
                    $this->errores[] = "Fila {$filaExcel}: El código «{$codigo}» ya existe en la bodega (omitido).";
                    continue;
                }
            }

            if (count($erroresFila) > 0) {
                $this->fallidos++;
                foreach ($erroresFila as $e) {
                    $this->errores[] = "Fila {$filaExcel}: {$e}";
                }
                continue;
            }

            // ── Crear producto ──
            try {
                Producto::create([
                    'codigo'           => $codigo,
                    'nombre'           => $nombre,
                    'descripcion'      => $descripcion,
                    'stock'            => $stock,
                    'categoria_id'     => $categoriaId,
                    'tipo_producto_id' => $tipoId,
                    'unidades_id'      => $unidadId,
                    'departamento_id'  => $departamentoId,
                    'bodega_id'        => $bodegaFinal,
                    'estado'           => 1,
                ]);

                $this->importados++;
            } catch (\Exception $e) {
                $this->fallidos++;
                $this->errores[] = "Fila {$filaExcel}: Error al guardar — {$e->getMessage()}";
            }
        }
    }
}
