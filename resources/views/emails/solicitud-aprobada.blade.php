<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .header {
            background: #198754;
            padding: 25px;
            color: white;
            text-align: center;
            font-size: 22px;
            font-weight: bold;
        }

        .content {
            padding: 25px;
            font-size: 15px;
            line-height: 1.6;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 25px;
            color: #0d6efd;
        }

        .footer {
            margin-top: 25px;
            padding-top: 15px;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #e3e3e3;
        }

        a.button {
            display: inline-block;
            margin-top: 15px;
            background: #0d6efd;
            color: white !important;
            padding: 10px 18px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        Solicitud Aprobada
    </div>

    <div class="content">

        <p>Hola,</p>

        <p>
            La solicitud <strong>{{ $solicitud->codigo }}</strong> ha sido 
            <strong style="color:#119420;">APROBADA</strong>.
        </p>

        <p><strong>Solicitante:</strong> {{ $solicitud->solicitante }}</p>
        <p><strong>Teléfono:</strong> {{ $solicitud->telefono }}</p>
        <p><strong>Correo:</strong> {{ $solicitud->correo }}</p>

        <p><strong>Bodega donde retirará:</strong> {{ $solicitud->bodega->nombre }}</p>
        <p><strong>Departamento Solicitante:</strong> {{ $solicitud->departamento->nombre }}</p>

        <p class="footer">
            Se adjunta el PDF oficial de la solicitud.<br>
            Gracias por utilizar nuestro sistema.
        </p>

        <a class="button" href="http://127.0.0.1:8000/admin/solicitudes" target="_blank">
            Ingresar al Sistema
        </a>

    </div>
</div>
</body>
</html>
