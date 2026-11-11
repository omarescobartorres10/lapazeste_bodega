@extends('welcome')

@section('title', 'Solicitudes')

@section('content')
<div class="container-fluid py-4">

    @if(auth()->user()->bodega)
        <div class="alert alert-info d-flex align-items-center shadow-sm rounded-pill px-3 py-2 mb-3">
            <i class="bi bi-shop me-2 fs-5"></i>
            <div><strong>Bodega asignada:</strong> {{ auth()->user()->bodega->nombre }}</div>
        </div>
    @endif

    <h1 class="page-title mt-4">📋 Solicitudes</h1>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
        <li class="breadcrumb-item active">Solicitudes</li>
    </ol>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list-check"></i> Listado de Solicitudes</h5>
        </div>

        <div class="card-body">

            @if($solicitudes->isEmpty())
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No hay solicitudes registradas por el momento.
                </div>
            @else

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Solicitante</th>
                            <th>Bodega origen</th>
                            <th>Tipo</th>
                            <th>Bodega destino</th>
                            <th>Estado</th>
                            <th>Fecha solicitud</th>
                            <th>Fecha requerida / préstamo</th>
                            <th>Fecha devolución</th>
                            <th>Resolución</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($solicitudes as $s)
                        <tr>
                            <td><strong>{{ $s->codigo }}</strong></td>
                            <td>{{ $s->solicitante }}</td>

                            <td>{{ $s->bodega->nombre ?? 'N/A' }}</td>

                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ $s->tipo_movimiento ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                @if(($s->tipo_movimiento ?? '') === 'TRASLADO')
                                    {{ $s->bodegaDestino->nombre ?? 'N/D' }}
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                @switch($s->estado)
                                    @case('pendiente')
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                        @break
                                    @case('aprobado')
                                        <span class="badge bg-success">Aprobado</span>
                                        @break
                                    @case('rechazado')
                                        <span class="badge bg-danger">Rechazado</span>
                                        @break
                                @endswitch
                            </td>

                            {{-- FECHA DE SOLICITUD --}}
                            <td>{{ $s->created_at ? $s->created_at->format('d/m/Y') : 'N/A' }}</td>

                            {{-- FECHA REQUERIDA / PRESTAMO --}}
                            <td>
                                {{ $s->fecha_requerida
                                    ? \Carbon\Carbon::parse($s->fecha_requerida)->format('d/m/Y')
                                    : 'N/A'
                                }}
                            </td>

                            {{-- FECHA DEVOLUCION (solo PRESTAMO) --}}
                            <td>
                                @if(($s->tipo_movimiento ?? '') === 'PRESTAMO')
                                    {{ $s->fecha_devolucion
                                        ? \Carbon\Carbon::parse($s->fecha_devolucion)->format('d/m/Y')
                                        : 'N/A'
                                    }}
                                @else
                                    —
                                @endif
                            </td>

                            {{-- RESOLUCIÓN --}}
                            <td>
                                @if($s->estado === 'pendiente')
                                    <span class="badge bg-secondary">Sin revisión</span>

                                @elseif($s->estado === 'rechazado')
                                    <span class="badge bg-danger">Rechazado</span>

                                @elseif($s->estado === 'aprobado')

                                    @if($s->resolucion === 'ENTREGADO')
                                        <span class="badge bg-success">Entregado</span>
                                    @else
                                        @if(auth()->user()->bodega_id == $s->bodega_id)
                                            <form action="{{ route('solicitud.entregado', $s->id) }}"
                                                method="POST" class="form-entregar d-inline">
                                                @csrf
                                                <button type="button"
                                                    class="btn btn-warning btn-sm rounded-pill btn-entregar"
                                                    data-id="{{ $s->id }}">
                                                    <i class="bi bi-check-circle"></i> Entregar
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge bg-warning text-dark">A la espera</span>
                                        @endif
                                    @endif

                                @endif
                            </td>

                            <td>
                                <a href="{{ route('solicitud.ver', $s->id) }}"
                                   class="btn btn-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-eye"></i> Ver detalle
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $solicitudes->links('pagination::bootstrap-5') }}
            </div>

            @endif
        </div>
    </div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    $(".btn-entregar").on("click", function () {

        let form = $(this).closest("form");

        Swal.fire({
            title: "¿Confirmar entrega?",
            text: "Esta solicitud será marcada como ENTREGADA.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, entregar",
            cancelButtonText: "Cancelar",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

    });

});
</script>

<script>
@if(session('success'))
Swal.fire({
    icon: 'success',
    title: 'Éxito',
    text: '{{ session('success') }}',
    timer: 2000,
    showConfirmButton: false
});
@endif

@if(session('error'))
Swal.fire({
    icon: 'error',
    title: 'Error',
    text: '{{ session('error') }}',
});
@endif
</script>
@endpush