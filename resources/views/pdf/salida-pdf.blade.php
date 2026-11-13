<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Salida - {{ $codigo }}</title>
    <style>
        @page { margin: 40px; }
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
        }
        
        /* Utilidades */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-upper { text-transform: uppercase; }
        
        /* Encabezado */
        .header-table { width: 100%; margin-bottom: 30px; }
        .header-table td { vertical-align: middle; }
        .title {
            font-size: 15px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 12px;
            text-align: center;
            text-transform: uppercase;
        }
        .subtitle-code {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            text-decoration: underline;
            margin-top: 5px;
        }

        /* Info Box (Solicitante, Depto, etc) */
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 3px 0; border-bottom: 1px solid #000; }
        .info-label { width: 15%; font-weight: bold; text-transform: uppercase; }
        .info-colon { width: 2%; }
        
        /* Tabla de Productos */
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 6px; }
        .items-table th { background-color: #f2f2f2; font-weight: bold; text-align: center; font-size: 11px; }
        .items-table .totals { text-align: right; font-weight: bold; border: none; padding-top: 10px; }

        /* Sección de Firmas */
        .signatures-table { width: 100%; margin-top: 70px; text-align: center; border-collapse: collapse; }
        .signatures-table td { width: 50%; vertical-align: bottom; }
        .signature-line { border-bottom: 1px solid #000; width: 80%; margin: 0 auto; margin-bottom: 5px; }
        .signature-name { font-weight: bold; text-transform: uppercase; font-size: 11px; }
        .signature-role { font-size: 10px; margin-top: 2px; }
        
        /* Footer */
        .footer { margin-top: 50px; text-align: right; font-size: 9px; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>
<body>

    {{-- 🔹 MARCA DE AGUA SI ESTÁ CANCELADA --}}
    @if($estado == 0)
        <div style="position: fixed; top: 30%; left: 15%; font-size: 100px; color: rgba(255,0,0,0.15); transform: rotate(-45deg); font-weight: bold; z-index: -1000;">
            CANCELADA
        </div>
    @endif

    {{-- 🔹 ENCABEZADO --}}
    <table class="header-table">
        <tr>
            <td width="20%" class="text-center">
                <img src="{{ public_path('img/LAPAZESTE.png') }}" width="90" alt="Logo">
            </td>
            <td width="60%">
                <div class="title">COMPROBANTE DE SALIDA</div>
                <div class="subtitle">BODEGA GENERAL</div>
                <div class="subtitle-code">{{ $codigo }}</div>
            </td>
            <td width="20%" class="text-right" style="vertical-align: top;">
                <strong>FECHA:</strong><br>
                {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    {{-- 🔹 DATOS GENERALES --}}
    <table class="info-table">
        <tr>
            <td class="info-label">RECIBIDO POR:</td>
            <td class="info-colon">:</td>
            <td class="text-upper">{{ $recibido }}</td>
        </tr>
        <tr>
            <td class="info-label">DEPARTAMENTO:</td>
            <td class="info-colon">:</td>
            <td class="text-upper">{{ $departamento }}</td>
        </tr>
        <tr>
            <td class="info-label">DESTINO / USO:</td>
            <td class="info-colon">:</td>
            <td class="text-upper">{{ $destino }}</td>
        </tr>
        <tr>
            <td class="info-label">BODEGA ORIGEN:</td>
            <td class="info-colon">:</td>
            <td class="text-upper">{{ $bodega }}</td>
        </tr>
    </table>

    {{-- 🔹 DETALLE DE PRODUCTOS --}}
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">N°</th>
                <th width="40%">DESCRIPCIÓN</th>
                <th width="20%">CATEGORÍA</th>
                <th width="10%">CANT.</th>
                <th width="10%">U/M</th>
                <th width="15%">OBSERVACIÓN</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; $totalCantidad = 0; @endphp
            @foreach ($items as $item)
                @php $totalCantidad += $item->cantidad; @endphp
                <tr>
                    <td class="text-center">{{ $i++ }}</td>
                    <td>
                        <strong class="text-upper">{{ $item->producto->nombre ?? '—' }}</strong>
                    </td>
                    <td class="text-center text-upper">{{ $item->categoria->nombre ?? '—' }}</td>
                    <td class="text-center">{{ number_format($item->cantidad, 2) }}</td>
                    <td class="text-center text-upper">{{ $item->unidades->nombre ?? 'UNIDAD' }}</td>
                    <td class="text-upper">{{ $item->observacion ?? '' }}</td>
                </tr>
            @endforeach
            
            {{-- Fila de totales (simulando el recuadro del formato oficial) --}}
            <tr>
                <td colspan="6" class="totals" style="border-top: 1px solid #000;">
                    Productos diferentes: {{ $items->count() }} &nbsp;&nbsp;|&nbsp;&nbsp; 
                    Total de unidades: {{ number_format($totalCantidad, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- 🔹 OBSERVACIONES GENERALES --}}
    <div style="margin-top: 20px;">
        <strong>OBSERVACIONES / DESCRIPCIÓN DE USO:</strong>
        <div style="border: 1px solid #000; height: 40px; padding: 5px; margin-top: 5px;">
            <!-- Aquí puedes poner alguna variable si tienes una observación general -->
        </div>
    </div>

    {{-- 🔹 FIRMAS --}}
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $recibido }}</div>
                <div class="signature-role">Nombre, firma y sello</div>
                <div class="signature-role text-bold" style="margin-top: 10px;">
                    SALIDA REGISTRADA EL {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y H:i') }}
                </div>
            </td>
            <td>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $entregado }}</div>
                <div class="signature-role">ENCARGADO DE BODEGA GENERAL</div>
                <div class="signature-role">Firma y sello</div>
            </td>
        </tr>
    </table>

    {{-- 🔹 PIE DE PÁGINA --}}
    <div class="footer">
        Comprobante generado por el Sistema de Control de Inventario - Alcaldía Municipal de La Paz Este
    </div>

</body>
</html>
