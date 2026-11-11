@extends('layouts.public')

@section('content')
<div class="container py-5">

    <div class="card shadow border-0">
        <div class="card-body p-4">

            <h3 class="mb-4 text-center">Detalle de Solicitud</h3>

            <p><strong>Código:</strong> {{ $solicitud->codigo }}</p>
            <p><strong>Estado:</strong> 
                <span class="badge 
                    @if($solicitud->estado=='pendiente') bg-warning 
                    @elseif($solicitud->estado=='aprobado') bg-success 
                    @else bg-danger @endif">
                    {{ strtoupper($solicitud->estado) }}
                </span>
            </p>

            <p><strong>Fecha requerida:</strong> {{ $solicitud->fecha_requerida }}</p>

            <hr>
            <h5>Datos del Solicitante</h5>
            <p><strong>Nombre:</strong> {{ $solicitud->solicitante }}</p>
            <p><strong>Teléfono:</strong> {{ $solicitud->telefono }}</p>
            <p><strong>Correo:</strong> {{ $solicitud->correo }}</p>

            <hr>
            <h5>Información de la Solicitud</h5>
            <p><strong>Bodega:</strong> {{ $solicitud->bodega->nombre }}</p>
            <p><strong>Departamento:</strong> {{ $solicitud->departamento->nombre }}</p>

            <hr>
            <h5>Productos Solicitados</h5>

            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitud->detalles as $d)
                    <tr>
                        <td>{{ $d->producto->nombre }}</td>
                        <td>{{ $d->cantidad }}</td>
                        <td>{{ $d->observacion }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-center mt-4">
                <a href="{{ route('public.consultar') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Nueva consulta
                </a>
            </div>

        </div>
    </div>

</div>
@endsection
