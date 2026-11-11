<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; }

        .logo { text-align: center; margin-bottom: -5px; }
        .logo img { width: 95px; }

        .title-bar {
            background: #004d66;
            color: white;
            padding: 6px 10px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            border-radius: 4px;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 13px; }
        th {
            background: #004d66;
            color: white;
            padding: 6px;
            border: 1px solid #003444;
            text-align: center;
        }
        td { padding: 6px; border: 1px solid #003444; }

        .section p { margin: 2px 0; }

        .badge {
            display:inline-block;
            padding:4px 10px;
            border-radius:6px;
            font-weight:bold;
            font-size:13px;
            color:white;
        }
        .badge-pendiente { background:#d9a404; }
        .badge-aprobado  { background:#28a745; }
        .badge-rechazado { background:#dc3545; }

        .subtle {
            color:#003b4d;
            font-weight:bold;
        }
    </style>
</head>

<body>

@php
    $tipo = $solicitud->tipo_movimiento ?? '';

    $titulo = match($tipo) {
        'TRASLADO' => 'TRASLADO DE PRODUCTOS',
        'SALIDA'   => 'SALIDA DE PRODUCTOS',
        'PRESTAMO' => 'PRÉSTAMO DE PRODUCTOS',
        default    => 'SOLICITUD DE PRODUCTOS',
    };

    $claseEstado = match($solicitud->estado) {
        'pendiente' => 'badge-pendiente',
        'aprobado'  => 'badge-aprobado',
        'rechazado' => 'badge-rechazado',
        default     => 'badge-pendiente',
    };
@endphp

    {{-- LOGO --}}
    <div class="logo">
        <img src="{{ public_path('img/LAPAZESTE.png') }}">
    </div>

    {{-- BLOQUE PRINCIPAL --}}
    <div style="
        background:#e9f2f7;
        padding:18px;
        border-radius:10px;
        text-align:center;
        margin-top:10px;
        margin-bottom:25px;
        border:1px solid #c7dce8;
    ">

        <h2 style="
            margin:0;
            font-size:18px;
            text-transform:uppercase;
            letter-spacing:1px;
            color:#003b4d;
        ">
            {{ $titulo }}
        </h2>

        <p style="margin:8px 0 2px 0; font-size:14px;">
            <strong>Código:</strong>
            <span style="color:#006699; font-weight:bold;">
                {{ $solicitud->codigo }}
            </span>
        </p>

        <p style="margin-top:6px; font-size:14px;">
            <strong>Estado:</strong>
            <span class="badge {{ $claseEstado }}">
                {{ strtoupper($solicitud->estado) }}
            </span>
        </p>

    </div>

    {{-- DATOS DEL SOLICITANTE --}}
    <div class="title-bar">Datos del solicitante:</div>

    <div class="section">
        <p><strong>Nombre:</strong> {{ $solicitud->solicitante }}</p>
        <p><strong>Teléfono:</strong> {{ $solicitud->telefono }}</p>
        <p><strong>Correo:</strong> {{ $solicitud->correo }}</p>

        {{-- FECHAS SEGÚN TIPO --}}
        @if($tipo === 'PRESTAMO')
            <p><strong>Fecha de préstamo (salida):</strong> {{ $solicitud->fecha_requerida }}</p>
            <p><strong>Fecha de devolución:</strong> {{ $solicitud->fecha_devolucion ?? 'N/D' }}</p>
        @else
            <p><strong>Fecha requerida:</strong> {{ $solicitud->fecha_requerida }}</p>
        @endif
    </div>

    {{-- INFORMACIÓN --}}
    <div class="title-bar">Información de la solicitud:</div>

    <div class="section">
        <p><strong>Tipo de movimiento:</strong> {{ $tipo }}</p>
        <p><strong>Bodega origen:</strong> {{ $solicitud->bodega->nombre ?? 'N/D' }}</p>

        @if($tipo === 'TRASLADO')
            <p><strong>Bodega destino:</strong> {{ $solicitud->bodegaDestino->nombre ?? 'N/D' }}</p>
        @endif

        <p><strong>Departamento:</strong> {{ $solicitud->departamento->nombre ?? 'N/D' }}</p>
    </div>

    {{-- PRODUCTOS --}}
    <div class="title-bar">Productos solicitados:</div>

    <table>
        <thead>
            <tr>
                <th>PRODUCTO</th>
                <th>CANTIDAD</th>
                <th>OBSERVACIÓN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($solicitud->detalles as $d)
            <tr>
                <td>{{ $d->producto->nombre }}</td>
                <td style="text-align:center;">{{ $d->cantidad }}</td>
                <td>{{ $d->observacion }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>