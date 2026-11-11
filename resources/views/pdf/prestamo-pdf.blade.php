<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; position: relative; }

        .logo { text-align: center; margin-bottom: -5px; }
        .logo img { width: 95px; }

        .title-bar {
            background: #4b0082;
            color: white;
            padding: 6px 10px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            border-radius: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 13px;
        }

        th {
            background: #4b0082;
            color: white;
            padding: 6px;
            border: 1px solid #2c004d;
            text-align: center;
        }

        td {
            padding: 6px;
            border: 1px solid #2c004d;
        }

        .section p { margin: 2px 0; }

        @if($estado === 'cancelado')
        tbody tr td {
            background: #ffe6e6 !important;
        }
        @endif
    </style>
</head>

<body>

    {{-- 🔥 Marca de agua si está cancelado --}}
    @if($estado === 'cancelado')
        <div style="
            position: fixed;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 85px;
            color: rgba(255, 0, 0, 0.18);
            font-weight: bold;
            z-index: 0;
            pointer-events: none;
        ">
            CANCELADO
        </div>
    @endif

    {{-- LOGO --}}
    <div class="logo">
        <img src="{{ public_path('img/LAPAZESTE.png') }}">
    </div>

    {{-- BLOQUE PRINCIPAL --}}
    <div style="
        background:#f1ecff;
        padding:18px;
        border-radius:10px;
        text-align:center;
        margin-top:10px;
        margin-bottom:25px;
        border:1px solid #d0c2ff;
        position: relative;
        z-index: 1;
    ">

        <h2 style="
            margin:0;
            font-size:18px;
            text-transform:uppercase;
            letter-spacing:1px;
            color:#3d096b;
        ">
            Registro de Préstamo
        </h2>

        <p style="margin:8px 0 2px 0; font-size:14px;">
            <strong>Código operación:</strong>
            <span style="color:#5a15a8; font-weight:bold;">
                {{ $codigo }}
            </span>
        </p>

        {{-- ESTADO --}}
        <p style="margin-top:5px; font-size:14px;">
            <strong>Estado:</strong>
            @if($estado === 'prestado')
                <span style="color:#d98900; font-weight:bold;">PRESTADO</span>
            @elseif($estado === 'parcial')
                <span style="color:#0066cc; font-weight:bold;">DEVOLUCIÓN PARCIAL</span>
            @elseif($estado === 'devuelto')
                <span style="color:#008000; font-weight:bold;">DEVUELTO</span>
            @elseif($estado === 'cancelado')
                <span style="color:#ff0000; font-weight:bold;">CANCELADO</span>
            @endif
        </p>

        <p style="margin-top:6px; font-size:14px;">
            <strong>Fecha:</strong> {{ $fecha->format('d/m/Y H:i') }}
        </p>

    </div>

    {{-- INFO GENERAL --}}
    <div class="title-bar">Datos del préstamo:</div>

    <div class="section" style="position: relative; z-index: 1;">
        <p><strong>Bodega:</strong> {{ strtoupper($bodega ?? '---') }}</p>
        <p><strong>Departamento:</strong> {{ strtoupper($departamento ?? '---') }}</p>
        <p><strong>Destino:</strong> {{ strtoupper($destino) }}</p>
        <p><strong>Entregado por:</strong> {{ strtoupper($entregado) }}</p>
        <p><strong>Recibido por:</strong> {{ strtoupper($recibido) }}</p>
    </div>

    {{-- RESUMEN --}}
    <div class="title-bar">Resumen general:</div>

    <div class="section" style="position: relative; z-index: 1;">
        <p><strong>Total prestado:</strong> {{ $totalPrestado }}</p>
        <p><strong>Total devuelto:</strong> {{ $totalDevuelto }}</p>
        <p><strong>Pendiente:</strong> {{ $pendiente }}</p>
    </div>

    {{-- DETALLE --}}
    <div class="title-bar">Detalle de productos en préstamo:</div>

    <table style="position: relative; z-index: 1;">
        <thead>
            <tr>
                <th>PRODUCTO</th>
                <th>CATEGORÍA</th>
                <th>CANTIDAD PRESTADA</th>
                <th>DEVUELTA</th>
                <th>PENDIENTE</th>
                <th>OBSERVACIÓN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $p)
                @php
                    $pend = $p->cantidad - $p->cantidad_devuelta;
                @endphp
                <tr>
                    <td>{{ $p->producto->nombre ?? '' }}</td>
                    <td>{{ $p->categoria->nombre ?? '' }}</td>
                    <td style="text-align:center;">{{ $p->cantidad }}</td>
                    <td style="text-align:center;">{{ $p->cantidad_devuelta }}</td>
                    <td style="text-align:center;">{{ $pend }}</td>
                    <td>{{ $p->observacion }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
