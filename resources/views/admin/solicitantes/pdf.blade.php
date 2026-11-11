<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin: 25px;
        }

        .logo {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo img {
            width: 90px;
        }

        .header-box {
            background: #e3f1f6;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            border: 1px solid #c1d9e2;
            margin-bottom: 25px;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
            color: #003d52;
            margin: 0;
            text-transform: uppercase;
        }

        .header-date {
            margin-top: 6px;
            font-size: 14px;
        }

        /* ----------------------------------------- */
        /* SECCIÓN INFORMACIÓN GENERAL */
        /* ----------------------------------------- */

        .section-title {
            background: #004d66;
            color: white;
            padding: 8px 10px;
            border-radius: 4px;
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 14px;
        }

        /* ----------------------------------------- */
        /* TABLA - DISEÑO PROFESIONAL */
        /* ----------------------------------------- */

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 8px;
        }

        th, td {
            border: 1px solid #005577;
            padding: 6px;
            font-size: 11px;
            word-wrap: break-word;
            text-align: center;
        }

        th {
            background: #005f7a;
            color: white;
        }

        /* Anchos controlados */
        th:nth-child(1) { width: 70px; }
        th:nth-child(2) { width: 120px; }
        th:nth-child(3) { width: 120px; }
        th:nth-child(4) { width: 150px; }
        th:nth-child(5) { width: 75px; }
        th:nth-child(6) { width: 160px; }
        th:nth-child(7) { width: 70px; }
        th:nth-child(8) { width: 60px; }

        /* ----------------------------------------- */
        /* BADGES ESTADOS */
        /* ----------------------------------------- */

        .badge-activo {
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-inactivo {
            background: #dc3545;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

    </style>
</head>

<body>

    {{-- LOGO --}}
    <div class="logo">
        <img src="{{ public_path('img/LAPAZESTE.png') }}">
    </div>

    {{-- ENCABEZADO --}}
    <div class="header-box">
        <h2 class="header-title">Listado de Solicitantes</h2>
        <p class="header-date">
            Fecha de emisión:
            <strong>{{ now()->format('d/m/Y') }}</strong>
        </p>
    </div>

    {{-- INFORMACIÓN GENERAL --}}
    <div class="section-title">Información general:</div>

    {{-- TABLA --}}
    <table>
        <thead>
            <tr>
                <th>DUI</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Estado</th>
                <th>Solicitudes</th>
            </tr>
        </thead>

        <tbody>
            @foreach($solicitantes as $s)
            <tr>
                <td>{{ $s->dui }}</td>
                <td>{{ strtoupper($s->nombre) }}</td>
                <td>{{ $s->cargo }}</td>
                <td>{{ strtoupper($s->departamento->nombre ?? '-') }}</td>
                <td>{{ $s->telefono }}</td>
                <td>{{ $s->correo }}</td>

                <td>
                    @if($s->estado == 'activo')
                        <span class="badge-activo">ACTIVO</span>
                    @else
                        <span class="badge-inactivo">INACTIVO</span>
                    @endif
                </td>

                <td>{{ $s->solicitudes_count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
