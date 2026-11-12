<head>
    <link rel="stylesheet" href="{{ asset('assets/styleAggCategoria.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
</head>

@extends('welcome')

@section('title', 'Gestión de Bodegas')

@section('content')

<script>
    window.bloquearBotonBodega = function (formulario, textoCarga) {
        if (!formulario.checkValidity()) {
            return true;
        }

        if (formulario.dataset.enviando === '1') {
            return false;
        }
        

        formulario.dataset.enviando = '1';

        const boton = formulario.querySelector('button[type="submit"]');

        if (boton) {
            boton.disabled = true;
            boton.style.pointerEvents = 'none';

            boton.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                ${textoCarga}
            `;
        }

        return true;
    };
</script>

<div class="container-fluid py-4">
    <h1 class="page-title mt-4">🏭 Gestión de Bodegas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Panel</a></li>
        <li class="breadcrumb-item active">Bodegas</li>
    </ol>

    <!-- Botón para agregar nueva bodega -->
    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAgregarBodega">
            <i class="bi bi-building-add"></i> Añadir Bodega
        </button>
    </div>

    <!-- Modal para agregar bodega -->
    <div class="modal fade" id="modalAgregarBodega" tabindex="-1" aria-labelledby="modalAgregarBodegaLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow rounded-4 border-0">
                <div class="modal-header bg-primary text-white rounded-top-4" style="background-color: #6f42c1 !important;">
                    <h5 class="modal-title fw-bold d-flex align-items-center" id="modalAgregarBodegaLabel">
                        <i class="bi bi-building-fill-add me-2"></i>Nueva Bodega
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form 
                        action="{{ route('bodegas.store') }}" 
                        method="POST"
                        onsubmit="return bloquearBotonBodega(this, 'Guardando...')">
                        @csrf
                        <div class="mb-3">
                            <label for="nombre" class="form-label fw-semibold">Nombre de la Bodega</label>
                            <input type="text" class="form-control rounded-pill px-4" id="nombre" name="nombre" required placeholder="Ej. Bodega Central" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label for="responsable" class="form-label fw-semibold">Responsable</label>
                            <input type="text" class="form-control rounded-pill px-4" id="responsable" name="responsable" placeholder="Nombre del responsable" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label fw-semibold">Correo del Encargado</label>
                            <input type="email" class="form-control rounded-pill px-4" id="correo" name="correo"
                                placeholder="ejemplo@correo.com">
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label fw-semibold">Dirección</label>
                            <input type="text" class="form-control rounded-pill px-4" id="direccion" name="direccion" placeholder="Dirección de la bodega" style="text-transform: uppercase;">
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label fw-semibold">Estado</label>
                            <select class="form-select rounded-pill px-4" name="estado" id="estado">
                                <option value="Activo" selected>Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn text-white rounded-pill px-4" style="background-color: #6f42c1;">
                                <i class="bi bi-check2-circle me-1"></i>Registrar Bodega
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de bodegas -->
    <div class="card shadow-sm">
        <div class="card-header text-white">
            <h5 class="mb-0">🏭 Detalle de Bodegas</h5>
        </div>
        <div class="card-body">

            <!-- Filtro de búsqueda -->
            <form id="busquedaForm" method="GET" action="{{ route('bodegas.index') }}" class="mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-2">
                        <input type="text" id="buscarInput" name="buscar" class="form-control form-control-lg" placeholder="Buscar bodega..." value="{{ request('buscar') }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </form>

            @if ($bodegas->isEmpty())
                <div class="alert alert-info text-center rounded-3 py-2 mt-4">
                    <i class="bi bi-info-circle me-2"></i> No hay bodegas registradas.
                </div>
            @else
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Nombre</th>
                                <th>Responsable</th>
                                <th>Correo</th>
                                <th>Dirección</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bodegas as $bodega)
                                <tr>
                                    <td style="text-transform: uppercase;">{{ $bodega->nombre }}</td>
                                    <td style="text-transform: uppercase;">{{ $bodega->responsable ?? '—' }}</td>
                                    <td style="text-transform: uppercase;">{{ $bodega->correo ?? '—' }}</td>
                                    <td style="text-transform: uppercase;">{{ $bodega->direccion ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="{{ $bodega->estado == 'Activo' ? 'estado-activo' : 'estado-inactivo' }}">
                                            {{ $bodega->estado }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- Botón Editar -->
                                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editarBodega{{ $bodega->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- Activar / Desactivar -->
                                        <form action="{{ $bodega->estado == 'Activo' ? route('bodegas.desactivar', $bodega->id) : route('bodegas.activar', $bodega->id) }}" method="POST" class="d-inline-flex">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm {{ $bodega->estado == 'Activo' ? 'btn-danger' : 'btn-success' }}">
                                                <i class="fas fa-toggle-{{ $bodega->estado == 'Activo' ? 'off' : 'on' }}"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Editar -->
                                <div class="modal fade" id="editarBodega{{ $bodega->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $bodega->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-md">
                                        <div class="modal-content shadow rounded-4 border-0">
                                            <div class="modal-header bg-primary text-white rounded-top-4">
                                                <h5 class="modal-title fw-bold" id="editModalLabel{{ $bodega->id }}">
                                                    <i class="bi bi-pencil-square me-2"></i>Editar Bodega
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form 
                                                    action="{{ route('bodegas.update', $bodega->id) }}" 
                                                    method="POST"
                                                    onsubmit="return bloquearBotonBodega(this, 'Guardando cambios...')">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Nombre</label>
                                                        <input type="text" class="form-control rounded-pill px-4" name="nombre" value="{{ $bodega->nombre }}" required style="text-transform: uppercase;">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Responsable</label>
                                                        <input type="text" class="form-control rounded-pill px-4" name="responsable" value="{{ $bodega->responsable }}" style="text-transform: uppercase;">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Correo del Encargado</label>
                                                        <input type="email" class="form-control rounded-pill px-4"
                                                            name="correo"
                                                            value="{{ $bodega->correo }}"
                                                            placeholder="ejemplo@correo.com">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Dirección</label>
                                                        <input type="text" class="form-control rounded-pill px-4" name="direccion" value="{{ $bodega->direccion }}" style="text-transform: uppercase;">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Estado</label>
                                                        <select class="form-select rounded-pill px-4" name="estado">
                                                            <option value="Activo" {{ $bodega->estado == 'Activo' ? 'selected' : '' }}>Activo</option>
                                                            <option value="Inactivo" {{ $bodega->estado == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                                        </select>
                                                    </div>
                                                    <div class="d-flex justify-content-end">
                                                        <button type="submit" class="btn btn-success rounded-pill px-4">
                                                            <i class="bi bi-check-circle me-1"></i>Guardar Cambios
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Paginación -->
    <div class="row mt-4">
        <div class="col-12 col-md-6">
            <p class="mb-0">Mostrando {{ $bodegas->firstItem() }} a {{ $bodegas->lastItem() }} de {{ $bodegas->total() }} bodegas</p>
        </div>
        <div class="col-12 col-md-6 d-flex justify-content-md-end justify-content-center">
            {{ $bodegas->appends(['buscar' => request('buscar')])->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Toasts -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055;">
    @if(session('success'))
        <div class="custom-toast">
            <i class="bi bi-check-circle-fill toast-icon"></i>
            <span class="toast-text">{{ session('success') }}</span>
            <button class="toast-close" data-bs-dismiss="toast">&times;</button>
        </div>
    @endif
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        document.querySelectorAll('.custom-toast').forEach(toast => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 500);
        });
    }, 4000);
});

document.getElementById('busquedaForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const buscar = document.getElementById('buscarInput').value;
    const form = e.target;
    fetch(form.action + '?buscar=' + encodeURIComponent(buscar))
        .then(response => response.text())
        .then(html => {
            document.body.innerHTML = html;
            window.history.pushState({}, '', '{{ route('bodegas.index') }}');
        });
});
</script>
@endpush