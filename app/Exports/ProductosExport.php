<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Auth;

class ProductosExport implements FromView, WithColumnWidths, WithEvents
{
    public $bodega;
    public $buscar;

    public function __construct($bodega = null, $buscar = null)
    {
        $this->bodega = $bodega;
        $this->buscar = $buscar;
    }

    public function view(): View
    {
        $user = Auth::user();
        $bodegaAsignada = $user->bodega_id ?? null;

        $query = Producto::with(['bodega', 'categoria', 'departamento', 'unidades', 'tipoProducto'])
            ->when($bodegaAsignada, fn($q) => $q->where('bodega_id', $bodegaAsignada))
            ->when(!$bodegaAsignada && $this->bodega, fn($q) => $q->where('bodega_id', $this->bodega))
            ->when($this->buscar, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nombre', 'like', "%{$this->buscar}%")
                        ->orWhere('codigo', 'like', "%{$this->buscar}%");
                });
            })
            ->orderBy('bodega_id')
            ->orderBy('nombre');

        $productos = $query->get();

        return view('exports.productos', [
            'productos' => $productos,
            'user' => $user
        ]);
    }

    // 🔹 Anchos de columna fijos para que nada se aplaste
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Código
            'B' => 35, // Nombre
            'C' => 40, // Descripción
            'D' => 20, // Bodega
            'E' => 10, // Stock
            'F' => 15, // Unidad
            'G' => 20, // Categoría
            'H' => 25, // Departamento
            'I' => 15, // Tipo
            'J' => 15, // Estado
        ];
    }

    // 🔹 Magia de estilos aplicada dinámicamente al terminar de generar las filas
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // 1. Título Principal (Fila 1)
                $sheet->mergeCells('A1:J1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('198754'); // Verde institucional
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // 2. Encabezados de la tabla (Fila 2)
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '198754'] // Fondo Verde
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]
                    ]
                ];
                $sheet->getStyle('A2:J2')->applyFromArray($headerStyle);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // 3. Estilo para las filas de datos (Filas 3 en adelante)
                $dataRange = 'A3:J' . $highestRow;
                
                // Bordes finos grises
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                // 4. Efecto Cebra (Alternar fondo gris claro y blanco)
                for ($row = 3; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':J' . $row)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F9FAFB'); // Gris muy claro
                    }
                    
                    // Alinear números al centro/derecha
                    $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Stock
                    $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Estado
                }
            },
        ];
    }
}
