@extends('welcome')

@section('title', 'Detalle de Solicitud')

@section('content')
<div class="container-fluid py-4">

    <h1 class="page-title mt-4">📄 Detalle de Solicitud</h1>

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
        <li class="breadcrumb-item"><a href="{{ route('solicitud.index') }}">Solicitudes</a></li>
        <li class="breadcrumb-item active">{{ $solicitud->codigo }}</li>
    </ol>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Información General</h5>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <p><strong>Código:</strong> {{ $solicitud->codigo }}</p>
                    <p><strong>Solicitante:</strong> {{ $solicitud->solicitante }}</p>
                    <p><strong>Teléfono:</strong> {{ $solicitud->telefono }}</p>

                    <p><strong>Tipo movimiento:</strong>
                        <span class="badge bg-info text-dark">
                            {{ $solicitud->tipo_movimiento ?? 'N/A' }}
                        </span>
                    </p>
                </div>

                <div class="col-md-6">
                    <p><strong>Correo:</strong> {{ $solicitud->correo }}</p>

                    <p><strong>Bodega origen:</strong> {{ $solicitud->bodega->nombre ?? 'N/A' }}</p>

                    @if(($solicitud->tipo_movimiento ?? '') === 'TRASLADO')
                        <p><strong>Bodega destino:</strong> {{ $solicitud->bodegaDestino->nombre ?? 'N/D' }}</p>
                    @endif

                    <p><strong>Departamento:</strong> {{ $solicitud->departamento->nombre ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="row mb-3">

                {{-- FECHAS SEGÚN TIPO --}}
                @if(($solicitud->tipo_movimiento ?? '') === 'PRESTAMO')

                    <div class="col-md-6">
                        <p>
                            <strong>Fecha préstamo (salida):</strong>
                            {{ $solicitud->fecha_requerida ? \Carbon\Carbon::parse($solicitud->fecha_requerida)->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>

                    <div class="col-md-6">
                        <p>
                            <strong>Fecha devolución:</strong>
                            {{ $solicitud->fecha_devolucion ? \Carbon\Carbon::parse($solicitud->fecha_devolucion)->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>

                @else

                    <div class="col-md-6">
                        <p>
                            <strong>Fecha requerida:</strong>
                            {{ $solicitud->fecha_requerida ? \Carbon\Carbon::parse($solicitud->fecha_requerida)->format('d/m/Y') : 'N/A' }}
                        </p>
                    </div>

                @endif

                {{-- ESTADO --}}
                <div class="col-md-6">
                    <p>
                        <strong>Estado:</strong>
                        @if($solicitud->estado == 'pendiente')
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        @elseif($solicitud->estado == 'aprobado')
                            <span class="badge bg-success">Aprobado</span>
                        @else
                            <span class="badge bg-danger">Rechazado</span>
                        @endif
                    </p>
                </div>

            </div>

        </div>
    </div>

    {{-- DETALLE DE PRODUCTOS --}}
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Productos Solicitados</h5>
        </div>

        <div class="card-body">
            <table class="table table-bordered table-striped">
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

            {{-- BOTONES DE ACCIÓN --}}
            @auth
                @if(!Auth::user()->bodega)
                    @if($solicitud->estado == 'pendiente')
                    <div class="mt-4">

                        <form action="{{ route('solicitud.aprobar', $solicitud->id) }}"
                            method="POST"
                            class="d-inline form-aprobar">
                            @csrf
                            <button type="submit" class="btn btn-success px-4 rounded-pill">
                                ✔ Aprobar
                            </button>
                        </form>

                        <form action="{{ route('solicitud.rechazar', $solicitud->id) }}"
                            method="POST"
                            class="d-inline form-rechazar">
                            @csrf
                            <button type="button"
                                    class="btn btn-danger px-4 rounded-pill btn-abrir-rechazo">
                                ✖ Rechazar
                            </button>
                        </form>

                    </div>
                    @endif
                @endif
            @endauth

        </div>
    </div>

    {{-- MODAL MOTIVO DE RECHAZO --}}
    <div class="modal fade" id="modalRechazo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form id="formMotivoRechazo" method="POST" action="{{ route('solicitud.rechazar', $solicitud->id) }}">
                    @csrf

                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Motivo del Rechazo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Describe por qué se rechaza:</label>
                        <textarea class="form-control" name="motivo_rechazo" rows="4" required></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Enviar Rechazo</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: '{{ session('success') }}',
            timer: 2200,
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

    document.addEventListener('DOMContentLoaded', () => {

        const formAprobar = document.querySelector('.form-aprobar');

        if (formAprobar) {
            formAprobar.addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: '¿Aprobar solicitud?',
                    text: 'Se notificará al encargado de la bodega.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, aprobar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        }

        const btnRechazar = document.querySelector('.btn-abrir-rechazo');

        if (btnRechazar) {
            btnRechazar.addEventListener('click', function() {
                const modal = new bootstrap.Modal(document.getElementById('modalRechazo'));
                modal.show();
            });
        }

    });
</script>
@endpush