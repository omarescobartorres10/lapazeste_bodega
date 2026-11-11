@extends('layouts.public')

@section('title', 'Resultado de Consulta')

@section('content')

<div class="container py-4" style="max-width: 900px;">

    <div class="card shadow-lg border-0 rounded-4">

        {{-- LOGO --}}
        <div class="text-center mt-4">
            <img src="{{ asset('img/LAPAZESTE.png') }}" width="110" class="img-fluid" style="filter: drop-shadow(0px 2px 4px rgba(0,0,0,0.2));">
        </div>

        <h2 class="text-center mt-3 mb-1 fw-bold text-uppercase" style="letter-spacing:1px;">
            Solicitud de Productos
        </h2>

        <p class="text-center mb-4 fs-5">
            <strong>Código:</strong> 
            <span class="text-primary fw-bold" style="font-size:1.1rem;">{{ $solicitud->codigo }}</span>
        </p>

        {{-- ESTADO --}}
        <div class="d-flex justify-content-center">
            <div class="px-4 py-2 rounded-3 shadow-sm" 
                style="background: #f8f9fa; border:1px solid #e3e3e3; width:auto;">
                <strong class="me-2">Estado:</strong>

                <span class="badge fs-6 px-3 py-2 
                    @if($solicitud->estado == 'pendiente') bg-warning text-dark
                    @elseif($solicitud->estado == 'aprobado') bg-success
                    @elseif($solicitud->estado == 'rechazado') bg-danger
                    @endif"
                    style="border-radius: 8px;">
                    {{ strtoupper($solicitud->estado) }}
                </span>
            </div>
        </div>


        <div class="card-body p-4" style="font-size:15px;">           

            {{-- === DATOS DEL SOLICITANTE === --}}
            <div class="px-3 py-2 mb-3 text-white fw-bold"
                 style="background:#004d66; border-radius:5px;">
                Datos del solicitante:
            </div>

            <div class="ms-2">
                <p><strong>Nombre:</strong> {{ $solicitud->solicitante }}</p>
                <p><strong>Teléfono:</strong> {{ $solicitud->telefono }}</p>
                <p><strong>Correo:</strong> {{ $solicitud->correo }}</p>
                <p>
                    <strong>Fecha requerida:</strong>
                    {{ \Carbon\Carbon::parse($solicitud->fecha_requerida)->format('d/m/Y') }}
                </p>
            </div>

            {{-- === INFORMACIÓN DE LA SOLICITUD === --}}
            <div class="px-3 py-2 mt-4 mb-3 text-white fw-bold"
                 style="background:#004d66; border-radius:5px;">
                Información de la solicitud:
            </div>

            <div class="ms-2">
                <p><strong>Bodega:</strong> {{ $solicitud->bodega->nombre }}</p>
                <p><strong>Departamento:</strong> {{ $solicitud->departamento->nombre }}</p>
            </div>

            {{-- === PRODUCTOS === --}}
            <div class="px-3 py-2 mt-4 mb-2 text-white fw-bold"
                 style="background:#004d66; border-radius:5px;">
                Productos solicitados:
            </div>

            <table class="table table-bordered" style="font-size:14px;">
                <thead>
                    <tr style="background:#004d66; color:white;">
                        <th>PRODUCTO</th>
                        <th style="width:120px;">CANTIDAD</th>
                        <th>OBSERVACIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($solicitud->detalles as $item)
                    <tr>
                        <td>{{ $item->producto->nombre }}</td>
                        <td class="text-center"><strong>{{ $item->cantidad }}</strong></td>
                        <td>{{ $item->observacion }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- BOTÓN --}}
            <div class="text-end mt-4">
                <a href="{{ route('solicitud.consultar') }}" class="btn btn-secondary px-4">
                    <i class="bi bi-arrow-left"></i> Nueva Consulta
                </a>
            </div>

        </div>
    </div>
</div>

@endsection
