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

        /* 🔴 Encabezados en rojo */
        .title-bar {
            background: #b30000;
            color: white;
            padding: 6px 10px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            border-radius: 4px;
        }

        /* 🔴 Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 13px;
        }

        th {
            background: #b30000;
            color: white;
            padding: 6px;
            border: 1px solid #800000;
            text-align: center;
        }

        td {
            padding: 6px;
            border: 1px solid #800000;
        }

        .section p { margin: 2px 0; }

        /* 🔴 Filas rosadas si está cancelada */
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
        font-size: 90px;
        color: rgba(255, 0, 0, 0.15);
        font-weight: bold;
        z-index: 0;
        pointer-events: none;
    ">
        CANCELADA
    </div>
    @endif

    {{-- LOGO --}}
    <div class="logo">
        <img src="{{ public_path('img/LAPAZESTE.png') }}">
    </div>

    {{-- TÍTULO PRINCIPAL --}}
    <div style="
        background:#ffe6e6;
        padding:18px;
        border-radius:10px;
        text-align:center;
        margin-top:10px;
        margin-bottom:25px;
        border:1px solid #ffcccc;
        position: relative;
        z-index: 1;
    ">

        <h2 style="
            margin:0;
            font-size:18px;
            text-transform:uppercase;
            letter-spacing:1px;
            color:#b30000;
        ">
            Registro de Bajas de Inventario
        </h2>

        <p style="margin:8px 0 2px 0; font-size:14px;">
            <strong style="color:#800000;">Código operación:</strong>
            <span style="color:#b30000; font-weight:bold;">
                {{ $codigo }}
            </span>
        </p>

        <p style="margin-top:5px; font-size:14px;">
            <strong style="color:#800000;">Estado:</strong>
            @if($estado == 1)
                <span style="color:green; font-weight:bold;">CREADA</span>
            @else
                <span style="color:red; font-weight:bold;">CANCELADA</span>
            @endif
        </p>

        <p style="margin-top:6px; font-size:14px;">
            <strong style="color:#800000;">Fecha:</strong> {{ $fecha->format('d/m/Y H:i') }}
        </p>
    </div>

    @if($estado == 0)
    <div style="
        background:#ffcccc;
        border-left:5px solid red;
        padding:10px;
        margin-bottom:15px;
        color:#b30000;
        font-weight:bold;
        position: relative;
        z-index: 1;
    ">
        ⚠ ESTA OPERACIÓN FUE CANCELADA Y EL STOCK FUE RESTAURADO
    </div>
    @endif

    {{-- DATOS GENERALES --}}
    <div class="title-bar">Datos de la baja:</div>

    <div class="section" style="position: relative; z-index: 1;">
        <p><strong>Bodega:</strong> {{ strtoupper($bodega) }}</p>
        <p><strong>Departamento:</strong> {{ strtoupper($departamento) }}</p>
        <p><strong>Motivo:</strong> {{ strtoupper($motivo) }}</p>
        <p><strong>Responsable:</strong> {{ strtoupper($responsable) }}</p>
        <p><strong>Total unidades dadas de baja:</strong> {{ $totalBajas }}</p>
    </div>

    {{-- PRODUCTOS --}}
    <div class="title-bar">Productos dados de baja:</div>

    <table style="position: relative; z-index: 1;">
        <thead>
            <tr>
                <th>PRODUCTO</th>
                <th>CATEGORÍA</th>
                <th>DEPARTAMENTO</th>
                <th>CANTIDAD</th>
            </tr>
        </thead>

        <tbody>
            @foreach($items as $i)
            <tr>
                <td>{{ $i->producto->nombre ?? '' }}</td>
                <td>{{ $i->categoria->nombre ?? '' }}</td>
                <td>{{ $i->departamento->nombre ?? '' }}</td>
                <td style="text-align:center;">{{ $i->cantidad }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
