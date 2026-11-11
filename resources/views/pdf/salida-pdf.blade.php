<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            position: relative;
        }

        .logo { text-align: center; margin-bottom: -5px; }
        .logo img { width: 95px; }

        .title-bar {
            background: #7b1010;
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
            background: #7b1010;
            color: white;
            padding: 6px;
            border: 1px solid #4f0707;
            text-align: center;
        }

        td {
            padding: 6px;
            border: 1px solid #4f0707;
        }

        .section p { margin: 2px 0; }

        @if($estado == 0)
        tbody tr td {
            background: #ffe6e6 !important;
        }
        @endif
    </style>
</head>

<body>

    {{-- Marca de agua si está cancelada --}}
    @if($estado == 0)
        <div style="
            position: fixed;
            top: 35%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 95px;
            color: rgba(255, 0, 0, 0.15);
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
        background:#f7e9e9;
        padding:18px;
        border-radius:10px;
        text-align:center;
        margin-top:10px;
        margin-bottom:25px;
        border:1px solid #e8c7c7;
        position: relative;
        z-index: 1;
    ">

        <h2 style="
            margin:0;
            font-size:18px;
            text-transform:uppercase;
            letter-spacing:1px;
            color:#7b1010;
        ">
            Salida de Productos
        </h2>

        <p style="margin:8px 0 2px 0; font-size:14px;">
            <strong>Código operación:</strong>
            <span style="color:#b30000; font-weight:bold;">
                {{ $codigo }}
            </span>
        </p>

        <p style="margin-top:5px; font-size:14px;">
            <strong>Estado:</strong>
            @if($estado == 1)
                <span style="color:green; font-weight:bold;">CREADA</span>
            @else
                <span style="color:red; font-weight:bold;">CANCELADA</span>
            @endif
        </p>

        <p style="margin-top:6px; font-size:14px;">
            <strong>Fecha:</strong> {{ $fecha->format('d/m/Y H:i') }}
        </p>
    </div>

    {{-- BLOQUE ALERTA SI ESTÁ CANCELADA --}}
    @if($estado == 0)
        <div style="
            background:#ffcccc;
            border-left:5px solid red;
            padding:10px;
            margin-bottom:15px;
            color:#b30000;
            font-weight:bold;
            z-index: 1;
            position: relative;
        ">
            ⚠ ESTA SALIDA FUE CANCELADA Y EL STOCK FUE RESTITUIDO
        </div>
    @endif

    {{-- DATOS GENERALES --}}
    <div class="title-bar">Datos de la salida:</div>

    <div class="section" style="z-index: 1; position: relative;">
        <p><strong>Bodega:</strong> {{ strtoupper($bodega) }}</p>
        <p><strong>Departamento:</strong> {{ strtoupper($departamento) }}</p>
        <p><strong>Destino:</strong> {{ strtoupper($destino) }}</p>
        <p><strong>Entregado por:</strong> {{ strtoupper($entregado) }}</p>
        <p><strong>Recibido por:</strong> {{ strtoupper($recibido) }}</p>
    </div>

    {{-- PRODUCTOS --}}
    <div class="title-bar">Productos egresados:</div>

    <table style="z-index: 1; position: relative;">
        <thead>
            <tr>
                <th>PRODUCTO</th>
                <th>CATEGORÍA</th>
                <th>CANTIDAD</th>
                <th>OBSERVACIÓN</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i)
            <tr>
                <td>{{ $i->producto->nombre }}</td>
                <td>{{ $i->categoria->nombre }}</td>
                <td style="text-align:center;">{{ $i->cantidad }}</td>
                <td>{{ $i->observacion }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
