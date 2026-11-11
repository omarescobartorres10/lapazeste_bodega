<?php

namespace App\Exports;

use App\Models\Solicitante;
use Maatwebsite\Excel\Concerns\FromCollection;

class SolicitantesExport implements FromCollection
{
    public function collection()
    {
        return Solicitante::select('dui', 'nombre', 'cargo', 'departamento_id', 'telefono', 'correo', 'estado')
            ->with('departamento')
            ->get()
            ->map(function ($s) {
                return [
                    'DUI' => $s->dui,
                    'Nombre' => $s->nombre,
                    'Cargo' => $s->cargo,
                    'Departamento' => $s->departamento?->nombre,
                    'Teléfono' => $s->telefono,
                    'Correo' => $s->correo,
                    'Estado' => ucfirst($s->estado),
                ];
            });
    }
}
